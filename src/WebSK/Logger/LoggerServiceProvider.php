<?php

namespace WebSK\Logger;

use Psr\Container\ContainerInterface;
use WebSK\Cache\CacheServiceProvider;
use WebSK\DB\DBService;
use WebSK\DB\DBServiceFactory;
use WebSK\Logger\Entry\LoggerEntry;
use WebSK\Logger\Entry\LoggerEntryRepository;
use WebSK\Logger\Entry\LoggerEntryService;

/**
 * Class LoggerServiceProvider
 * @package WebSK\Logger
 */
class LoggerServiceProvider
{
    const DUMP_FILE_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'dumps' . DIRECTORY_SEPARATOR . 'db_logger.sql';
    const DB_SERVICE_CONTAINER_ID = 'logger.db_service';
    const DB_ID = 'db_logger';

    /**
     * @param ContainerInterface $container
     */
    public static function register(ContainerInterface $container)
    {
        /**
         * @param ContainerInterface $container
         * @return LoggerEntryService
         */
        $container[LoggerEntryService::class] = function (ContainerInterface $container) {
            return new LoggerEntryService(
                LoggerEntry::class,
                $container->get(LoggerEntryRepository::class),
                CacheServiceProvider::getCacheService($container)
            );
        };

        /**
         * @param ContainerInterface $container
         * @return LoggerEntryRepository
         */
        $container[LoggerEntryRepository::class] = function (ContainerInterface $container) {
            return new LoggerEntryRepository(
                LoggerEntry::class,
                $container->get(self::DB_SERVICE_CONTAINER_ID)
            );
        };

        /**
         * @param ContainerInterface $container
         * @return DBService
         */
        $container[self::DB_SERVICE_CONTAINER_ID] = function (ContainerInterface $container): DBService {
            $db_config = $container['settings']['db'][self::DB_ID];

            return DBServiceFactory::factoryMySQL($db_config);
        };
    }

    /**
     * @param ContainerInterface $container
     * @return LoggerEntryService
     */
    public static function getEntryService(ContainerInterface $container): LoggerEntryService
    {
        return $container->get(LoggerEntryService::class);
    }

    /**
     * @param ContainerInterface $container
     * @return DBService
     */
    public static function getDBService(ContainerInterface $container): DBService
    {
        return $container->get(self::DB_SERVICE_CONTAINER_ID);
    }
}
