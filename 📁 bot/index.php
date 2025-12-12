<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Bot.php';
require_once __DIR__ . '/core/Logger.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/handlers/StartHandler.php';

// Initialize Logger
Logger::init();

// Get update from Telegram
$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update) {
    Logger::error("No update received or invalid JSON");
    die('No update received');
}

// Log the update
Logger::info("Update received: " . json_encode($update, JSON_PRETTY_PRINT));

try {
    // Process the update
    if (isset($update['message'])) {
        $message = $update['message'];
        $text = $message['text'] ?? '';
        
        // Check if it's a start command
        if (strpos($text, '/start') === 0) {
            $handler = new Handlers\StartHandler($update);
            $handler->handle();
        } else if (strpos($text, '/my') === 0) {
            // Handle /my command
            $this->handleMyCommand($update);
        } else {
            // Handle other messages
            $this->handleMessage($update);
        }
    } else if (isset($update['callback_query'])) {
        // Handle callback queries
        $this->handleCallbackQuery($update);
    }
    
    echo 'OK';
    
} catch (Exception $e) {
    Logger::error("Error processing update: " . $e->getMessage());
    http_response_code(500);
    echo 'Error';
}

// Additional handler functions
function handleMyCommand($update) {
    $bot = new Core\Bot();
    $message = $update['message'];
    $chatId = $message['chat']['id'];
    $userId = $message['from']['id'];
    
    $user = new Models\User($userId);
    
    if ($user->exists()) {
        $data = $user->getData();
        $balance = number_format($data['balance'], 2);
        
        $messageText = "👤 <b>معلومات حسابك</b>\n\n";
        $messageText .= "🆔 <b>ID:</b> {$userId}\n";
        $messageText .= "📧 <b>الإيميل:</b> " . ($data['email'] ?? 'غير معروف') . "\n";
        $messageText .= "💰 <b>الرصيد:</b> {$balance} روبل\n";
        $messageText .= "🛒 <b>الطلبات:</b> {$data['orders_count']}\n";
        $messageText .= "👥 <b>الإحالات:</b> {$data['referrals']}\n";
        $messageText .= "📅 <b>تاريخ التسجيل:</b> " . date('Y-m-d', $data['created_at']);
        
        $bot->sendMessage($chatId, $messageText);
    } else {
        $bot->sendMessage($chatId, "⚠️ <b>ليس لديك حساب</b>\n\nاستخدم /start لإنشاء حساب جديد.");
    }
}

function handleCallbackQuery($update) {
    $bot = new Core\Bot();
    $callback = $update['callback_query'];
    $data = $callback['data'];
    $chatId = $callback['message']['chat']['id'];
    $messageId = $callback['message']['message_id'];
    $callbackId = $callback['id'];
    
    Logger::info("Callback received: {$data}");
    
    switch ($data) {
        case 'check_subscription':
            $bot->answerCallback($callbackId, "جارٍ التحقق...");
            // Add subscription check logic here
            break;
            
        case 'register_new':
            $bot->answerCallback($callbackId, "جارٍ تحويلك إلى صفحة التسجيل...");
            $this->showRegistrationForm($chatId);
            break;
            
        case 'buy_numbers':
            $bot->answerCallback($callbackId, "جارٍ فتح قائمة الأرقام...");
            $this->showNumbersMenu($chatId);
            break;
            
        default:
            $bot->answerCallback($callbackId, "هذا الزر غير نشط حالياً");
    }
}

function showRegistrationForm($chatId) {
    $bot = new Core\Bot();
    
    $message = "📝 <b>إنشاء حساب جديد</b>\n\n";
    $message .= "لإنشاء حساب، أرسل إيميلك وكلمة المرور بالشكل التالي:\n";
    $message .= "<code>email@example.com password123</code>\n\n";
    $message .= "⚠️ <i>يجب أن تكون كلمة المرور 6 أحرف على الأقل</i>";
    
    $bot->sendMessage($chatId, $message);
    
    // Save step for registration
    file_put_contents(DATA_DIR . "id/{$chatId}/step.txt", "register");
}

function showNumbersMenu($chatId) {
    $bot = new Core\Bot();
    $user = new Models\User($chatId);
    
    if (!$user->exists()) {
        $bot->sendMessage($chatId, "⚠️ <b>يجب إنشاء حساب أولاً</b>\n\nاستخدم /start لإنشاء حساب.");
        return;
    }
    
    $countries = [
        ['code' => 'sa', 'name' => 'السعودية', 'price' => 5],
        ['code' => 'eg', 'name' => 'مصر', 'price' => 3],
        ['code' => 'ae', 'name' => 'الإمارات', 'price' => 6],
        ['code' => 'us', 'name' => 'أمريكا', 'price' => 8],
        ['code' => 'gb', 'name' => 'بريطانيا', 'price' => 7],
    ];
    
    $keyboard = ['inline_keyboard' => []];
    
    foreach ($countries as $country) {
        $keyboard['inline_keyboard'][] = [
            [
                'text' => "{$country['name']} - {$country['price']} روبل",
                'callback_data' => "buy_{$country['code']}"
            ]
        ];
    }
    
    $keyboard['inline_keyboard'][] = [
        ['text' => '🔙 رجوع', 'callback_data' => 'back_to_main']
    ];
    
    $message = "📱 <b>شراء أرقام</b>\n\n";
    $message .= "💰 <b>رصيدك الحالي:</b> " . number_format($user->getBalance(), 2) . " روبل\n\n";
    $message .= "اختر الدولة:";
    
    $bot->sendMessage($chatId, $message, [
        'reply_markup' => json_encode($keyboard)
    ]);
}
?>