<?php
namespace Core;

class Logger {
    private static $logFile = DATA_DIR . 'logs/bot.log';
    
    public static function init() {
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }
    
    public static function log($message, $type = 'INFO') {
        self::init();
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$type}] {$message}\n";
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
    }
    
    public static function info($message) {
        self::log($message, 'INFO');
    }
    
    public static function error($message) {
        self::log($message, 'ERROR');
    }
    
    public static function warning($message) {
        self::log($message, 'WARNING');
    }
}
?>