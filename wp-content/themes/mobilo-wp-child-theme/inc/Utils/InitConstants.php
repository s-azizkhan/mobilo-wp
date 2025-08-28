<?php

namespace Mobilo\WpTheme\Utils;

defined('ABSPATH') || exit;

class InitConstants
{
    public static function init()
    {
        self::initConstants();
    }

    private static function initConstants()
    {

        if (!defined('MOBILO_VERSION')) {
            // Replace the version number of the theme on each release
            define('MOBILO_VERSION', '0.0.1');
        }

        // defining the theme prefix
        if (!defined('MOBILO_PREFIX')) {
            define('MOBILO_PREFIX', 'mc');
        }

        // TODO: change this based on env
        // Define apk api gateway address
        if (!defined('MOBILO_APK_GATEWAY')) {
            define('MOBILO_APK_GATEWAY', 'https://apk-gtw-dev.mobilocard.com');
        }

        // TODO: change this based on env
        // Define api gateway address
        if (!defined('MOBILO_API_GATEWAY')) {
            define('MOBILO_API_GATEWAY', 'https://api-gtw-dev.mobilocard.com');
        }

        // TODO: change this based on env
        // Define api key to access api gateway
        if (!defined('MOBILO_X_API_KEY')) {
            define('MOBILO_X_API_KEY', "AIzaSyAiPOPN07KhNxIBVeH2JvYNxKaWV8u7rXA");
        }

        // TODO: change this based on env
        // Define secret key
        if (!defined('MOBILO_WP_SECRET_KEY')) {
            // Replace the version number of the theme on each release
            define('MOBILO_WP_SECRET_KEY', 'VC2hnfJbkEddEkbJfnh2CV');
        }
    }
}
