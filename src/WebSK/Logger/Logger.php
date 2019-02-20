<?php

namespace WebSK\Logger;

use WebSK\Entity\InterfaceEntity;
use WebSK\Slim\Facade;
use WebSK\Logger\Entry\LoggerEntry;
use WebSK\Logger\Entry\LoggerEntryService;

/**
 * Class Logger
 * @see LoggerEntryService
 * @method static logObjectEvent(InterfaceEntity $object, string $comment, ?string $user_full_id)
 * @package VitrinaTV\Logger
 */
class Logger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LoggerEntry::ENTITY_SERVICE_CONTAINER_ID;
    }
}
