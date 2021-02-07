<?php

namespace WebSK\Logger\RequestHandlers;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
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
use WebSK\Logger\LoggerRoutes;
use WebSK\Views\PhpRender;

/**
 * Class EntriesListHandler
 * @package WebSK\Logger\RequestHandlers
 */
class EntriesListHandler extends BaseHandler
{
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
                    'Объект',
                    new CRUDTableWidgetText(LoggerEntry::_OBJECT_FULLID)
                ),
                new CRUDTableColumn(
                    'Дата создания',
                    new CRUDTableWidgetTimestamp(LoggerEntry::_CREATED_AT_TS)
                ),
                new CRUDTableColumn(
                    'Пользователь',
                    new CRUDTableWidgetTextWithLink(
                        LoggerEntry::_USER_FULLID,
                        function (LoggerEntry $logger_entry) {
                            return $this->pathFor(LoggerRoutes::ROUTE_NAME_ADMIN_LOGGER_ENTRY_EDIT, ['entry_id' => $logger_entry->getId()]);
                        }
                    )
                )
            ],
            [
                new CRUDTableFilterLike('object_full_id', 'Object Full ID', LoggerEntry::_OBJECT_FULLID),
            ],
            LoggerEntry::_CREATED_AT_TS . ' DESC',
            'loger_entries_list',
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
