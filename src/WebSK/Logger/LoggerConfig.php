<?php

namespace WebSK\Logger;

use WebSK\Config\ConfWrapper;

/**
 * Class LoggerConfig
 * @package WebSK\Auth
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
    public static function getSkifLayout(): string
    {
        return ConfWrapper::value('logger.layout_skif', ConfWrapper::value('skif.layout'));
    }

    /**
     * @return string
     */
    public static function getSkifMainPageUrl(): string
    {
        return ConfWrapper::value('logger.skif_main_page_url', '/admin');
    }
}
