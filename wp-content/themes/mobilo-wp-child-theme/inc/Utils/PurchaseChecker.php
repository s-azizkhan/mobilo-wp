<?php

namespace Mobilo\WpTheme\Utils;

defined('ABSPATH') || exit;


/**
 * Class PurchaseChecker
 * @since 0.0.1
 * @version 1.0.0
 * @package Mobilo\WpTheme\Utils
 * @author S.Aziz Khan <hi@justaziz.com>
 */
class PurchaseChecker
{
    /**
     * Check if a user has bought specific items based on user ID or billing email.
     *
     * @param mixed $user_var User ID or billing email to check the purchase for.
     * @param mixed $product_ids Product IDs to check if bought.
     * @return bool Returns true if the user has bought the items, false otherwise.
     */
    public static function hasBoughtItems($user_var = 0, $product_ids = 0): bool
    {
        global $wpdb; // Use global $wpdb within the static context

        list($meta_key, $meta_value) = self::getUserMeta($user_var);
        $paid_statuses = self::getPaidStatuses();
        $line_meta_value = self::getLineMetaValue($product_ids);

        $count = $wpdb->get_var("
            SELECT COUNT(p.ID) FROM {$wpdb->prefix}posts AS p
            INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_items AS woi ON p.ID = woi.order_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woim ON woi.order_item_id = woim.order_item_id
            WHERE p.post_status IN ('wc-" . implode("','wc-", $paid_statuses) . "' )
            AND pm.meta_key = '$meta_key'
            AND pm.meta_value = '$meta_value'
            AND woim.meta_key IN ('_product_id', '_variation_id') $line_meta_value
        ");

        return $count > 0;
    }

    // Static function to get meta key and value based on user identifier type
    private static function getUserMeta($user_var): array
    {
        if (is_numeric($user_var)) {
            $meta_key = '_customer_user';
            $meta_value = (int) $user_var === 0 ? (int) get_current_user_id() : (int) $user_var;
        } else {
            $meta_key = '_billing_email';
            $meta_value = sanitize_email($user_var);
        }

        return [$meta_key, $meta_value];
    }

    // Static function to get the paid order statuses
    private static function getPaidStatuses(): array
    {
        return array_map('esc_sql', wc_get_is_paid_statuses());
    }

    // Static function to construct the line meta value condition
    private static function getLineMetaValue($product_ids): string
    {
        if (is_array($product_ids)) {
            $product_ids = implode(',', $product_ids);
        }
        return $product_ids != (0 || '') ? 'AND woim.meta_value IN (' . $product_ids . ')' : 'AND woim.meta_value != 0';
    }
}
