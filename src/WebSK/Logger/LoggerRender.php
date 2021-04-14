<?php

namespace WebSK\Logger;

use Slim\Interfaces\RouterInterface;
use WebSK\Logger\RequestHandlers\ObjectEntriesListHandler;
use WebSK\Slim\Container;
use WebSK\Entity\InterfaceEntity;
use WebSK\Utils\FullObjectId;

/**
 * Class LoggerRender
 * @package WebSK\Logger
 */
class LoggerRender
{
    /**
     * @param InterfaceEntity $entity_obj
     * @return string
     */
    public static function getLoggerLinkForEntityObj(InterfaceEntity $entity_obj): string
    {
        $container = Container::self();

        /** @var RouterInterface $router */
        $router = $container['router'];

        $entity_full_id = FullObjectId::getFullObjectId($entity_obj);
        $logger_link = $router->pathFor(
            ObjectEntriesListHandler::class,
            ['object_full_id' => urlencode($entity_full_id)]
        );

        return $logger_link;
    }
}
