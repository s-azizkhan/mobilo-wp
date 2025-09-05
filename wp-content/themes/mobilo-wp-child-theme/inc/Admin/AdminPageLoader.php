<?php

namespace Mobilo\WpTheme\Admin;

use Mobilo\WpTheme\Feature\OrderSyncFeature;
use Mobilo\WpTheme\Feature\OrganizationIdManager;
use Mobilo\WpTheme\Feature\OrgOrderProration;
use Mobilo\WpTheme\Feature\AutoSubscriptionCreateFromManualOrder;

defined('ABSPATH') || exit;

class AdminPageLoader
{
    public function __construct()
    {
    }

    public function load()
    {
        add_action('admin_init', [$this, 'init']);
        (new OrgOrderProration())->adminInit();
        (new AutoSubscriptionCreateFromManualOrder())->run();
        (new OrderSyncFeature())->run();
    }

    public function init()
    {
        (new OrganizationIdManager())->init();
    }
}