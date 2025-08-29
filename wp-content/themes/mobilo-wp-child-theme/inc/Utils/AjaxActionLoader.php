<?php
namespace Mobilo\WpTheme\Utils;

use Mobilo\WpTheme\Interfaces\AjaxActionLoaderInterface;
use Throwable;

defined('ABSPATH') || exit;

class AjaxActionLoader implements AjaxActionLoaderInterface
{
    public function loadAjaxAction($ajaxClass)
    {
        if (!class_exists($ajaxClass)) {
            return;
        }

        try {
            (new $ajaxClass())->load();
        } catch (Throwable $e) {
            mobilo_log(__METHOD__, $e->getMessage());
        }
    }
}

