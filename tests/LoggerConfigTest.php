<?php

namespace WebSK\Logger\Tests;

use PHPUnit\Framework\TestCase;
use WebSK\Config\ConfWrapper;
use WebSK\Cache\Engines\Memcached;
use WebSK\Logger\LoggerConfig;

final class LoggerConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        ConfWrapper::setConfig([]);
    }

    public function testReadsLoggerSettingsAndLayoutFallbacks(): void
    {
        ConfWrapper::setConfig([
            'layout' => ['main' => '/fallback-main.php', 'admin' => '/fallback-admin.php'],
            'logger' => [
                'layout_main' => '/logger-main.php',
                'main_page_url' => '/log',
                'admin_main_page_url' => '/admin',
                'user_service_container_name' => 'users',
                'user_profile_route_name' => 'user.profile',
            ],
        ]);

        self::assertSame('/logger-main.php', LoggerConfig::getMainLayout());
        self::assertSame('/fallback-admin.php', LoggerConfig::getAdminLayout());
        self::assertSame('/log', LoggerConfig::getMainPageUrl());
        self::assertSame('/admin', LoggerConfig::getAdminMainPageUrl());
        self::assertSame('users', LoggerConfig::getUserServiceContainerName());
        self::assertSame('user.profile', LoggerConfig::getUserProfileRouteName());
    }

    public function testDefaultConfigurationUsesDocumentedMemcachedBackend(): void
    {
        $config = require dirname(__DIR__) . '/config/config.default.php';
        self::assertIsArray($config);

        $settings = $config['settings'] ?? null;
        self::assertIsArray($settings);

        $cache = $settings['cache'] ?? null;
        self::assertIsArray($cache);

        self::assertSame(Memcached::class, $cache['engine'] ?? null);
    }

    public function testRejectsInvalidConfigurationValueType(): void
    {
        ConfWrapper::setConfig(['logger' => ['main_page_url' => 123]]);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('logger.main_page_url must be a string');

        LoggerConfig::getMainPageUrl();
    }
}
