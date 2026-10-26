<?php

namespace WebSK\Logger\Table;

use Psr\Http\Message\ServerRequestInterface;
use WebSK\CRUD\Table\Filters\CRUDTableFilterEqualTimestampIntervalInline;
use WebSK\Utils\Sanitize;

final class AccessibleTimestampIntervalFilter extends CRUDTableFilterEqualTimestampIntervalInline
{
    public function getHtml(ServerRequestInterface $request): string
    {
        $html = parent::getHtml($request);

        $html = $this->addLabel($html, $this->getFilterStartUniqId(), $this->getTitle() . ': с');

        return $this->addLabel($html, $this->getFilterEndUniqId(), $this->getTitle() . ': по');
    }

    private function addLabel(string $html, string $id, string $label): string
    {
        $safe_id = Sanitize::sanitizeAttrValue($id);
        $safe_label = Sanitize::sanitizeAttrValue($label);

        return str_replace(
            'id="' . $safe_id . '"',
            'id="' . $safe_id . '" aria-label="' . $safe_label . '"',
            $html
        );
    }
}
