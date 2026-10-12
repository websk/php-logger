<?php

namespace WebSK\Logger\Tests;

use PHPUnit\Framework\TestCase;
use WebSK\Cache\CacheService;
use WebSK\Logger\Entry\LoggerEntry;
use WebSK\Logger\Entry\LoggerEntryRepository;
use WebSK\Logger\Entry\LoggerEntryService;

final class LoggerEntryServiceTest extends TestCase
{
    private LoggerEntryService $service;

    protected function setUp(): void
    {
        $repository = $this->createStub(LoggerEntryRepository::class);
        $cache_service = $this->createStub(CacheService::class);
        $this->service = new LoggerEntryService(LoggerEntry::class, $repository, $cache_service);
    }

    public function testExtractsNumericUserId(): void
    {
        $entry = new LoggerEntry();
        $entry->setUserFullId('WebSK\\User\\User.42');

        self::assertSame(42, $this->service->getUserIdForEntry($entry));
    }

    public function testReturnsNullForMissingOrInvalidUserId(): void
    {
        $entry = new LoggerEntry();
        self::assertNull($this->service->getUserIdForEntry($entry));

        $entry->setUserFullId('User.invalid');
        self::assertNull($this->service->getUserIdForEntry($entry));

        $entry->setUserFullId('User.0');
        self::assertNull($this->service->getUserIdForEntry($entry));
    }
}
