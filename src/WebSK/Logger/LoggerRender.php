<?php

namespace WebSK\Logger;

use WebSK\Logger\RequestHandlers\ObjectEntriesListHandler;
use WebSK\Entity\InterfaceEntity;
use WebSK\Slim\Router;
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
        $entity_full_id = FullObjectId::getFullObjectId($entity_obj);
        return Router::urlFor(
            ObjectEntriesListHandler::class,
            ['object_full_id' => urlencode($entity_full_id)]
        );
    }
}
