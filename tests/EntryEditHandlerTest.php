<?php

namespace WebSK\Logger\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use WebSK\Logger\Entry\LoggerEntry;
use WebSK\Logger\Entry\LoggerEntryService;
use WebSK\Logger\RequestHandlers\EntryEditHandler;

final class EntryEditHandlerTest extends TestCase
{
    public function testFlattensNestedValuesAndStopsObjectCycles(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $handler = new TestableEntryEditHandler($container);

        $node = new \stdClass();
        $node->name = 'root';
        $node->self = $node;

        self::assertSame(
            [
                'enabled' => '#TRUE#',
                'nested.value' => '15',
                'object.name' => 'root',
                'object.self' => '#RECURSION#',
                'nullable' => '#NULL#',
            ],
            $handler->flatten([
                'enabled' => true,
                'nested' => ['value' => 15],
                'object' => $node,
                'nullable' => null,
            ])
        );
    }

    public function testNormalizesSerializedProtectedAndPrivatePropertyNames(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $handler = new TestableEntryEditHandler($container);

        self::assertSame(
            [
                'body' => 'protected',
                'Example\\Entity::identifier' => 'private',
            ],
            $handler->flatten([
                "\0*\0body" => 'protected',
                "\0Example\\Entity\0identifier" => 'private',
            ])
        );
    }

    public function testRendersBootstrapFiveMarkupAndEscapesLoggedValues(): void
    {
        $entry = new LoggerEntry();
        $entry->setCreatedAtTs(1_700_000_000);
        $entry->setUserFullId('<script>user</script>');
        $entry->setUserIp('<img src=x onerror=alert(1)>');
        $entry->setRequestUriWithServerName('example.test/?value=<script>');
        $entry->setHttpUserAgent('<svg onload=alert(1)>');
        $entry->setComment('<b>unsafe comment</b>');
        $entry->setObjectFullId('Entity.<script>');
        $entry->setSerializedObject(serialize([
            '<script>field</script>' => '<strong>value</strong>',
            'long' => str_repeat('<tag>', 30),
        ]));

        $entry_service = $this->createStub(LoggerEntryService::class);
        $entry_service->method('getById')->willReturn($entry);
        $entry_service->method('getUserIdForEntry')->willReturn(null);

        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->willReturnCallback(
            static fn(string $id): LoggerEntryService => $id === LoggerEntryService::class
                ? $entry_service
                : throw new \UnexpectedValueException('Unexpected service: ' . $id)
        );
        $handler = new TestableEntryEditHandler($container);

        $html = $handler->renderEntry($entry);

        self::assertStringContainsString('class="row g-0 rounded border bg-light p-3 mt-3"', $html);
        self::assertStringContainsString('class="list-group mb-4"', $html);
        self::assertStringContainsString('class="mb-0 text-wrap text-break"', $html);
        self::assertStringNotContainsString('dl-horizontal', $html);
        self::assertStringNotContainsString('jumbotron', $html);
        self::assertStringNotContainsString('style=', $html);
        self::assertStringNotContainsString('<script>user</script>', $html);
        self::assertStringNotContainsString('<img src=x onerror=alert(1)>', $html);
        self::assertStringNotContainsString('<strong>value</strong>', $html);
        self::assertStringContainsString('&lt;script&gt;user&lt;/script&gt;', $html);
        self::assertStringContainsString('&lt;strong&gt;value&lt;/strong&gt;', $html);

        $document = new \DOMDocument();
        self::assertTrue($document->loadHTML('<!doctype html><html><body>' . $html . '</body></html>'));
        $xpath = new \DOMXPath($document);
        self::assertSame(7, self::queryCount($xpath, '//dl/dt'));
        self::assertSame(7, self::queryCount($xpath, '//dl/dd'));
        self::assertSame(2, self::queryCount($xpath, '//div[contains(@class, "list-group-item")]'));
    }

    private static function queryCount(\DOMXPath $xpath, string $expression): int
    {
        $nodes = $xpath->query($expression);
        if ($nodes === false) {
            throw new \RuntimeException('Invalid XPath expression: ' . $expression);
        }

        return $nodes->length;
    }
}

final class TestableEntryEditHandler extends EntryEditHandler
{
    /** @return array<string, string> */
    public function flatten(mixed $value): array
    {
        return $this->convertValueToList($value);
    }

    public function renderEntry(LoggerEntry $entry): string
    {
        $entry_id = $entry->getId() ?? 1;

        return $this->renderRecordHead($entry_id) . $this->renderObjectFields($entry_id);
    }
}
