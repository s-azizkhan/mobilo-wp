<?php

namespace Mobilo\WpTheme\Feature;

defined('ABSPATH') || exit;

/**
 * Class CardColorFeature
 *
 * @link logicwind.com
 * @since 0.2.0
 * @version 1.0.0
 * @package Mobilo\WpTheme\Feature
 */
class CardColorFeature
{

    /**
     * The card color constant.
     * @since 1.0.0
     * @version 1.0.0
     * @access public
     */
    const CARD_COLOR_KEY = 'card_colors';

    /**
     * The card color meta key.
     * @since 1.0.0
     * @version 1.0.0
     * @access public
     * @var string $card_color_meta_key
     */
    public $card_color_meta_key;

    /**
     * Get the full card color with prefix.
     * @since 1.0.0
     * @version 1.0.0
     * @return string
     */
    public static function getCardColorKey()
    {
        return MOBILO_PREFIX . '_' . self::CARD_COLOR_KEY;
    }

    public function __construct()
    {
        // Set the card color meta key
        $this->card_color_meta_key = self::getCardColorKey();
    }

    public function run()
    {
        $this->actions_init();
    }

    /**
     * Register the actions
     *
     * @return void
     */
    public function actions_init()
    {
        // Hook into the 'woocommerce_product_options_general_product_data' action
        add_action('woocommerce_product_options_general_product_data', [$this, 'add_custom_card_color_field']);
        // Hook into the 'woocommerce_process_product_meta' action
        add_action('woocommerce_process_product_meta', [$this, 'save_custom_card_color_field']);
        // Hook into the 'woocommerce_checkout_create_order_line_item' action
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'map_card_color_to_order_item'], 10, 4);
    }

    /**
     * Add card colors field to the "General" tab of product
     *
     * @since 1.0.0
     * @version 1.0.0
     */
    function add_custom_card_color_field()
    {
        global $post;
        echo '<div class="options_group">';
        // Get existing custom field value
        $custom_card_color_value = get_post_meta($post->ID, $this->card_color_meta_key, true);
        woocommerce_wp_text_input(array(
            'id'          => $this->card_color_meta_key,
            'label'       => __('Card Colors', 'woocommerce'),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => __('Add card color in comma separated format.', 'woocommerce'),
            'type'        => 'text',
            'value'       => $custom_card_color_value,
        ));
        echo '</div>';
    }

    /**
     * Saves custom card color field data.
     *
     * @since 1.0.0
     * @version 1.0.0
     * @param int $post_id The post ID.
     */
    function save_custom_card_color_field($post_id)
    {
        if (isset($_POST[$this->card_color_meta_key])) {
            $custom_card_color_value = sanitize_text_field($_POST[$this->card_color_meta_key]);
            update_post_meta($post_id, $this->card_color_meta_key, $custom_card_color_value);
        }
    }

    /**
     * Function to map card color to order item.
     *
     * @since 1.0.0
     * @version 1.0.0
     * @param object $item The order item object.
     * @param string $cart_item_key The key of the cart item.
     * @param array $values Array of values associated with the cart item.
     * @param object $order The order object.
     */
    function map_card_color_to_order_item($item, $cart_item_key, $values, $order)
    {
        // Get the card color from the cart item
        $card_color = $values[$this->card_color_meta_key] ?? '';

        // Check if card color exists and add it to the order item
        if (!empty($card_color)) {
            $item->add_meta_data('Card Color', $card_color);
        }
    }
}
