<?php
require_once 'config.php';

echo "📦 جاري تثبيت نظام البوت...\n\n";

// Create all necessary directories
$directories = [
    DATA_DIR, EMIL_DIR, BUY_DIR, ASSIGNMENT_DIR,
    DATA_DIR . 'id/', DATA_DIR . 'txt/', DATA_DIR . 'api/',
    DATA_DIR . 'logs/', EMIL_DIR . 'users/', BUY_DIR . 'orders/',
    ASSIGNMENT_DIR . 'referrals/'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
        echo "✅ تم إنشاء المجلد: {$dir}\n";
    } else {
        echo "⚠️ المجلد موجود بالفعل: {$dir}\n";
    }
}

// Create default files
$defaultFiles = [
    DATA_DIR . 'openlock.json' => json_encode(['bot' => ['lock' => 'unlock']]),
    DATA_DIR . 'country.json' => json_encode([
        'sa' => ['name' => 'السعودية', 'price' => 5, 'status' => 'active'],
        'eg' => ['name' => 'مصر', 'price' => 3, 'status' => 'active']
    ]),
    DATA_DIR . 'txt/agent.json' => json_encode(['gents' => []]),
    DATA_DIR . 'storenumber.json' => json_encode(['ready' => []]),
    DATA_DIR . 'id/admin.json' => json_encode([]),
    DATA_DIR . 'txt/rubleall.txt' => '0',
    DATA_DIR . 'txt/pointall.txt' => '0',
    DATA_DIR . 'txt/file.txt' => ''
];

foreach ($defaultFiles as $file => $content) {
    if (!file_exists($file)) {
        file_put_contents($file, $content);
        echo "✅ تم إنشاء الملف: {$file}\n";
    } else {
        echo "⚠️ الملف موجود بالفعل: {$file}\n";
    }
}

echo "\n🎉 تم اكتمال التثبيت بنجاح!\n";
echo "🔗 يمكنك الآن تعيين الويب هوك باستخدام الرابط:\n";
echo "https://api.telegram.org/bot" . BOT_TOKEN . "/setWebhook?url=" . urlencode("https://YOUR-DOMAIN.COM/bot/index.php");
?>