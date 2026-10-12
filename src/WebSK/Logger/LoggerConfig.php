<?php

namespace WebSK\Logger;

use WebSK\Config\ConfWrapper;

/**
 * Class LoggerConfig
 * @package WebSK\Logger
 */
class LoggerConfig
{

    /**
     * @return string
     */
    public static function getMainLayout(): string
    {
        return self::getString('logger.layout_main', self::getString('layout.main'));
    }

    /**
     * @return string
     */
    public static function getMainPageUrl(): string
    {
        return self::getString('logger.main_page_url', '/');
    }

    /**
     * @return string
     */
    public static function getAdminLayout(): string
    {
        return self::getString('logger.layout_admin', self::getString('layout.admin'));
    }

    /**
     * @return string
     */
    public static function getAdminMainPageUrl(): string
    {
        return self::getString('logger.admin_main_page_url', '/admin');
    }

    /**
     * @return string
     */
    public static function getUserProfileRouteName(): string
    {
        return self::getString('logger.user_profile_route_name', '');
    }

    /**
     * @return ?string
     */
    public static function getUserServiceContainerName(): ?string
    {
        $value = ConfWrapper::value('logger.user_service_container_name', null);
        if ($value !== null && !is_string($value)) {
            throw new \UnexpectedValueException('logger.user_service_container_name must be a string or null');
        }

        return $value;
    }

    private static function getString(string $path, string $default = ''): string
    {
        $value = ConfWrapper::value($path, $default);
        if (!is_string($value)) {
            throw new \UnexpectedValueException($path . ' must be a string');
        }

        return $value;
    }
}
