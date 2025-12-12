<?php
namespace Handlers;

use Core\Bot;
use Core\Database;
use Models\User;
use Core\Logger;

class StartHandler {
    private $bot;
    private $db;
    private $update;
    
    public function __construct($update) {
        $this->bot = new Bot();
        $this->db = Database::getInstance();
        $this->update = $update;
    }
    
    public function handle() {
        $message = $this->update['message'] ?? null;
        if (!$message) {
            return;
        }
        
        $chatId = $message['chat']['id'];
        $userId = $message['from']['id'];
        $text = $message['text'] ?? '';
        $firstName = $message['from']['first_name'] ?? 'User';
        $username = $message['from']['username'] ?? null;
        
        // Check channel subscription
        if (!$this->checkSubscription($userId)) {
            $this->showSubscriptionRequired($chatId, $firstName, $userId);
            return;
        }
        
        // Parse start parameters for referral
        $referralCode = null;
        if (strpos($text, '/start ') === 0) {
            $parts = explode(' ', $text);
            if (isset($parts[1])) {
                $referralCode = $parts[1];
                $this->processReferral($referralCode, $userId);
            }
        }
        
        // Show welcome message
        $this->showWelcomeMessage($chatId, $firstName, $userId, $username);
    }
    
    private function checkSubscription($userId) {
        try {
            $member = $this->bot->getChatMember(CHANNEL_ID, $userId);
            $status = $member['result']['status'] ?? 'left';
            return $status !== 'left';
        } catch (\Exception $e) {
            Logger::error("Subscription check failed: " . $e->getMessage());
            return true; // Allow access if check fails
        }
    }
    
    private function showSubscriptionRequired($chatId, $firstName, $userId) {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ التحقق من الانضمام', 'callback_data' => 'check_subscription']
                ],
                [
                    ['text' => '📢 قناة البوت', 'url' => 'https://t.me/TZZQX']
                ]
            ]
        ];
        
        $message = "مرحباً {$firstName} 👋\n\n";
        $message .= "🚫 يجب الانضمام إلى قناة البوت أولاً لاستخدام الخدمات.\n";
        $message .= "✅ بعد الانضمام، اضغط على زر التحقق.";
        
        $this->bot->sendMessage($chatId, $message, [
            'reply_markup' => json_encode($keyboard)
        ]);
    }
    
    private function processReferral($code, $newUserId) {
        // Get referral owner
        $referralData = $this->db->getReferralByCode($code);
        if ($referralData) {
            $ownerId = $referralData['user_id'];
            $owner = new User($ownerId);
            $owner->addReferral();
            
            // Add bonus to new user
            $newUser = new User($newUserId);
            if (!$newUser->exists()) {
                $newUser->updateBalance(REFERRAL_BONUS, 'add');
            }
        }
    }
    
    private function showWelcomeMessage($chatId, $firstName, $userId, $username) {
        $user = new User($userId);
        
        if ($user->exists()) {
            // User exists - show main menu
            $this->showMainMenu($chatId, $user);
        } else {
            // New user - show registration options
            $this->showRegistrationOptions($chatId, $firstName);
        }
    }
    
    private function showMainMenu($chatId, $user) {
        $userData = $user->getData();
        $balance = number_format($userData['balance'], 2);
        $email = $userData['email'] ?? 'غير مسجل';
        
        $message = "👤 <b>مرحباً بك مرة أخرى</b>\n\n";
        $message .= "📧 <b>الحساب:</b> {$email}\n";
        $message .= "💰 <b>الرصيد:</b> {$balance} روبل\n";
        $message .= "📊 <b>عدد الطلبات:</b> {$userData['orders_count']}\n";
        $message .= "👥 <b>الإحالات:</b> {$userData['referrals']}\n\n";
        $message .= "اختر من القائمة:";
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📱 شراء أرقام', 'callback_data' => 'buy_numbers']
                ],
                [
                    ['text' => '💰 شحن الرصيد', 'callback_data' => 'add_balance'],
                    ['text' => '💳 متجر الكروت', 'callback_data' => 'cards_shop']
                ],
                [
                    ['text' => '📊 إحصائياتي', 'callback_data' => 'my_stats'],
                    ['text' => '👥 الإحالات', 'callback_data' => 'referrals']
                ],
                [
                    ['text' => '⚙️ الإعدادات', 'callback_data' => 'settings'],
                    ['text' => '📞 الدعم', 'callback_data' => 'support']
                ]
            ]
        ];
        
        $this->bot->sendMessage($chatId, $message, [
            'reply_markup' => json_encode($keyboard)
        ]);
    }
    
    private function showRegistrationOptions($chatId, $firstName) {
        $message = "👋 <b>مرحباً {$firstName}</b>\n\n";
        $message .= "🔐 <b>مرحباً بك في بوت الأرقام الفورية</b>\n";
        $message .= "يمكنك من خلالي الحصول على أرقام وهمية لتطبيقات مختلفة.\n\n";
        $message .= "⚠️ <i>لبدء الاستخدام، يجب إنشاء حساب أولاً</i>";
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ إنشاء حساب جديد', 'callback_data' => 'register_new']
                ],
                [
                    ['text' => '🔐 تسجيل الدخول', 'callback_data' => 'login_existing']
                ],
                [
                    ['text' => '📖 شروط الاستخدام', 'callback_data' => 'terms'],
                    ['text' => '❓ المساعدة', 'callback_data' => 'help']
                ]
            ]
        ];
        
        $this->bot->sendMessage($chatId, $message, [
            'reply_markup' => json_encode($keyboard)
        ]);
    }
}
?>