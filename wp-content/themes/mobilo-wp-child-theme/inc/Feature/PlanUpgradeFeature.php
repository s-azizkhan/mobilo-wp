<?php
namespace Mobilo\WpTheme\Feature;

use Mobilo\WpTheme\Feature\BaseFeature;

defined('ABSPATH') || exit;

use Throwable;

/**
 * Class PlanUpgradeFeature
 * 
 * @version 1.0.0
 * @package Mobilo\WpTheme\Feature
 */
class PlanUpgradeFeature extends BaseFeature
{
    private static $featureKey = 'plan_upgrade_feature';
    public static $cookieKey = MOBILO_PREFIX . '_plan_upgrade_mode';

    public function __construct()
    {
        parent::__construct(self::$featureKey);
    }

    public function actionInit()
    {
        add_action('woocommerce_thankyou', [$this, 'disable_plan_upgrade_mode']);
        add_action('template_redirect', [$this, 'checkPlanUpgradeModeRedirection']);

        // try {
        //     $this->loadAjaxAction(PlanSwitchOrPurchaseAction::class);
        // } catch (Throwable $th) {
        //     mobilo_log(__METHOD__, $th->getMessage());
        // }
    }

    /**
     * Checks if the current page is the cart page and the plan upgrade mode is enabled.
     * If so, redirects to the checkout page.
     */
    public function checkPlanUpgradeModeRedirection()
    {
        // If the current page is not the cart page, return early.
        if (!is_cart()) {
            return;
        }

        // If the plan upgrade mode is enabled, perform the redirection.
        if (self::is_plan_upgrade_mode_enabled()) {
            // If the cart is empty, disable the plan upgrade mode and return.
            if (WC()->cart->is_empty()) {
                self::disable_plan_upgrade_mode();
                return;
            }

            // Redirect to the checkout page.
            wp_redirect(wc_get_checkout_url());
            exit;
        }
    }

    /**
     * Checks if the plan upgrade mode is enabled.
     *
     */
    public static function is_plan_upgrade_mode_enabled(): ?string
    {
        global $lwmc_plan_upgrade_mode;

        if (isset($lwmc_plan_upgrade_mode)) {
            return $lwmc_plan_upgrade_mode;
        }

        return $_COOKIE[self::$cookieKey] ?? false;
    }

    /**
     * Enables the plan upgrade mode by setting a cookie and a global variable.
     * Cookie expiry time is set to 10 minutes as default.
     * @param string $planSku
     *
     */
    public static function enable_plan_upgrade_mode(string $planSku): void
    {
        try {

            if (!PlanFeature::is_valid_plan_sku($planSku)) {
                mobilo_log(__METHOD__, "Invalid planSku provided sku: {$planSku}");
                return;
            }

            // disable custom purchase mode if enabled
            PlanFeature::disable_custom_order_mode();

            setcookie(self::$cookieKey, $planSku, time() + 10 * 60, '/'); // for 10min

            // set the data in global variable
            global $lwmc_plan_upgrade_mode;
            $lwmc_plan_upgrade_mode = $planSku;
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
        }
    }

    /**
     * Disables the plan upgrade mode by clearing the corresponding cookie and setting the global variable.
     *
     */
    public static function disable_plan_upgrade_mode(): void
    {
        try {
            if (!self::is_plan_upgrade_mode_enabled()) {
                return;
            }
            setcookie(self::$cookieKey, '', time() - 3600, '/');
            // set the data in global variable
            global $lwmc_plan_upgrade_mode;
            $lwmc_plan_upgrade_mode = false;
        } catch (Throwable $th) {
            mobilo_log(__METHOD__, $th->getMessage());
        }
    }
}
