<?php
/*
Plugin Name: Mobilo Helper MU
Description: An optimized helper must-use plugin for Mobilo.
Version: 1.0
Author: Hypertics AI
Author URI: https://hyperticsai.com
*/

defined("ABSPATH") || exit();

$mobilo_helper_mu = new MobiloHelperMU();

/**
 * Override active theme in front-end.
 */
class MobiloHelperMU
{
    public function __construct()
    {
        /**
         * Checks the current theme stylesheet and template to use "mobilo-optimize" only on WooCommerce cart page.
         * Otherwise, uses the normal WordPress theme.
         */
        /* Hook filters to override active theme in front-end */
        add_filter("pre_option_stylesheet", [
            $this,
            "mobilo_helper_mu_stylesheet",
        ]);
        add_filter("pre_option_template", [$this, "mobilo_helper_mu_template"]);

        // add_filter('template', array($this, 'get_template'));
        // add_filter('stylesheet', array($this, 'get_stylesheet'));
        // add_filter('pre_option_current_theme', array($this, 'current_theme'));

        // // @link: https://core.trac.wordpress.org/ticket/20027
        // add_filter('pre_option_stylesheet', array($this, 'get_stylesheet'));
        // add_filter('pre_option_template', array($this, 'get_template'));

        // // Handle custom theme roots.
        // add_filter('pre_option_stylesheet_root', array($this, 'get_stylesheet_root'));
        // add_filter('pre_option_template_root', array($this, 'get_template_root'));
    }

    /**
     * Return the stylesheet directory (theme folder) to load.
     * Switch to "mobilo-optimize" on WooCommerce cart page only.
     */
    function mobilo_helper_mu_stylesheet(string $stylesheet): string
    {
        return $this->mobilo_helper_mu_chosen_theme_option("stylesheet");
    }

    /**
     * Return the template directory (theme folder) to load.
     * Switch to "mobilo-optimize" on WooCommerce cart page only.
     */
    function mobilo_helper_mu_template(string $template): string
    {
        return $this->mobilo_helper_mu_chosen_theme_option("template");
    }

    /**
     * Get current chosen theme option (stylesheet or template).
     * Returns "mobilo-optimize" theme on WooCommerce cart page;
     * otherwise, returns the normal theme.
     *
     * @param string $option 'stylesheet' or 'template'
     * @return string Theme stylesheet or template folder name
     */
    function mobilo_helper_mu_chosen_theme_option(string $option): string
    {
        global $mc_theme;

        if (!isset($mc_theme)) {
            $mc_theme = [];
        }

        if (!isset($mc_theme[$option])) {
            $chosen_theme = $this->mobilo_helper_mu_chosen_theme();
            $all_themes = $this->mobilo_helper_mu_all_themes();

            if ($chosen_theme === false) {
                $mc_theme["stylesheet"] = $this->mobilo_helper_mu_current_theme(
                    "stylesheet",
                );
                $mc_theme["template"] = $this->mobilo_helper_mu_current_theme(
                    "template",
                );
            } else {
                $mc_theme["stylesheet"] =
                    $all_themes[$chosen_theme]->stylesheet;
                $mc_theme["template"] = $all_themes[$chosen_theme]->template;
            }
        }

        return $mc_theme[$option];
    }

    /**
     * Determine which theme to use.
     * Use "mobilo-optimize" on conditional page only.
     * Otherwise, return theme according to usual multi-theme logic.
     *
     * @return string|bool Folder name of chosen theme or FALSE for default
     */
    function mobilo_helper_mu_chosen_theme()
    {
        // TODO: add more conditional pages here
        // conditional page check: Switch to 'mobilo-optimize' theme only here
        // get current url
        $current_url = home_url(add_query_arg([], $_SERVER["REQUEST_URI"]));
        if (strpos($current_url, "checkout")) {
            return "mobilo-optimize";
        }
        // if (strpos($current_url, "cart")) {
        //     return "mobilo-optimize";
        // }

        return false;
    }

    /**
     * Return all available WordPress themes keyed by folder name.
     *
     * @return array Available themes from wp_get_themes()
     */
    function mobilo_helper_mu_all_themes()
    {
        static $all_themes = null;
        if ($all_themes === null) {
            $all_themes = wp_get_themes();
        }
        return $all_themes;
    }

    /**
     * Return current active theme's template or stylesheet.
     *
     * @param string $option 'template' or 'stylesheet'
     * @return string
     */
    function mobilo_helper_mu_current_theme($option)
    {
        $all_options = wp_load_alloptions();
        return isset($all_options[$option])
            ? $all_options[$option]
            : get_option($option);
    }

    /**
     * Return the template directory (theme folder) to load.
     * Switch to "mobilo-optimize" on WooCommerce cart page only.
     */
    public function get_template($template)
    {
        return $this->mobilo_helper_mu_chosen_theme_option("template");
    }

    /**
     * Return the stylesheet directory (theme folder) to load.
     * Switch to "mobilo-optimize" on WooCommerce cart page only.
     */
    public function get_stylesheet($stylesheet)
    {
        return $this->mobilo_helper_mu_chosen_theme_option("stylesheet");
    }

    /**
     * Return the current theme directory.
     */
    public function current_theme($theme)
    {
        return basename(dirname(__FILE__));
    }

    /**
     * Return the stylesheet root directory.
     */
    public function get_stylesheet_root($stylesheet_root)
    {
        return get_template_directory();
    }

    /**
     * Return the template root directory.
     */
    public function get_template_root($template_root)
    {
        return get_template_directory();
    }
}
// ------ Class End ------
