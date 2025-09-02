<?php

namespace Mobilo\WpTheme\Admin;

use Mobilo\WpTheme\Feature\OrderSyncFeature;
use Mobilo\WpTheme\Feature\OrganizationIdManager;

defined('ABSPATH') || exit;

class AdminPageLoader
{
    public function __construct()
    {
    }

    public function load()
    {
        add_action('admin_init', [$this, 'init']);
    }

    public function init()
    {
        (new OrganizationIdManager())->init();
        (new OrderSyncFeature())->run();
    }
}