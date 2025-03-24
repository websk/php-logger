<?php

namespace WebSK\Logger;

use Fig\Http\Message\RequestMethodInterface;
use Slim\Interfaces\RouteCollectorProxyInterface;
use WebSK\Logger\RequestHandlers\EntriesListHandler;
use WebSK\Logger\RequestHandlers\EntryEditHandler;
use WebSK\Logger\RequestHandlers\ObjectEntriesListHandler;

/**
 * Class LoggerRoutes
 * @package WebSK\Logger
 */
class LoggerRoutes
{

    /**
     * @param RouteCollectorProxyInterface $route_collector_proxy
     */
    public static function registerAdmin(RouteCollectorProxyInterface $route_collector_proxy): void
    {
        $route_collector_proxy->group('/logger', function (RouteCollectorProxyInterface $route_collector_proxy) {
            $route_collector_proxy->group('/entry', function (RouteCollectorProxyInterface $route_collector_proxy) {
                $route_collector_proxy->map([RequestMethodInterface::METHOD_GET, RequestMethodInterface::METHOD_POST], '', EntriesListHandler::class)
                    ->setName(EntriesListHandler::class);

                $route_collector_proxy->map([RequestMethodInterface::METHOD_GET, RequestMethodInterface::METHOD_POST], '/{entry_id:\d+}', EntryEditHandler::class)
                    ->setName(EntryEditHandler::class);
            });
            $route_collector_proxy->map([RequestMethodInterface::METHOD_GET, RequestMethodInterface::METHOD_POST], '/objectentries/{object_full_id:.+}', ObjectEntriesListHandler::class)
                ->setName(ObjectEntriesListHandler::class);
        });
    }
}
