<?php

declare(strict_types=1);

$config = [
    'includes' => [
    ],
    'parameters' => [
        'phpVersion' => PHP_VERSION_ID,
    ],
];

if (PHP_VERSION_ID < 80000) {
    $config['includes'][] = __DIR__ . '/phpstan.php-lt-80.neon';
}

if (PHP_VERSION_ID < 80100) {
    $config['includes'][] = __DIR__ . '/phpstan.php-lt-81.neon';
}

return $config;
