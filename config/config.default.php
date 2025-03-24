<?php

return [
    'settings' => [
        'displayErrorDetails' => false,
        'cache' => [
            'engine' => \WebSK\Cache\Engines\Memcache::class,
            'cache_key_prefix' => 'websk_logger',
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
                'dump_file_path' => \WebSK\Logger\LoggerServiceProvider::DUMP_FILE_PATH
            ]
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
            'layout_admin' => '/var/www/php-logger/views/layouts/layout.main.tpl.php',
            'main_page_url' => '/',
            'admin_main_page_url' => '/admin',
            'user_service_container_name' => null,
            'user_profile_route_name' => '',
        ]
    ],
];
