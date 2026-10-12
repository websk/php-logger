<?php

namespace WebSK\Logger\Demo;

use DI\Container;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Handlers\ErrorHandler;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Psr7\Factory\ResponseFactory;
use WebSK\Cache\CacheServiceProvider;
use WebSK\CRUD\CRUDServiceProvider;
use WebSK\CRUD\MutableContainerAdapter;
use WebSK\Logger\LoggerConfig;
use WebSK\Logger\LoggerRoutes;
use WebSK\Logger\LoggerServiceProvider;
use WebSK\Logger\RequestHandlers\EntriesListHandler;
use WebSK\Slim\Router;

/**
 * Class LoggerDemoApp
 * @package WebSK\Logger\Demo
 */
/** @extends App<Container> */
class LoggerDemoApp extends App
{
    /**
     * LoggerDemoApp constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct(new ResponseFactory(), $container);

        $this->registerRouterSettings($container);

        $mutable_container = new MutableContainerAdapter($container);
        CacheServiceProvider::register($mutable_container);
        LoggerServiceProvider::register($mutable_container);
        CRUDServiceProvider::register($mutable_container);

        $this->registerRoutes();

        $error_middleware = $this->addErrorMiddleware(true, true, true);
        $error_middleware->setDefaultErrorHandler(ErrorHandler::class);
    }

    /**
     * @param Container $container
     */
    protected function registerRouterSettings(Container $container): void
    {
        $route_collector = $this->getRouteCollector();
        $invocation_strategy = $container->get(InvocationStrategyInterface::class);
        if (!$invocation_strategy instanceof InvocationStrategyInterface) {
            throw new \UnexpectedValueException('Slim invocation strategy is not registered');
        }
        $route_collector->setDefaultInvocationStrategy($invocation_strategy);
        $route_parser = $route_collector->getRouteParser();

        $container->set(RouteParserInterface::class, $route_parser);
    }

    protected function registerRoutes(): void
    {
        // Demo routing. Redirects
        $this->get('/', function (ServerRequestInterface $request, ResponseInterface $response) {
            return $response->withHeader('Location', Router::urlFor(EntriesListHandler::class))
                ->withStatus(StatusCodeInterface::STATUS_TEMPORARY_REDIRECT);
        });

        $this->get(LoggerConfig::getAdminMainPageUrl(), function (ServerRequestInterface $request, ResponseInterface $response) {
            return $response->withHeader('Location', Router::urlFor(EntriesListHandler::class))
                ->withStatus(StatusCodeInterface::STATUS_TEMPORARY_REDIRECT);
        });

        $this->group('/admin', function (RouteCollectorProxyInterface $route_collector_proxy) {
            LoggerRoutes::registerAdmin($route_collector_proxy);
        });
    }
}
