<?php

namespace Mobilo\WpTheme\PageTemplates;

defined('ABSPATH') || exit;

abstract class PageTemplateLoader
{
    abstract public function init();
    public $pageId = 'page';

    public function load()
    {
        try {
            // Initialize the template loader
            $this->init();
        } catch (\Throwable $e) {
            mobilo_log(__METHOD__, $e->getMessage(), $e->getTrace());
        }
    }
}
