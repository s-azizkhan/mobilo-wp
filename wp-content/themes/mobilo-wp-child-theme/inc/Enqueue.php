<?php

namespace Mobilo\WpTheme;

defined('ABSPATH') || exit;

use Mobilo\WpTheme\Actions\Cart\CartAjaxActions;
use Mobilo\WpTheme\Feature\EDOFeature;
use Mobilo\WpTheme\PageTemplates\CartAjaxOnlyPageTemplate;
use Mobilo\WpTheme\PageTemplates\CheckoutAjaxOnlyPageTemplate;
use Mobilo\WpTheme\PageTemplates\CheckoutPageTemplate;
use Mobilo\WpTheme\PageTemplates\CartPageTemplate;
use Mobilo\WpTheme\PageTemplates\CommonPageTemplate;
use Mobilo\WpTheme\Admin\AdminPageLoader;



class Enqueue
{
    /**
     * Initialize all theme enqueue actions.
     */
    public function init()
    {
        $current_url = home_url(add_query_arg([], $_SERVER["REQUEST_URI"]));
        // get from which page request is coming from
        if (!is_admin() && !strpos($current_url, "wp-json")) {
            (new CommonPageTemplate())->load(true);
            // Cart page template
            (new CartPageTemplate())->load();
            // Checkout page template
            (new CheckoutPageTemplate())->load();

        } else {
            // TODO: Load API functions here
        }
        // Page specific ajax actions (load specific ajax actions on specific page)
        if (isset($_SERVER['HTTP_REFERER'])) {
            if (strpos($_SERVER['HTTP_REFERER'], "cart")) {
                // ini cart page template
                new CartAjaxOnlyPageTemplate();
                // if ajax request then load ajax actions
                (new CartAjaxActions())->init();
            }
            // on checkout only
            if (strpos($_SERVER['HTTP_REFERER'], "checkout")) {
                (new EDOFeature())->init_ajax();
                new CheckoutAjaxOnlyPageTemplate();
            }
        }

        // load required functions on admin page
        if (is_admin()) {
            (new AdminPageLoader())->load();
        }
    }
}
