<?php

namespace WebSK\Logger\RequestHandlers;

use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
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
use WebSK\Logger\LoggerRoutes;
use WebSK\Views\PhpRender;

class ObjectEntriesListHandler extends BaseHandler
{
    /**
     * @param Request $request
     * @param Response $response
     * @param string $object_full_id
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response, string $object_full_id): ResponseInterface
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
                new CRUDTableFilterEqualInvisible('object_full_id', $object_full_id)
            ],
            LoggerEntry::_CREATED_AT_TS . ' DESC'
        );

        $crud_table_response = $crud_table_obj->processRequest($request, $response);
        if ($crud_table_response instanceof Response) {
            return $crud_table_response;
        }

        $layout_dto = new LayoutDTO();
        $layout_dto->setTitle($object_full_id);
        $layout_dto->setContentHtml($crud_table_obj->html($request));
        $breadcrumbs_arr = [
            new BreadcrumbItemDTO('Главная', LoggerConfig::getSkifMainPageUrl()),
            new BreadcrumbItemDTO(
                'Журналы',
                $this->pathFor(LoggerRoutes::ROUTE_NAME_ADMIN_LOGGER_ENTRIES_LIST)
            ),
        ];
        $layout_dto->setBreadcrumbsDtoArr($breadcrumbs_arr);

        return PhpRender::renderLayout($response, LoggerConfig::getSkifLayout(), $layout_dto);
    }
}
