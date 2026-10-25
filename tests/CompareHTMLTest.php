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
        self::assertStringContainsString('id="field-id" class="overflow-auto mb-4"', $html);
        self::assertStringContainsString('/assets/libraries/highlight.js/styles/github.css', $html);
        self::assertStringContainsString('/assets/libraries/diff2html/css/diff2html.min.css', $html);
        self::assertStringContainsString('/assets/libraries/diff2html/js/diff2html-ui.min.js', $html);
        self::assertStringContainsString('/assets/libraries/diff/diff.min.js', $html);
        self::assertStringNotContainsString('https://', $html);
    }

    public function testReplacesInvalidSerializedPropertyElementId(): void
    {
        $html = CompareHTML::drawCompare('old', 'new', "\0*\0body");

        self::assertStringNotContainsString("\0", $html);
        self::assertMatchesRegularExpression('/id="logger-diff-[a-f0-9]{64}"/', $html);
        self::assertStringContainsString('const element_label = "body";', $html);
    }
}
