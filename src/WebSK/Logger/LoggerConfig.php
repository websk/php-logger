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
        return ConfWrapper::value('logger.layout_main', ConfWrapper::value('layout.main'));
    }

    /**
     * @return string
     */
    public static function getMainPageUrl(): string
    {
        return ConfWrapper::value('logger.main_page_url', '/');
    }

    /**
     * @return string
     */
    public static function getAdminLayout(): string
    {
        return ConfWrapper::value('logger.layout_admin', ConfWrapper::value('layout.admin'));
    }

    /**
     * @return string
     */
    public static function getAdminMainPageUrl(): string
    {
        return ConfWrapper::value('logger.admin_main_page_url', '/admin');
    }
}
