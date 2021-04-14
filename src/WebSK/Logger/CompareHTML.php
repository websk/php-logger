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
            $html .= ob_get_clean();
        }

        ob_start();
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                var left_content = $('<textarea />').html(`<?php echo Sanitize::sanitizeTagContent($left_content); ?>`).text();
                var right_content = $('<textarea />').html(`<?php echo Sanitize::sanitizeTagContent($right_content); ?>`).text();

                var diff = window.Diff.createTwoFilesPatch("<?php echo $element_id; ?>", "<?php echo $element_id; ?>", left_content, right_content);

                const targetElement = document.getElementById('<?php echo $element_id; ?>');
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

        <div id="<?php echo $element_id; ?>"></div>

        <?php
        $html .= ob_get_clean();

        return $html;
    }
}
