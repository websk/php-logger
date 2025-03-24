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
    const string DUMP_FILE_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'dumps' . DIRECTORY_SEPARATOR . 'db_logger.sql';
    const string DB_SERVICE_CONTAINER_ID = 'logger.db_service';
    const string DB_ID = 'db_logger';

    const string SETTINGS_CONTAINER_ID = 'settings';
    const string PARAM_DB = 'db';

    /**
     * @param ContainerInterface $container
     */
    public static function register(ContainerInterface $container): void
    {
        /**
         * @param ContainerInterface $container
         * @return LoggerEntryService
         */
        $container[LoggerEntryService::class] = function (ContainerInterface $container) {
            return new LoggerEntryService(
                LoggerEntry::class,
                $container->get(LoggerEntryRepository::class),
                $container->get(CacheServiceProvider::SERVICE_CONTAINER_ID)
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
            $db_config = $container->get(
                self::SETTINGS_CONTAINER_ID . '.' . self::PARAM_DB . '.' . self::DB_ID
            );

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
}
