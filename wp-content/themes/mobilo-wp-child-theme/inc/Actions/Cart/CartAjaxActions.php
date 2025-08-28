<?php

namespace Mobilo\WpTheme\Actions\Cart;

use Mobilo\WpTheme\Actions\Cart\AddToCartAction;
use Mobilo\WpTheme\Actions\Cart\UpdateCartQuantityAction;
use Mobilo\WpTheme\Actions\Cart\RemoveCartItemAction;
use Mobilo\WpTheme\Actions\Cart\GetCartDataAction;
use Mobilo\WpTheme\Actions\Cart\AddUpsellAllAction;
use Mobilo\WpTheme\Actions\MobiloAjaxAction;

defined('ABSPATH') || exit;

/**
 * Cart AJAX Actions Loader
 * Loads and manages individual cart AJAX actions
 */
class CartAjaxActions
{
    private $actions = [];

    public function __construct()
    {
        // Constructor is called when class is instantiated
    }

    public function init()
    {
        $this->load_default_actions();
    }

    /**
     * Load default cart AJAX actions
     */
    private function load_default_actions()
    {

        $this->add_action(new AddToCartAction());
        $this->add_action(new UpdateCartQuantityAction());
        $this->add_action(new RemoveCartItemAction());
        $this->add_action(new GetCartDataAction());
        $this->add_action(new AddUpsellAllAction());
    }

    /**
     * Add a specific AJAX action
     *
     * @param MobiloAjaxAction $action
     */
    public function add_action($action)
    {
        $this->actions[] = $action;
        $action->load();
    }

    /**
     * Load only specific actions
     *
     * @param array $action_names Array of action names to load
     */
    public function load_specific_actions($action_names)
    {
        $action_map = [
            'add_to_cart' => AddToCartAction::class,
            'update_quantity' => UpdateCartQuantityAction::class,
            'remove_item' => RemoveCartItemAction::class,
            'get_cart_data' => GetCartDataAction::class,
            'add_upsell_all' => AddUpsellAllAction::class,
        ];

        foreach ($action_names as $action_name) {
            if (isset($action_map[$action_name])) {
                $action_class = $action_map[$action_name];
                $this->add_action(new $action_class());
            }
        }
    }

    /**
     * Get loaded actions
     *
     * @return array
     */
    public function get_loaded_actions()
    {
        return $this->actions;
    }
}
