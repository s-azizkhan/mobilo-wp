<?php

namespace Mobilo\WpTheme\Models;

use Mobilo\WpTheme\Feature\CardColorFeature;
use Mobilo\WpTheme\Interfaces\CartItemInterface;
use WC_Product;

/**
 * Class CartItemModel
 *
 * @since 0.1.12
 * @version 1.1.0
 */
class CartItemModel implements CartItemInterface
{
    protected $thumbnail;
    protected $quantity;
    protected $title;
    protected $url;
    protected $subtotal_html;
    protected $subtotal;
    protected $item_key;
    protected $raw_item;
    protected $product;
    protected $data;
    protected $formatted_data;
    protected $card_color;
    protected $card_color_meta_key;

    public function __construct(string $key, array $item)
    {
        $this->item_key = $key;
        $this->raw_item = $item;
        $this->card_color_meta_key = CardColorFeature::getCardColorKey();
        $this->card_color = isset($item[$this->card_color_meta_key]) ? $item[$this->card_color_meta_key] : '';

        /** @var WC_Product $_product */
        $product = apply_filters('woocommerce_cart_item_product', $item['data'], $item, $key);
        $this->product = $product;

        $woocommerce_filtered_cart_item_row_class = esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $item, $key));
        $this->thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $product->get_image('cfw_cart_thumb'), $item, $key);
        $this->quantity = floatval($item['quantity']);
        $this->title = apply_filters('woocommerce_cart_item_name', $product->get_name(), $item, $key);
        $this->url = get_permalink($item['product_id']);
        $this->subtotal_html = apply_filters('woocommerce_cart_item_subtotal_html', WC()->cart->get_product_subtotal($product, $item['quantity']), $item, $key);
        $this->subtotal = apply_filters('woocommerce_cart_item_subtotal', $item['line_subtotal'], $item, $key);
        $this->data = $this->get_cart_item_data($item);
        $this->formatted_data = $this->get_formatted_cart_data();
    }

    protected function get_cart_item_data(array $cart_item): array
    {
        $item_data = array();

        // Variation values are shown only if they are not found in the title as of 3.0.
        // This is because variation titles display the attributes.
        if ($cart_item['data']->is_type('variation') && is_array($cart_item['variation'])) {
            foreach ($cart_item['variation'] as $name => $value) {
                $taxonomy = wc_attribute_taxonomy_name(str_replace('attribute_pa_', '', urldecode($name)));

                if (taxonomy_exists($taxonomy)) {
                    // If this is a term slug, get the term's nice name.
                    $term = get_term_by('slug', $value, $taxonomy);
                    if (!is_wp_error($term) && $term && $term->name) {
                        $value = $term->name;
                    }
                    $label = wc_attribute_label($taxonomy);
                } else {
                    // If this is a custom option slug, get the options name.
                    $value = apply_filters('woocommerce_variation_option_name', $value, null, $taxonomy, $cart_item['data']);
                    $label = wc_attribute_label(str_replace('attribute_', '', $name), $cart_item['data']);
                }

                // Check the nicename against the title.
                if ('' === $value || wc_is_attribute_in_product_name($value, $cart_item['data']->get_name())) {
                    continue;
                }

                $item_data[] = array(
                    'key' => $label,
                    'value' => $value,
                );
            }
        }

        // Filter item data to allow 3rd parties to add more to the array.
        $item_data = apply_filters('woocommerce_get_item_data', $item_data, $cart_item);

        $prepared_data = array();

        // Format item data ready to display.
        foreach ($item_data as $key => $data) {
            // Set hidden to true to not display meta on cart.
            if (!empty($data['hidden'])) {
                unset($item_data[$key]);
                continue;
            }

            $key = !empty($data['key']) ? $data['key'] : $data['name'];
            $display = !empty($data['display']) ? $data['display'] : $data['value'];
            $prepared_data[$key] = $display;
        }

        return $prepared_data;
    }

    protected function get_formatted_cart_data()
    {
        $output = wc_get_formatted_cart_item_data($this->get_raw_item());

        return str_replace(' :', ':', $output);
    }

    public function get_thumbnail(): string
    {
        return wp_kses(
            $this->thumbnail,
            array(
                'img' => array_merge(
                    wp_kses_allowed_html('post')['img'] ?? array(),
                    array(
                        'srcset' => true,
                        'decoding' => true,
                    )
                ),
            )
        );
    }

    public function get_quantity(): float
    {
        return floatval($this->quantity);
    }

    public function get_title(): string
    {
        return strval($this->title);
    }

    public function get_url(): string
    {
        return strval($this->url);
    }

    public function get_subtotal(): float
    {
        $currency = get_woocommerce_currency();
        $data = WC()->session->get('checkout_data');
        $billing_country = $data['billing_country'] ?? null;
        $billing_state = $data['billing_state'] ?? null;
        $per_pro_price = $this->subtotal / $this->quantity;
        if ($currency == 'EUR' && $billing_country !== 'GB') {
            $per_pro_price = mc_get_include_vat_price($per_pro_price);
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
            $per_pro_price = mc_get_include_vat_price($per_pro_price, $vat_rate);
        }
        $this->subtotal = $per_pro_price * $this->quantity;
        return floatval($this->subtotal);
    }

    public function get_subtotal_html(): string
    {
        return strval($this->subtotal);
    }

    public function get_item_key(): string
    {
        return strval($this->item_key);
    }

    public function get_raw_item()
    {
        // TODO: Eliminate the necessity of this workaround in a future major version
        return $this->raw_item;
    }

    public function get_product(): WC_Product
    {
        return wc_get_product($this->raw_item['product_id']);
    }

    public function get_data(): array
    {
        $data = $this->data ?? array();
        $raw = $this->get_raw_item();
        $product = $this->get_product();

        if (empty($data)) {
            $data = array(
                'id' => $product->get_id(),
                'name' => $this->get_title(),
                'quantity' => $this->get_quantity(),
                'sku' => $product->get_sku(),
                'type' => $product->get_type(),
                'subtotal' => mc_format_price($this->get_subtotal()),
                'item_key' => $this->get_item_key(),
                'thumbnail' => $this->get_thumbnail(),
                'card_color' => $this->card_color,
            );

            if ($raw['variation_id'] && $product->is_type('variable')) {
                $data['variation_id'] = $raw['variation_id'];
                $data['variation'] = array_values($raw['variation'])[0];
                $data['available_variations'] = $this->parse_variable_product_variation($product);
                $data['base_price'] = $data['available_variations'][$data['variation']]['base_price'];
                $data['price'] = $data['available_variations'][$data['variation']]['price'];
            } else {
                $base_price = $product->get_regular_price();
                $sale_price = $product->get_sale_price();
                $data['base_price'] = $base_price;
                $data['price'] = $sale_price <= 0 ? $base_price : $sale_price;
            }
        }

        return $data;
    }
    public static function extract_card_colors($card_color_value)
    {
        // Initialize an empty array to hold the card color information.
        $card_color_array = [];

        // Check if the input string is not empty.
        if (!empty($card_color_value)) {
            // Split the string by '|' to separate each category and its colors.
            $categories = explode('|', $card_color_value);

            // Iterate over each category and its colors.
            foreach ($categories as $category) {
                // Split each category string by ':' to separate the category name from the colors.
                $category_data = array_map('trim', explode(':', $category));

                // Ensure that the split resulted in exactly two parts: the category name and the colors.
                if (count($category_data) === 2) {
                    // First part is the category name.
                    $category_name = $category_data[0];

                    // Second part is the list of colors, split by ',' and trimmed of any extra spaces.
                    $colors = array_map('trim', explode(',', $category_data[1]));

                    // Add the category and its corresponding colors to the result array.
                    $card_color_array[$category_name] = $colors;
                }
            }
        }

        // Return the associative array of categories and their corresponding colors.
        return $card_color_array;
    }
    public static function get_card_color_images($colorNames)
    {
        $color_images = [];
        $url = MOBILO_THEME_URL . '/public/assets/images';
        foreach ($colorNames as $colorName) {
            $color_images[$colorName] = $url . '/card_' . $colorName . '.webp';
        }
        return $color_images;
    }
    public static function parse_variable_product_variation(WC_Product $product)
    {
        $card_color_meta_key = CardColorFeature::getCardColorKey();
        // Get the custom card color value from post meta
        $card_color_value = get_post_meta($product->get_id(), $card_color_meta_key, true);
        // Extract card colors from the card color value
        $card_color_array = self::extract_card_colors($card_color_value);
        // Get the available variations.
        $temp_variations = $product->get_available_variations();
        // If there are no variations, return an empty array.
        if (!$temp_variations) {
            return "";
        }
        // Create an array to store the variations.
        $variations = [];

        //$currency = get_woocommerce_currency();

        // Loop through the variations.
        foreach ($temp_variations as $var) {
            // Skip variations that are not active or visible.
            if (!$var['variation_is_active'] && !$var['variation_is_visible']) {
                continue;
            }

            // Get the base price.
            $base_price = (string) $var['display_regular_price'];

            // Get the sale price.
            $sale_price = (string) $var['display_price'];

            // Create a variation object.
            $variation = [
                'id' => $var['variation_id'],
                'name' => $product->get_title(),
                'base_price' => mc_format_price($base_price),
                'price' => mc_format_price($sale_price) <= 0 ? mc_format_price($base_price) : mc_format_price($sale_price),
                'sku' => $var['sku'],
                'attribute' => $var['attributes'],
                'image' => $var['image'],
                'description' => self::parse_product_description($var['variation_description'], true),
            ];

            // Get the first attribute value.
            $attribute_value = array_values($var['attributes'])[0];
            if (isset($card_color_array[$attribute_value])) {
                $colorKeywords = $card_color_array[$attribute_value];
                $card_colors = self::get_card_color_images($colorKeywords);
                $variation['card_colors'] = $card_colors;
            }

            // Add the variation to the array.
            $variations[$attribute_value] = $variation;
        }

        return $variations;
    }
    public static function parse_product_description($description, $parse = false)
    {
        // Remove HTML tags from the description string.
        $description = strip_tags($description);

        // If the `parse` flag is set, split the description string into an array.
        if ($parse) {
            $separator = '|';
            $description_array = explode($separator, $description);

            // Trim whitespace from each line in the array.
            $description = array_map('trim', $description_array);
        }

        // Return the parsed description string or array.
        return $description;
    }
    public function get_formatted_data(): string
    {
        return $this->formatted_data;
    }

    public function get_data_v2(): array
    {
        $data = array();
        $raw = $this->get_raw_item();
        $product = $this->get_product();

        if (empty($data)) {
            $data = array(
                'id' => $product->get_id(),
                'name' => $this->get_title(),
                'quantity' => $this->get_quantity(),
                'sku' => $product->get_sku(),
                'type' => $product->get_type(),
                'regular_price' => mc_format_price($product->get_price()),
                'sale_price' => mc_format_price($product->get_sale_price()),
                'item_key' => $this->get_item_key(),
                'subtotal' => mc_format_price($this->get_subtotal()),

            );
            if ($this->card_color) {
                $data['card_color'] = $this->card_color;
            }

            if ($raw['variation_id'] && $product->is_type('variable')) {
                $data['variation_id'] = $raw['variation_id'];
                $data['variation'] = array_values($raw['variation'])[0];
                $data['available_variations'] = $this->parse_variable_product_variation($product);
                $data['base_price'] = $data['available_variations'][$data['variation']]['base_price'];
                $data['price'] = $data['available_variations'][$data['variation']]['price'];
            } else {
                $base_price = $product->get_regular_price();
                $sale_price = $product->get_sale_price();
                $data['base_price'] = mc_format_price($base_price);
                $data['price'] = mc_format_price($sale_price <= 0 ? $base_price : $sale_price);
            }
        }

        $data['raw_item'] = $raw;

        return $data;
    }
}
