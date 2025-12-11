<?php
namespace Core;

class Database {
    private static $instance = null;
    private $data = [];
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // User Management
    public function saveUser($chatId, $data) {
        $file = EMIL_DIR . "users/{$chatId}.json";
        return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    public function getUser($chatId) {
        $file = EMIL_DIR . "users/{$chatId}.json";
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        }
        return null;
    }
    
    // Order Management
    public function saveOrder($orderId, $data) {
        $file = BUY_DIR . "orders/{$orderId}.json";
        return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    public function getOrder($orderId) {
        $file = BUY_DIR . "orders/{$orderId}.json";
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        }
        return null;
    }
    
    // Statistics
    public function getStats() {
        $stats = [
            'total_users' => 0,
            'total_orders' => 0,
            'completed_orders' => 0,
            'total_revenue' => 0,
            'active_users' => 0
        ];
        
        // Count users
        $users = glob(EMIL_DIR . "users/*.json");
        $stats['total_users'] = count($users);
        
        // Count orders
        $orders = glob(BUY_DIR . "orders/*.json");
        $stats['total_orders'] = count($orders);
        
        // Calculate completed orders and revenue
        foreach ($orders as $orderFile) {
            $order = json_decode(file_get_contents($orderFile), true);
            if ($order && isset($order['status']) && $order['status'] == 'completed') {
                $stats['completed_orders']++;
                $stats['total_revenue'] += ($order['price'] ?? 0);
            }
        }
        
        return $stats;
    }
    
    // Referral System
    public function saveReferral($code, $userId) {
        $file = ASSIGNMENT_DIR . "referrals/{$code}.json";
        $data = ['user_id' => $userId, 'created_at' => time()];
        return file_put_contents($file, json_encode($data));
    }
    
    public function getReferralByCode($code) {
        $file = ASSIGNMENT_DIR . "referrals/{$code}.json";
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        }
        return null;
    }
}
?>
