<?php
namespace Models;

use Core\Database;
use Core\Logger;

class User {
    private $chatId;
    private $data;
    private $db;
    
    public function __construct($chatId) {
        $this->chatId = $chatId;
        $this->db = Database::getInstance();
        $this->load();
    }
    
    private function load() {
        $this->data = $this->db->getUser($this->chatId) ?? [
            'chat_id' => $this->chatId,
            'balance' => 0,
            'total_spent' => 0,
            'orders_count' => 0,
            'referral_code' => null,
            'referrals' => 0,
            'created_at' => time(),
            'last_active' => time()
        ];
    }
    
    public function save() {
        $this->data['last_active'] = time();
        return $this->db->saveUser($this->chatId, $this->data);
    }
    
    public function exists() {
        return isset($this->data['created_at']);
    }
    
    public function create($email, $password) {
        if ($this->exists()) {
            return false;
        }
        
        $this->data['email'] = $email;
        $this->data['password'] = password_hash($password, PASSWORD_DEFAULT);
        $this->data['balance'] = 0;
        $this->data['total_spent'] = 0;
        $this->data['orders_count'] = 0;
        $this->data['referral_code'] = $this->generateReferralCode();
        $this->data['referrals'] = 0;
        $this->data['created_at'] = time();
        $this->data['last_active'] = time();
        
        return $this->save();
    }
    
    public function updateBalance($amount, $type = 'add') {
        if ($type === 'add') {
            $this->data['balance'] += $amount;
        } else if ($type === 'subtract') {
            if ($this->data['balance'] < $amount) {
                return false; // Insufficient balance
            }
            $this->data['balance'] -= $amount;
            $this->data['total_spent'] += $amount;
        }
        
        return $this->save();
    }
    
    public function addOrder($orderId, $amount) {
        $this->data['orders_count']++;
        return $this->updateBalance($amount, 'subtract');
    }
    
    public function addReferral() {
        $this->data['referrals']++;
        $this->updateBalance(REFERRAL_BONUS, 'add');
        return $this->save();
    }
    
    private function generateReferralCode() {
        return substr(md5(uniqid() . $this->chatId), 0, 8);
    }
    
    // Getters
    public function getData() {
        return $this->data;
    }
    
    public function getBalance() {
        return $this->data['balance'] ?? 0;
    }
    
    public function getEmail() {
        return $this->data['email'] ?? null;
    }
    
    public function getReferralCode() {
        return $this->data['referral_code'] ?? null;
    }
    
    public function verifyPassword($password) {
        if (!isset($this->data['password'])) {
            return false;
        }
        return password_verify($password, $this->data['password']);
    }
}
?>
