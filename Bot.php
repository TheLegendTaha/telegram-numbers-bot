<?php
namespace Core;

class Bot {
    private $token;
    private $baseUrl;
    
    public function __construct($token = null) {
        $this->token = $token ?: BOT_TOKEN;
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}/";
    }
    
    public function call($method, $params = []) {
        $url = $this->baseUrl . $method;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $response = @file_get_contents($url);
        
        if ($response === false) {
            Logger::error("Failed to call Telegram API: {$method}");
            return null;
        }
        
        return json_decode($response, true);
    }
    
    public function sendMessage($chatId, $text, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true
        ], $options);
        
        return $this->call('sendMessage', $params);
    }
    
    public function editMessage($chatId, $messageId, $text, $options = []) {
        $params = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ], $options);
        
        return $this->call('editMessageText', $params);
    }
    
    public function answerCallback($callbackId, $text, $showAlert = false) {
        return $this->call('answerCallbackQuery', [
            'callback_query_id' => $callbackId,
            'text' => $text,
            'show_alert' => $showAlert
        ]);
    }
    
    public function getChatMember($chatId, $userId) {
        return $this->call('getChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId
        ]);
    }
    
    public function deleteMessage($chatId, $messageId) {
        return $this->call('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId
        ]);
    }
}
?>