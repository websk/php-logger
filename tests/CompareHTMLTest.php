<?php

namespace WebSK\Logger\Tests;

use PHPUnit\Framework\TestCase;
use WebSK\Logger\CompareHTML;

final class CompareHTMLTest extends TestCase
{
    public function testEncodesComparedContentForJavascriptContext(): void
    {
        $html = CompareHTML::drawCompare('</script><script>alert(1)</script>', 'safe', 'field-id');

        self::assertStringNotContainsString('</script><script>alert(1)</script>', $html);
        self::assertStringContainsString('\\u003C\\/script\\u003E', $html);
        self::assertStringContainsString('id="field-id"', $html);
    }
}
