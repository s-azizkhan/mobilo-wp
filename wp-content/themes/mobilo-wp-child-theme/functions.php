<?php

/**
 * Mobilo functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Mobilo
 */


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
// Define theme url
if (!defined('MOBILO_THEME_URL')) {
    define('MOBILO_THEME_URL', get_stylesheet_directory_uri());
}

// Define theme url
if (!defined('MOBILO_THEME_PATH')) {
    define('MOBILO_THEME_PATH', get_stylesheet_directory());
}

// Autoload Composer (and PSR-4 classes)
if (file_exists(MOBILO_THEME_PATH . '/vendor/autoload.php')) {
    require_once MOBILO_THEME_PATH . '/vendor/autoload.php';
}
if (class_exists('Mobilo\WpTheme\Utils\InitConstants')) {
    \Mobilo\WpTheme\Utils\InitConstants::init();
}

// To enqueue page-specific assets via a modular approach:
if (class_exists('Mobilo\WpTheme\Enqueue')) {
    (new \Mobilo\WpTheme\Enqueue())->init();
}
// TODO: use WPCacheEngine for caching
