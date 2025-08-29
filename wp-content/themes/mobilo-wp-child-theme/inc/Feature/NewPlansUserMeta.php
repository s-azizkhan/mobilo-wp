<?php

namespace Mobilo\WpTheme\Feature;

/**
 * Class NewPlansUserMeta
 * 
 * @version 1.0.0
 * @package Mobilo\WpTheme\Feature
 */
class NewPlansUserMeta
{

    public static $orgMetaKey = MOBILO_PREFIX . '_user_org';
    public static $planMetaKey = MOBILO_PREFIX . '_chosen_plan_sku';

    public function __construct()
    {
        add_action('init', [$this, 'registerUserMeta']);
        add_action('show_user_profile', [$this, 'addCustomUserMetaField']);
        add_action('edit_user_profile', [$this, 'addCustomUserMetaField']);
        add_action('personal_options_update', [$this, 'saveCustomUserMetaField']);
        add_action('edit_user_profile_update', [$this, 'saveCustomUserMetaField']);

    }

    /**
     * Registers user metadata for Firebase.
     *
     * This function registers two user metadata fields: one for the user's current plan SKU
     * and another for the user's organization ID. The metadata is registered with the 'user'
     * meta type and has the following properties:
     * - 'type': string
     * - 'description': user's current plan SKU or user's organization ID
     * - 'show_in_rest': true
     * - 'single': true
     *
     * @return void
     */
    public function registerUserMeta()
    {
        register_meta('user', self::$planMetaKey, array(
            "type" => "string",
            "description" => "user's current plan SKU",
            "show_in_rest" => true,
            "single" => true,
        ));

        register_meta('user', self::$orgMetaKey, array(
            "type" => "string",
            "description" => "user's Organization ID",
            "show_in_rest" => true,
            "single" => true,
        ));
    }

    /**
     * Adds a custom user meta field to the user profile page.
     *
     * @param WP_User $user The user object.
     * @return void
     */
    public function addCustomUserMetaField($user)
    {
        ?>
        <table class="form-table">
            <tr>
                <th><label for="<?php echo esc_attr(self::$orgMetaKey); ?>"><?php _e('Org Id', 'mobilo'); ?></label></th>
                <td>
                    <input disabled type="text" name="<?php echo esc_attr(self::$orgMetaKey); ?>"
                        id="<?php echo esc_attr(self::$orgMetaKey); ?>"
                        value="<?php echo esc_attr(get_user_meta($user->ID, self::$orgMetaKey, true)); ?>" class="regular-text"
                        readonly />
                    <span class="description"><?php _e('This is a org id of the user.', 'mobilo'); ?></span>
                </td>
            </tr>
            <tr>
                <th><label for="<?php echo esc_attr(self::$planMetaKey); ?>"><?php _e('Current Plan SKU', 'mobilo'); ?></label>
                </th>
                <td>
                    <input type="text" name="<?php echo esc_attr(self::$planMetaKey); ?>"" id=" <?php echo esc_attr(self::$planMetaKey); ?>"
                        value="<?php echo esc_attr(get_user_meta($user->ID, self::$planMetaKey, true)); ?>"
                        class="regular-text" />
                    <span class="description"><?php _e('This is the current plan SKU of the user.', 'mobilo'); ?></span>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Saves the custom user meta field.
     *
     * @param int $user_id The ID of the user.
     * @return void
     */
    public function saveCustomUserMetaField(int $user_id): void
    {
        if (!current_user_can('edit_user', $user_id) || !isset($_POST[self::$planMetaKey])) {
            return;
        }
        self::set_plan_sku($_POST[self::$planMetaKey], $user_id);
    }
    /**
     * Retrieves the SKU of the plan associated with a user.
     *
     * @param int|null $user_id The ID of the user. Defaults to null.
     * @return string The SKU of the plan associated with the user, or an empty string if no user ID is found.
     */
    public static function getPlanSku($user_id = null)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return '';
        }

        $planSku = get_user_meta($user_id, self::$planMetaKey, true);
        return strtoupper(trim($planSku));
    }

    /**
     * Sets the SKU of the plan associated with a user.
     *
     * @param string $plan_sku The SKU of the plan to associate with the user.
     * @param int|null $user_id The ID of the user. Defaults to null.
     */
    public static function set_plan_sku($plan_sku, $user_id = null)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return false;
        }

        $current_plan_sku = self::getPlanSku($user_id);
        $plan_sku = strtoupper(trim(sanitize_text_field($plan_sku)));
        // if the plan sku is same as current plan sku, then return
        if ($current_plan_sku == $plan_sku) {
            return false;
        }

        // if the current plan sku is not null and plan sku is null, then return
        if ($current_plan_sku && !$plan_sku) {
            mobilo_log(__METHOD__, "user have plan `{$current_plan_sku}` but want to downgrade to plan `{$plan_sku}` for user {$user_id}", 'info');
            return false;
        }

        update_user_meta($user_id, self::$planMetaKey, $plan_sku);

        mobilo_log(__METHOD__, "User's plan sku updated to {$plan_sku} for user {$user_id}", 'info');
        return true;
    }

    /**
     * Retrieves the organization ID associated with a user.
     *
     * @param int|null $user_id The ID of the user. Defaults to null.
     * @return string The organization ID associated with the user, or an empty string if no user ID is found.
     */
    public static function getOrgId($user_id = null)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return '';
        }

        $orgId = get_user_meta($user_id, self::$orgMetaKey, true);
        return trim($orgId);
    }

    /**
     * Sets the organization ID associated with a user.
     *
     * @param string $orgId The organization ID to associate with the user.
     * @param int|null $user_id The ID of the user. Defaults to null.
     */
    public static function set_org_id($orgId, $user_id = null)
    {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return false;
        }

        update_user_meta($user_id, self::$orgMetaKey, trim(sanitize_text_field($orgId)));
        mobilo_log(__METHOD__, "User's orgId updated to '{$orgId}' for user {$user_id}", 'info');
        return true;
    }
}