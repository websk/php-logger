<?php

namespace WebSK\Logger\Tests;

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use WebSK\Logger\Table\AccessibleTimestampIntervalFilter;

final class AccessibleTimestampIntervalFilterTest extends TestCase
{
    public function testLabelsBothIntervalInputs(): void
    {
        $filter = new AccessibleTimestampIntervalFilter(
            'created_at_ts_start',
            'created_at_ts_end',
            'Дата создания',
            'created_at_ts'
        );

        $html = $filter->getHtml(
            (new ServerRequestFactory())->createServerRequest('GET', '/admin/logger/entry')
        );

        self::assertStringContainsString(
            'id="created_at_ts_start" aria-label="Дата создания: с"',
            $html
        );
        self::assertStringContainsString(
            'id="created_at_ts_end" aria-label="Дата создания: по"',
            $html
        );
    }
}
