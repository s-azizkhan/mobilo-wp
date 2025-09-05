<?php

namespace Mobilo\WpTheme\PageTemplates;

use Throwable;

defined('ABSPATH') || exit;

abstract class PageTemplateLoader
{
    abstract public function init();
    public $pageId = 'page';

    public function __construct()
    {
        // initialize common things here
    }

    public function load($force = false)
    {
        try {
            // Initialize the template loader
            $current_url = home_url(add_query_arg([], $_SERVER["REQUEST_URI"]));
            if (strpos($current_url, $this->pageId) || $force) {
                add_action('wp_loaded', [$this, 'init']);
                return true;
            }
            return false;
        } catch (Throwable $e) {
            mobilo_log(__METHOD__, $e->getMessage(), $e->getTrace());
        }
    }
}
