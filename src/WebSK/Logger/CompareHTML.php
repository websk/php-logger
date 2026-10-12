<?php

namespace WebSK\Logger;

use WebSK\Utils\Sanitize;

/**
 * Class CompareHTML
 * @package WebSK\Logger
 */
class CompareHTML
{
    /**
     * @param string $left_content
     * @param string $right_content
     * @param string $element_id
     * @return string
     */
    public static function drawCompare(string  $left_content, string $right_content, string $element_id): string
    {
        static $include_script;

        $left_content_json = self::encodeJson($left_content);
        $right_content_json = self::encodeJson($right_content);
        $element_id_json = self::encodeJson($element_id);

        $html = '';

        if (!isset($include_script)) {
            $include_script = false;

            ob_start();
            ?>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/highlight.js@10.7.2/styles/github.css" />
            <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/diff2html@3.4.3/bundles/css/diff2html.min.css" />

            <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/diff2html@3.4.3/bundles/js/diff2html-ui.min.js"></script>
            <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/diff@5.0.0/dist/diff.min.js"></script>
            <?php
            $html .= self::getOutputBuffer();
        }

        ob_start();
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                var left_content = <?php echo $left_content_json; ?>;
                var right_content = <?php echo $right_content_json; ?>;
                var element_id = <?php echo $element_id_json; ?>;

                var diff = window.Diff.createTwoFilesPatch(element_id, element_id, left_content, right_content);

                const targetElement = document.getElementById(element_id);
                var diff2htmlUi = new window.Diff2HtmlUI(
                    targetElement,
                    diff,
                    {
                        drawFileList: false,
                        matching: 'lines',
                        outputFormat: 'side-by-side',

                    }
                );
                diff2htmlUi.draw();
            });
        </script>

        <div id="<?php echo Sanitize::sanitizeAttrValue($element_id); ?>"></div>

        <?php
        $html .= self::getOutputBuffer();

        return $html;
    }

    private static function encodeJson(string $value): string
    {
        $encoded = json_encode(
            $value,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
        );

        return $encoded;
    }

    private static function getOutputBuffer(): string
    {
        $output = ob_get_clean();
        if ($output === false) {
            throw new \RuntimeException('Could not read the compare output buffer');
        }

        return $output;
    }
}
