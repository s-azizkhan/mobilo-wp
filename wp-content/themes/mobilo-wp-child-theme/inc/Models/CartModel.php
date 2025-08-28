<?php

namespace Mobilo\WpTheme\Models;

use WC_Cart;

/**
 * Class CartModel
 *
 * @since 0.1.12
 * @version 1.1.0
 */
class CartModel extends WC_Cart
{
    /**
     * @var array List of cart items.
     */
    protected $data = [];
    protected $data_v2 = [];

    /**
     * @var float The total price of one-time items.
     */
    protected $onetime_price = 0;

    /**
     * @var float The total price of yearly subscription items.
     */
    protected $yearly_price = 0;

    public $total_license = 0;
    public $total_card = 0;

    /**
     * CartModel constructor.
     *
     * @since 1.0.0
     * @access public
     */
    public function __construct()
    {
        global $woocommerce;
        $cart_items = $woocommerce->cart->get_cart_contents();

        foreach ($cart_items as $key => $cart_item) {
            // Some of our callbacks rely on cart_item_key being a string
            // Since PHP coerces scalar types to strings for typed function arguments,
            // we just have to handle the situation where the key is null, which is
            // for some reason not coerced due to ancient secret PHP knowledge
            $key = $key ?? '';

            /** @var \WC_Product $product */
            $product = apply_filters('woocommerce_cart_itemproduct', $cart_item['data'], $cart_item, $key);

            $exists = $product && $product->exists();
            $non_zero = $cart_item['quantity'] > 0;
            $visible = apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $key);
            $include = $exists && $non_zero && $visible;

            if (!$include) {
                continue;
            }

            $this->set_item(new CartItemModel($key, $cart_item));

            $product_id = $cart_item['product_id'];

            $product = wc_get_product($product_id);

            // Note: below function will work only on yearly subscription product
            $this->set_price($product, $cart_item);
        }
    }

    /**
     * Adds a cart item.
     *
     * @version 1.0.0
     * @param CartItemModel $item The cart item to add.
     */
    public function set_item(CartItemModel $item)
    {
        $this->data[] = $item->get_data();
        $this->data_v2[] = $item->get_data_v2();
    }

    /**
     * Sets the price of a cart item.
     *
     * @version 1.0.0
     * @param \WC_product $product The product to set the price for.
     * @param array $cart_item The cart item data.
     */
    public function set_price(\WC_product $product, array $cart_item)
    {
        if (!$product) {
            return;
        }

        $product_type = $product->get_type();

        // Note: below function will work only on yearly subscription product
        switch ($product_type) {
            case 'subscription':
                $this->yearly_price += $cart_item['line_subtotal'];
                break;
            case 'variable-subscription':
                $this->yearly_price += $cart_item['line_subtotal'];
            default:
                break;
        }

        if (in_array($product_type, array('simple', 'variable'))) {
            $this->onetime_price += $cart_item['line_total'];
        }
    }

    /**
     * Gets the cart items.
     *
     * @version 1.0.0
     * @return array
     */
    public function get_data()
    {
        $cart_items = $this->data;

        $subscription_types = ['variable-subscription', 'subscription'];
        $accessories_sku = [];
        $products = [];
        // filter products & subscriptions
        foreach ($cart_items as $key => $cart_item) {
            if (in_array($cart_item['type'], $subscription_types)) {
                $products['subscriptions'][] = $cart_item;
            } else if (in_array($cart_item['sku'], $accessories_sku)) {
                $products['accessories'][] = $cart_item;
            } else {
                $products['products'][] = $cart_item;
            }
        }

        return $products;
    }

    /**
     * Gets the cart notes.
     *
     * @version 1.0.0
     * @return array
     */
    public function get_notes()
    {
        return [
            'Custom designs will be created after payment',
            'Shipping will be calculated at checkout',
        ];
    }

    public function get_notes_v2()
    {
        return [
            'shipping' => 'Shipping will be calculated at checkout',
            'design' => 'Custom designs will be created after payment',
        ];
    }

    /**
     * Gets the cart prices.
     *
     * @since 1.0.2
     * @version 1.0.3
     * @return array
     */
    public function get_prices()
    {
        $sub_total = (float) WC()->cart->subtotal;
        $yearly_price = (float) $this->yearly_price;
        $onetime_price = (float) $this->onetime_price;

        // Default sub_total display
        $sub_total_display = format_price($sub_total);
        $currency = get_woocommerce_currency();
        $data = WC()->session->get('checkout_data');
        $billing_country = $data['billing_country'] ?? null;
        $billing_state = $data['billing_state'] ?? null;
        if ($currency == 'EUR' && $billing_country !== 'GB') {
            $yearly_price = get_include_vat_price($yearly_price);
            $onetime_price = get_include_vat_price($onetime_price);
            $sub_total_display = format_price($sub_total) . ' (incl.tax)';
        } elseif ($currency == 'USD' && $billing_country === 'US' && $billing_state === 'NY') {
            // Get the tax rates for the given billing country code and billing state code.
            $tax_rates = \WC_Tax::find_rates([
                'country' => $billing_country,
                'state' => $billing_state,
            ]);
            if (!empty($tax_rates)) {
                // Get the NY state tax rate.
                $ny_tax_rate = reset($tax_rates);

                // Get the NY tax rate in percentage.
                $ny_tax_rate_percentage = $ny_tax_rate['rate'];

                $vat_rate = $ny_tax_rate_percentage / 100;
            }
            $onetime_price = get_include_vat_price($onetime_price, $vat_rate);
            $sub_total_display .= ' (incl.tax)';
        }

        $r = [
            'sub_total' => $sub_total_display,
            'yearly' => format_price($yearly_price),
            'onetime' => format_price($onetime_price),
        ];
        return $r;
    }

    /**
     * Gets the cart data.
     *
     * @version 1.0.0
     * @return array
     */
    public function get_cart()
    {
        $res = [
            'price' => $this->get_prices(),
            'cart_notes' => $this->get_notes(),
            'items' => $this->get_data(),
            'items_count' => WC()->cart->get_cart_contents_count(),
        ];
        return $res;
    }

    /**
     * Get new plan cart
     */
    public function get_new_plan_cart($return_data = true, $include_currency = false)
    {
        $cart_items = $this->data_v2;
        $currency_symbol = get_woocommerce_currency_symbol();

        $subscription_types = ['variable-subscription', 'subscription'];
        $accessories_sku = ['NFC-SB', 'NFC-KF'];

        $cart_cards = [];
        $cart_cards['title'] = __('Products', 'mobilo');
        $cart_cards['sub_title'] = __('Cards & Accessories', 'mobilo');
        $cart_cards['products'] = [];
        $cart_cards['accessories'] = [];

        $cart_license = [];

        $total_card = $digital_card = $total_license = $subtotal_card_price = $subtotal_license_price = $subtotal_accessories_price = 0;

        // filter products & subscriptions
        foreach ($cart_items as $key => $cart_item) {

            if ($cart_item['sku'] == 'MC_DIGITAL') {
                $digital_card += $cart_item['quantity'];
            }

            $itemSubtotal = 0;

            if (!empty($cart_item['raw_item']['line_subtotal']) && !empty($cart_item['raw_item']['line_subtotal_tax'])) {
                $rawPrice = $cart_item['raw_item']['line_subtotal'] + $cart_item['raw_item']['line_subtotal_tax'];
                $itemSubtotal = $rawPrice ? ($include_currency ? $currency_symbol . ' ' : '') . format_price($rawPrice) : 0;
            }
            if (in_array($cart_item['type'], $subscription_types)) {

                // note: one subscription allowed in a cart
                $total_license += $cart_item['quantity'];
                $subtotal_license_price += $itemSubtotal ?: $cart_item['subtotal'];

                $cart_license[] = $cart_item;
            } else if (in_array($cart_item['sku'], $accessories_sku)) {

                $cart_cards['accessories'][] = $cart_item;
                $subtotal_accessories_price += $itemSubtotal ?: $cart_item['subtotal'];
            } else {

                $total_card += $cart_item['quantity'];
                $subtotal_card_price += $itemSubtotal ?: $cart_item['subtotal'];

                $product = [
                    'id' => $cart_item['id'],
                    'sku' => $cart_item['sku'],
                    'name' => $cart_item['name'],
                    "item_key" => $cart_item['item_key'],
                    'quantity' => $cart_item['quantity'],
                    "base_price" => $cart_item['base_price'],
                    "price" => $cart_item['price'],
                    "subtotal" => $itemSubtotal ?: $cart_item['subtotal'],
                ];

                if (isset($cart_item['variation'])) {
                    $product['variation'] = $cart_item['variation'];
                    $product['variation_id'] = $cart_item['variation_id'];
                }

                if (isset($cart_item['card_color'])) {
                    $product['card_color'] = $cart_item['card_color'];
                }

                $cart_cards['products'][] = $product;
            }
        }

        $this->total_license = $total_license;
        $this->total_card = $total_card;
        $this->digital_card = $digital_card;

        if (!$return_data) {
            return;
        }

        $res = [
            'items' => $cart_cards,
            'card' => [
                'title' => __('Cards', 'mobilo'),
                'count' => $total_card,
                'sub_total' => $include_currency ? $currency_symbol . ' ' . format_price($subtotal_card_price) : format_price($subtotal_card_price),
            ],
            'cart_notes' => $this->get_notes_v2(),
            'total' => $include_currency ? $currency_symbol . ' ' . format_price($subtotal_accessories_price + $subtotal_card_price + $subtotal_license_price) : format_price($subtotal_accessories_price + $subtotal_card_price + $subtotal_license_price),
            'one_time' => $include_currency ? $currency_symbol . ' ' . format_price($this->onetime_price) : format_price($this->onetime_price),
            'per_year' => $include_currency ? $currency_symbol . ' ' . format_price($subtotal_license_price) : format_price($subtotal_license_price),
        ];
        return $res;
    }
}
