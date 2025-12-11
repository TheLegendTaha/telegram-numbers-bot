<?php
// Telegram Bot Configuration
define('BOT_TOKEN', '8385144854:AAEMR_U3O7KWXrGrmJOk4vmACZMo67_kI6k');
define('BOT_USERNAME', 'NUMBER22_1_BOT'); // ضع اسم البوت الحقيقي

// Admin Configuration
define('ADMIN_ID', 6541324779);
define('CHANNEL_ID', -1001913573510);
define('PAY_CHANNEL', -1001634464532);
define('BUY_CHANNEL', -1001928637976);
define('NOTIFY_CHANNEL', -1001810340779);

// Paths Configuration
define('DATA_DIR', __DIR__ . '/data/');
define('EMIL_DIR', DATA_DIR . 'EMIL/');
define('BUY_DIR', DATA_DIR . 'BUY/');
define('ASSIGNMENT_DIR', DATA_DIR . 'assignment/');

// Bot Settings
define('REFERRAL_BONUS', 0.25);
define('EXCHANGE_RATE', 60);
define('MIN_TRANSFER', 20);

// Timezone
date_default_timezone_set('Asia/Baghdad');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create directories if not exist
$directories = [
    DATA_DIR, EMIL_DIR, BUY_DIR, ASSIGNMENT_DIR,
    DATA_DIR . 'id/', DATA_DIR . 'txt/', DATA_DIR . 'api/',
    EMIL_DIR . 'users/', BUY_DIR . 'orders/'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}
?>