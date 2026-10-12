<?php

namespace WebSK\Logger\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
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
}

final class TestableEntryEditHandler extends EntryEditHandler
{
    /** @return array<string, string> */
    public function flatten(mixed $value): array
    {
        return $this->convertValueToList($value);
    }
}
