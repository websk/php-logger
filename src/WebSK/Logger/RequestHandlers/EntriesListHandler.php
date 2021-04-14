<?php

namespace WebSK\Logger\RequestHandlers;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use WebSK\CRUD\Table\Filters\CRUDTableFilterEqualTimestampIntervalInline;
use WebSK\Logger\LoggerConfig;
use WebSK\Slim\RequestHandlers\BaseHandler;
use WebSK\Views\LayoutDTO;
use WebSK\Views\BreadcrumbItemDTO;
use WebSK\CRUD\CRUDServiceProvider;
use WebSK\CRUD\Table\CRUDTable;
use WebSK\CRUD\Table\CRUDTableColumn;
use WebSK\CRUD\Table\Filters\CRUDTableFilterLike;
use WebSK\CRUD\Table\Widgets\CRUDTableWidgetText;
use WebSK\CRUD\Table\Widgets\CRUDTableWidgetTextWithLink;
use WebSK\CRUD\Table\Widgets\CRUDTableWidgetTimestamp;
use WebSK\Logger\Entry\LoggerEntry;
use WebSK\Views\PhpRender;

/**
 * Class EntriesListHandler
 * @package WebSK\Logger\RequestHandlers
 */
class EntriesListHandler extends BaseHandler
{
    const FILTER_OBJECT_FULL_ID = 'object_full_id';
    const FILTER_USER_FULL_ID = 'user_full_id';
    const FILTER_USER_IP = 'user_ip';
    const FILTER_CREATED_AT_TS_START = 'created_at_ts_start';
    const FILTER_CREATED_AT_TS_END = 'created_at_ts_end';

    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     * @throws \Exception
     */
    public function __invoke(Request $request, Response $response): ResponseInterface
    {
        $crud_table_obj = CRUDServiceProvider::getCrud($this->container)->createTable(
            LoggerEntry::class,
            null,
            [
                new CRUDTableColumn(
                    'ID',
                    new CRUDTableWidgetTextWithLink(
                        LoggerEntry::_ID,
                        function (LoggerEntry $logger_entry) {
                            return $this->pathFor(EntryEditHandler::class, ['entry_id' =>$logger_entry->getId()]);
                        }
                    )
                ),
                new CRUDTableColumn(
                    'Объект',
                    new CRUDTableWidgetTextWithLink(
                        LoggerEntry::_OBJECT_FULL_ID,
                        function (LoggerEntry $logger_entry) {
                            return $this->pathFor(ObjectEntriesListHandler::class, ['object_full_id' => urlencode($logger_entry->getObjectFullId())]);
                        }
                    )
                ),
                new CRUDTableColumn(
                    'Дата создания',
                    new CRUDTableWidgetTimestamp(LoggerEntry::_CREATED_AT_TS)
                ),
                new CRUDTableColumn(
                    'Пользователь',
                    new CRUDTableWidgetText(
                        LoggerEntry::_USER_FULL_ID
                    )
                ),
                new CRUDTableColumn(
                    'IP',
                    new CRUDTableWidgetText(LoggerEntry::_USER_IP)
                ),
                new CRUDTableColumn(
                    '',
                    new CRUDTableWidgetText(LoggerEntry::_COMMENT)
                ),
            ],
            [
                new CRUDTableFilterLike(
                    self::FILTER_OBJECT_FULL_ID,
                    'Object Full ID',
                    LoggerEntry::_OBJECT_FULL_ID,
                    'Можно указать только часть объекта, например, Contractor.1'
                ),
                new CRUDTableFilterLike(
                    self::FILTER_USER_FULL_ID,
                    'User Full ID',
                    LoggerEntry::_USER_FULL_ID,
                    'Можно указать только часть объекта, например, User.1'
                ),
                new CRUDTableFilterLike(self::FILTER_USER_IP, 'User IP', LoggerEntry::_USER_IP),
                new CRUDTableFilterEqualTimestampIntervalInline(
                    self::FILTER_CREATED_AT_TS_START,
                    self::FILTER_CREATED_AT_TS_END,
                    'Дата создания',
                    LoggerEntry::_CREATED_AT_TS
                ),
            ],
            LoggerEntry::_CREATED_AT_TS . ' desc',
            'logger_entries_list',
            CRUDTable::FILTERS_POSITION_TOP
        );
        $crud_table_response = $crud_table_obj->processRequest($request, $response);
        if ($crud_table_response instanceof Response) {
            return $crud_table_response;
        }

        $layout_dto = new LayoutDTO();
        $layout_dto->setTitle('Журналы');
        $layout_dto->setContentHtml($crud_table_obj->html($request));
        $breadcrumbs_arr = [
            new BreadcrumbItemDTO('Главная', LoggerConfig::getAdminMainPageUrl()),
        ];
        $layout_dto->setBreadcrumbsDtoArr($breadcrumbs_arr);

        return PhpRender::renderLayout($response, LoggerConfig::getAdminLayout(), $layout_dto);
    }
}
