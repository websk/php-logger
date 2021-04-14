<?php

namespace WebSK\Logger\Demo;

use Slim\App;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Slim\Http\Request;
use Slim\Http\Response;
use WebSK\Auth\AuthServiceProvider;
use WebSK\Auth\User\UserRoutes;
use WebSK\Auth\User\UserServiceProvider;
use WebSK\Cache\CacheServiceProvider;
use WebSK\CRUD\CRUDServiceProvider;
use WebSK\DB\DBWrapper;
use WebSK\Logger\LoggerConfig;
use WebSK\Logger\LoggerRoutes;
use WebSK\Logger\LoggerServiceProvider;
use WebSK\Logger\RequestHandlers\EntriesListHandler;
use WebSK\Slim\Facade;
use WebSK\Slim\Router;

/**
 * Class LoggerDemoApp
 * @package WebSK\Logger\Demo
 */
class LoggerDemoApp extends App
{
    /**
     * LoggerDemoApp constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $container = $this->getContainer();

        CacheServiceProvider::register($container);
        LoggerServiceProvider::register($container);
        CRUDServiceProvider::register($container);
        AuthServiceProvider::register($container);
        UserServiceProvider::register($container);

        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        $container = $this->getContainer();
        $container['foundHandler'] = function () {
            return new RequestResponseArgs();
        };

        // Demo routing. Redirects
        $this->get('/', function (Request $request, Response $response) {
            return $response->withRedirect(Router::pathFor(EntriesListHandler::class));
        });
        $this->get(LoggerConfig::getAdminMainPageUrl(), function (Request $request, Response $response) {
            return $response->withRedirect(Router::pathFor(EntriesListHandler::class));
        });

        $this->group('/admin', function (App $app) {
            LoggerRoutes::registerAdmin($app);
            UserRoutes::registerAdmin($app);
        });

        UserRoutes::register($this);

        /** Use facade */
        Facade::setFacadeApplication($this);

        /** Set DBWrapper db service */
        DBWrapper::setDbService(LoggerServiceProvider::getDBService($container));
    }
}
