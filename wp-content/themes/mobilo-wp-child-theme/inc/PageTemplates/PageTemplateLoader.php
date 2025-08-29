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

    public function load()
    {
        try {
            // Initialize the template loader
            add_action('init', [$this, 'init']);
        } catch (Throwable $e) {
            mobilo_log(__METHOD__, $e->getMessage(), $e->getTrace());
        }
    }
}
