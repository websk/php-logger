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

    /** @param ContainerInterface&\ArrayAccess<array-key, mixed> $container */
    public static function register(ContainerInterface&\ArrayAccess $container): void
    {
        /**
         * @param ContainerInterface $container
         * @return LoggerEntryService
         */
        $container[LoggerEntryService::class] = static function (ContainerInterface $container): LoggerEntryService {
            $repository = $container->get(LoggerEntryRepository::class);
            if (!$repository instanceof LoggerEntryRepository) {
                throw new \UnexpectedValueException('Logger entry repository is not registered');
            }

            $cache_service = CacheServiceProvider::getCacheService($container);

            return new LoggerEntryService(
                LoggerEntry::class,
                $repository,
                $cache_service
            );
        };

        /**
         * @param ContainerInterface $container
         * @return LoggerEntryRepository
         */
        $container[LoggerEntryRepository::class] = static function (ContainerInterface $container): LoggerEntryRepository {
            $db_service = $container->get(self::DB_SERVICE_CONTAINER_ID);
            if (!$db_service instanceof DBService) {
                throw new \UnexpectedValueException('Logger database service is not registered');
            }

            return new LoggerEntryRepository(
                LoggerEntry::class,
                $db_service
            );
        };

        /**
         * @param ContainerInterface $container
         * @return DBService
         */
        $container[self::DB_SERVICE_CONTAINER_ID] = static function (ContainerInterface $container): DBService {
            $db_config = $container->get(
                self::SETTINGS_CONTAINER_ID . '.' . self::PARAM_DB . '.' . self::DB_ID
            );
            if (!is_array($db_config)) {
                throw new \UnexpectedValueException('Logger database configuration must be an array');
            }

            foreach ($db_config as $key => $value) {
                if (!is_string($key) || !is_string($value)) {
                    throw new \UnexpectedValueException('Logger database configuration must contain string values');
                }
            }

            return DBServiceFactory::factoryMySQL($db_config);
        };
    }

    /**
     * @param ContainerInterface $container
     * @return LoggerEntryService
     */
    public static function getEntryService(ContainerInterface $container): LoggerEntryService
    {
        $entry_service = $container->get(LoggerEntryService::class);
        if (!$entry_service instanceof LoggerEntryService) {
            throw new \UnexpectedValueException('Logger entry service is not registered');
        }

        return $entry_service;
    }
}
