<?php
namespace Mobilo\WpTheme\Interfaces;

defined('ABSPATH') || exit;

interface AjaxActionLoaderInterface {
    public function loadAjaxAction($ajaxClass);
}