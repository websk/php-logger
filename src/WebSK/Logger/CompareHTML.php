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
        $dom_element_id = self::normalizeElementId($element_id);
        $element_id_json = self::encodeJson($dom_element_id);
        $element_label_json = self::encodeJson(self::normalizeElementLabel($element_id));

        $html = '';

        if (!isset($include_script)) {
            $include_script = false;

            ob_start();
            ?>
            <link rel="stylesheet" href="/assets/libraries/highlight.js/styles/github.css">
            <link rel="stylesheet" href="/assets/libraries/diff2html/css/diff2html.min.css">

            <script src="/assets/libraries/diff2html/js/diff2html-ui.min.js"></script>
            <script src="/assets/libraries/diff/diff.min.js"></script>
            <?php
            $html .= self::getOutputBuffer();
        }

        ob_start();
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const left_content = <?php echo $left_content_json; ?>;
                const right_content = <?php echo $right_content_json; ?>;
                const element_id = <?php echo $element_id_json; ?>;
                const element_label = <?php echo $element_label_json; ?>;

                const diff = window.Diff.createTwoFilesPatch(
                    element_label,
                    element_label,
                    left_content,
                    right_content
                );

                const targetElement = document.getElementById(element_id);
                const diff2htmlUi = new window.Diff2HtmlUI(
                    targetElement,
                    diff,
                    {
                        drawFileList: false,
                        matching: 'lines',
                        outputFormat: 'side-by-side'
                    }
                );
                diff2htmlUi.draw();
            });
        </script>

        <div id="<?php echo Sanitize::sanitizeAttrValue($dom_element_id); ?>" class="overflow-auto mb-4"></div>

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

    private static function normalizeElementId(string $element_id): string
    {
        if (preg_match('/^[A-Za-z][A-Za-z0-9_.:-]*$/D', $element_id) === 1) {
            return $element_id;
        }

        return 'logger-diff-' . hash('sha256', $element_id);
    }

    private static function normalizeElementLabel(string $element_id): string
    {
        $last_null_byte_position = strrpos($element_id, "\0");
        if ($last_null_byte_position === false) {
            return $element_id;
        }

        return substr($element_id, $last_null_byte_position + 1);
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
