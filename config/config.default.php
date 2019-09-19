<?php

return [
    'settings' => [
        'displayErrorDetails' => false,
        'cache' => [
            'engine' => \WebSK\Cache\Engines\Memcache::class,
            'cache_key_prefix' => 'skif',
            'servers' => [
                [
                    'host' => 'localhost',
                    'port' => 11211
                ]
            ]
        ],
        'db' => [
            'db_logger' => [
                'host' => 'localhost',
                'db_name' => 'db_logger',
                'user' => 'root',
                'password' => 'root',
            ],
        ],
        'log_path' => '/var/www/log',
        'tmp_path' => '/var/www/tmp',
        'site_domain' => 'http://localhost',
        'site_full_path' => '/var/www/php-logger',
        'site_name' => 'PHP Logger Demo',
        'site_title' => 'WebSK. PHP Logger Demo',
        'site_email' => 'support@websk.ru',
        'logger' => [
            'layout_main' => '/var/www/php-logger/views/layouts/layout.main.tpl.php',
            'layout_skif' => '/var/www/php-logger/views/layouts/layout.main.tpl.php',
            'main_page_url' => '/',
            'skif_main_page_url' => '/admin/logger/entry'
        ]
    ],
];
