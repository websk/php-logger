<?php

namespace WebSK\Logger;

use WebSK\Entity\InterfaceEntity;
use WebSK\Slim\Facade;
use WebSK\Logger\Entry\LoggerEntryService;

/**
 * Class Logger
 * @see LoggerEntryService
 * @method static logObjectEvent(InterfaceEntity $object, string $comment, ?string $user_full_id)
 * @package WebSK\Logger
 */
class Logger extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return LoggerEntryService::class;
    }
}
