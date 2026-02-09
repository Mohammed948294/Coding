<?php

declare(strict_types=1);

session_set_cookie_params([
    'httponly' => true,
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Riyadh');

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Core\\' => __DIR__ . '/',
        'App\\' => __DIR__ . '/../app/',
        'Dev\\' => __DIR__ . '/../dev/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relative = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

$config = require __DIR__ . '/config.php';
Core\Database::init($config['db']);
Core\AppContext::init($config);
