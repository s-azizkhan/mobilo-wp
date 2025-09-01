<?php

namespace Mobilo\WpTheme;

use Mobilo\WpTheme\Actions\Cart\CartAjaxActions;
use Mobilo\WpTheme\PageTemplates\CartAjaxOnlyPageTemplate;

defined('ABSPATH') || exit;


use Mobilo\WpTheme\PageTemplates\CartPageTemplate;
use Mobilo\WpTheme\PageTemplates\CommonPageTemplate;

class Enqueue
{
    /**
     * Initialize all theme enqueue actions.
     */
    public function init()
    {
        $current_url = home_url(add_query_arg([], $_SERVER["REQUEST_URI"]));
        // get from which page request is coming from
        if (!strpos($current_url, "wp-json")) {
            (new CommonPageTemplate())->load();
            if (strpos($current_url, "cart")) {
                (new CartPageTemplate())->load();
            }
            // Page specific ajax actions (load specific ajax actions on specific page)
            if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], "cart")) {
                // ini cart page template
                new CartAjaxOnlyPageTemplate();
                // if ajax request then load ajax actions
                (new CartAjaxActions())->init();
            }

        } else {
            // TODO: Load API functions here
        }
    }
}
