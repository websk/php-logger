<?php

namespace WebSK\Logger\Tests;

use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContainerBuilder;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Slim\Interfaces\RouteParserInterface;
use WebSK\Config\ConfWrapper;
use WebSK\CRUD\CRUD;
use WebSK\Logger\Demo\LoggerDemoApp;
use WebSK\Logger\RequestHandlers\EntriesListHandler;
use WebSK\Logger\RequestHandlers\EntryEditHandler;
use WebSK\Logger\RequestHandlers\ObjectEntriesListHandler;

#[CoversNothing]
final class ContainerIntegrationTest extends TestCase
{
    protected function tearDown(): void
    {
        ConfWrapper::setConfig([]);
    }

    public function testBuildsContainerInjectsCrudAndRegistersRoutes(): void
    {
        $settings = [
            'cache' => [
                'engine' => \WebSK\Cache\Engines\Memcached::class,
                'cache_key_prefix' => 'logger-test',
                'servers' => [['host' => '127.0.0.1', 'port' => 11211]],
            ],
            'db' => [
                'db_logger' => [
                    'host' => '127.0.0.1',
                    'db_name' => 'logger_test',
                    'user' => 'root',
                    'password' => '',
                    'dump_file_path' => '',
                ],
            ],
            'logger' => [
                'layout_main' => '/tmp/layout.php',
                'layout_admin' => '/tmp/layout.php',
                'main_page_url' => '/',
                'admin_main_page_url' => '/admin',
                'user_service_container_name' => null,
                'user_profile_route_name' => '',
            ],
        ];
        ConfWrapper::setConfig($settings);

        $configuration = new Configuration();
        $configuration->setDefinitions([['settings' => $settings]]);
        $configuration->setUseAttributes(true);
        $container = ContainerBuilder::build($configuration);

        $app = new LoggerDemoApp($container);

        $handler = $container->get(EntriesListHandler::class);
        self::assertInstanceOf(EntriesListHandler::class, $handler);
        $crud_property = new \ReflectionProperty(EntriesListHandler::class, 'crud_service');
        self::assertTrue($crud_property->isInitialized($handler));
        self::assertInstanceOf(CRUD::class, $crud_property->getValue($handler));
        self::assertInstanceOf(RouteParserInterface::class, $container->get(RouteParserInterface::class));

        $route_names = [];
        foreach ($app->getRouteCollector()->getRoutes() as $route) {
            $route_name = $route->getName();
            if ($route_name !== null) {
                $route_names[] = $route_name;
            }
        }

        self::assertContains(EntriesListHandler::class, $route_names);
        self::assertContains(EntryEditHandler::class, $route_names);
        self::assertContains(ObjectEntriesListHandler::class, $route_names);
    }
}
