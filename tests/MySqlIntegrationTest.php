<?php

namespace WebSK\Logger\Tests;

use Jgut\Slim\PHPDI\Configuration;
use Jgut\Slim\PHPDI\ContainerBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use WebSK\Cache\CacheService;
use WebSK\Cache\CacheServiceProvider;
use WebSK\Cache\Engines\CacheEngineInterface;
use WebSK\Config\ConfWrapper;
use WebSK\Logger\Demo\LoggerDemoApp;
use WebSK\Logger\Entry\LoggerEntry;
use WebSK\Logger\Entry\LoggerEntryService;
use WebSK\Slim\Facade;

#[Group('mysql')]
final class MySqlIntegrationTest extends TestCase
{
    protected function tearDown(): void
    {
        ConfWrapper::setConfig([]);
    }

    public function testPersistsEntryAndRendersListRoute(): void
    {
        $mysql_host = getenv('MYSQL_TEST_HOST');
        if (!is_string($mysql_host) || $mysql_host === '') {
            self::markTestSkipped('Set MYSQL_TEST_HOST to run the MySQL integration test');
        }

        $settings = [
            'cache' => [
                'engine' => \WebSK\Cache\Engines\Memcached::class,
                'cache_key_prefix' => 'logger-test',
                'servers' => [['host' => '127.0.0.1', 'port' => 11211]],
            ],
            'db' => [
                'db_logger' => [
                    'host' => $mysql_host,
                    'db_name' => self::environmentValue('MYSQL_TEST_DATABASE', 'db_logger'),
                    'user' => self::environmentValue('MYSQL_TEST_USER', 'root'),
                    'password' => self::environmentValue('MYSQL_TEST_PASSWORD', ''),
                    'dump_file_path' => dirname(__DIR__) . '/dumps/db_logger.sql',
                ],
            ],
            'site_title' => 'Logger integration test',
            'site_name' => 'Logger integration test',
            'logger' => [
                'layout_main' => dirname(__DIR__) . '/views/layouts/layout.main.tpl.php',
                'layout_admin' => dirname(__DIR__) . '/views/layouts/layout.main.tpl.php',
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
        Facade::setFacadeApplication($app);
        $container->set(
            CacheServiceProvider::SERVICE_CONTAINER_ID,
            new CacheService(new InMemoryCacheEngine())
        );

        $entry_service = $container->get(LoggerEntryService::class);
        self::assertInstanceOf(LoggerEntryService::class, $entry_service);

        $entry = new LoggerEntry();
        $entry->setObjectFullId('Integration.Entity.1');
        $entry->setSerializedObject(serialize(['state' => 'created']));
        $entry->setUserIp('127.0.0.1');
        $entry->setComment('integration smoke');
        $entry_service->save($entry);

        self::assertNotNull($entry->getId());
        self::assertInstanceOf(LoggerEntry::class, $entry_service->getById($entry->getId()));

        $request = (new ServerRequestFactory())->createServerRequest('GET', '/admin/logger/entry');
        $container->set(ServerRequestInterface::class, $request);
        $response = $app->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Integration.Entity.1', (string)$response->getBody());

        $detail_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            '/admin/logger/entry/' . $entry->getId()
        );
        $container->set(ServerRequestInterface::class, $detail_request);
        $detail_response = $app->handle($detail_request);
        $detail_html = (string)$detail_response->getBody();

        self::assertSame(200, $detail_response->getStatusCode());
        self::assertStringContainsString('class="row g-0 rounded border bg-light p-3 mt-3"', $detail_html);
        self::assertStringContainsString('class="list-group mb-4"', $detail_html);
        self::assertStringNotContainsString('dl-horizontal', $detail_html);
        self::assertStringNotContainsString('jumbotron', $detail_html);
    }

    private static function environmentValue(string $name, string $default): string
    {
        $value = getenv($name);

        return is_string($value) ? $value : $default;
    }
}

final class InMemoryCacheEngine implements CacheEngineInterface
{
    /** @var array<string, mixed> */
    private array $values = [];

    public function set(string $key, mixed $value, int $ttl_sec = 0): bool
    {
        $this->values[$key] = $value;

        return true;
    }

    public function get(string $key): mixed
    {
        return $this->values[$key] ?? false;
    }

    public function delete(string $key): bool
    {
        unset($this->values[$key]);

        return true;
    }
}
