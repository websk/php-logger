<?php

namespace WebSK\Logger\RequestHandlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WebSK\CRUD\Table\CRUDTable;
use WebSK\CRUD\Table\Filters\CRUDTableFilterEqualTimestampIntervalInline;
use WebSK\CRUD\Table\Filters\CRUDTableFilterLike;
use WebSK\Logger\LoggerConfig;
use WebSK\Views\LayoutDTO;
use WebSK\Slim\RequestHandlers\BaseHandler;
use WebSK\Views\BreadcrumbItemDTO;
use WebSK\CRUD\CRUDServiceProvider;
use WebSK\CRUD\Table\CRUDTableColumn;
use WebSK\CRUD\Table\Filters\CRUDTableFilterEqualInvisible;
use WebSK\CRUD\Table\Widgets\CRUDTableWidgetText;
use WebSK\CRUD\Table\Widgets\CRUDTableWidgetTextWithLink;
use WebSK\CRUD\Table\Widgets\CRUDTableWidgetTimestamp;
use WebSK\Logger\Entry\LoggerEntry;
use WebSK\Views\PhpRender;

/**
 * Class ObjectEntriesListHandler
 * @package WebSK\Logger\RequestHandlers
 */
class ObjectEntriesListHandler extends BaseHandler
{

    const FILTER_USER_FULL_ID = 'user_full_id';
    const FILTER_USER_IP = 'user_ip';
    const FILTER_CREATED_AT_TS_START = 'created_at_ts_start';
    const FILTER_CREATED_AT_TS_END = 'created_at_ts_end';

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param string $object_full_id
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, string $object_full_id): ResponseInterface
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
                new CRUDTableFilterEqualInvisible(LoggerEntry::_OBJECT_FULL_ID, $object_full_id),
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
            'logger_object_entries_list',
            CRUDTable::FILTERS_POSITION_TOP
        );

        $crud_table_response = $crud_table_obj->processRequest($request, $response);
        if ($crud_table_response instanceof ResponseInterface) {
            return $crud_table_response;
        }

        $layout_dto = new LayoutDTO();
        $layout_dto->setTitle($object_full_id);
        $layout_dto->setContentHtml($crud_table_obj->html($request));
        $breadcrumbs_arr = [
            new BreadcrumbItemDTO('Главная', LoggerConfig::getAdminMainPageUrl()),
            new BreadcrumbItemDTO(
                'Журналы',
                $this->pathFor(EntriesListHandler::class)
            ),
        ];
        $layout_dto->setBreadcrumbsDtoArr($breadcrumbs_arr);

        return PhpRender::renderLayout($response, LoggerConfig::getAdminLayout(), $layout_dto);
    }
}
