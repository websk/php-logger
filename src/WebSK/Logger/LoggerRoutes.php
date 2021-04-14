<?php

namespace WebSK\Logger;

use Slim\App;
use WebSK\Utils\HTTP;
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
     * @param App $app
     */
    public static function registerAdmin(App $app)
    {
        $app->group('/logger', function (App $app) {
            $app->group('/entry', function (App $app) {
                $app->map([HTTP::METHOD_GET, HTTP::METHOD_POST], '', EntriesListHandler::class)
                    ->setName(EntriesListHandler::class);

                $app->map([HTTP::METHOD_GET, HTTP::METHOD_POST], '/{entry_id:\d+}', EntryEditHandler::class)
                    ->setName(EntryEditHandler::class);
            });
            $app->map([HTTP::METHOD_GET, HTTP::METHOD_POST], '/objectentries/{object_full_id:.+}', ObjectEntriesListHandler::class)
                ->setName(ObjectEntriesListHandler::class);
        });
    }
}
