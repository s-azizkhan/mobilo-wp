<?php

namespace Mobilo\WpTheme\Feature;

use Mobilo\WpTheme\Utils\AjaxActionLoader;

defined('ABSPATH') || exit;

use Mobilo\WpTheme\Interfaces\InitializableInterface;

/**
 * Abstract class BaseFeature
 *
 * @package Mobilo\WpTheme\Feature
 * @version 1.0.0
 * @since 0.4.0
 */
abstract class BaseFeature implements InitializableInterface
{
    protected $feature;
    protected $ajaxLoader;

    public function __construct(string $feature = '')
    {
        $this->feature = $feature;
        $this->ajaxLoader = new AjaxActionLoader();
    }

    /**
     * Runs the function by calling the actionInit method.
     *
     * @return void
     */
    public function run()
    {
        $this->actionInit();
    }

    /**
     * Initializes the action.
     *
     * This function is called when the action is initialized. It is a placeholder
     * function and does not contain any code.
     *
     * @return void
     */
    public function actionInit()
    {
    }

    /**
     * Adds a filter to the specified hook.
     *
     * @param string $hook The name of the filter hook.
     * @param string $callback The callback function.
     * @param int $priority Optional. The priority at which the function should be executed. Default is 10.
     * @param int $acceptedArgs Optional. The number of arguments the function accepts. Default is 1.
     * @return void
     */
    public function addFilter($hook, $callback, $priority = 10, $acceptedArgs = 1)
    {
        if (!method_exists($this, $callback)) {
            mobilo_log(__METHOD__, "Method not found: {$callback}");
            return;
        }
        add_filter($hook, [$this, $callback], $priority, $acceptedArgs);
    }

    /**
     * Adds an action to the specified hook.
     *
     * @param string $hook The name of the action hook.
     * @param string $callback The callback function.
     * @param int $priority Optional. The priority at which the function should be executed. Default is 10.
     * @param int $acceptedArgs Optional. The number of arguments the function accepts. Default is 1.
     * @return void
     */
    public function addAction($hook, $callback, $priority = 10, $acceptedArgs = 1)
    {
        if (!method_exists($this, $callback)) {
            mobilo_log(__METHOD__, "Callback method not found, hook: {$hook} , callback: {$callback}");
            return;
        }
        add_action($hook, [$this, $callback], $priority, $acceptedArgs);
    }

    /**
     * Loads an AJAX action by calling the `loadAjaxAction` method of the `AjaxActionLoader` class.
     *
     * @param mixed $ajaxClass The class name of the AJAX action to load.
     * @return void
     */
    public function loadAjaxAction($ajaxClass)
    {
        $this->ajaxLoader->loadAjaxAction($ajaxClass);
    }
}