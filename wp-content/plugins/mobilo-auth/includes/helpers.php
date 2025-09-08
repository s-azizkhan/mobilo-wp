<?php
defined('ABSPATH') || exit;


/**
 * Logs a message with the function/method name.
 */
function mobilo_auth_log($from, $message, $level = 'error', $context = []): void
{
    try {
        // If function name or message is empty, exit the function.
        if (empty($from) || empty($message)) {
            return;
        }

        // Validate and set the log level
        $valid_levels = ['error', 'warning', 'info', 'debug'];
        if (!in_array($level, $valid_levels)) {
            $level = 'error';
        }

        // if (defined('WP_DEBUG') && WP_DEBUG) {
        //     $current_time = date('Y-m-d H:i:s');
        //     $formatted_level = strtoupper($level);
        //     error_log("Mobilo Auth[$formatted_level][$current_time]: {$from}: {$message}" . json_encode($context));
        // }

        // log on woocommerce
        if (class_exists('WooCommerce')) {
            // WooCommerce logger
            $wcLogger = wc_get_logger();
            $context = ['source' => 'mobilo-auth-log', 'function' => $from, 'context' => $context];

            // Write the log to WooCommerce log file
            $wcLogger->log($level, $message, $context);
        }
    } catch (Throwable $th) {
        error_log('Error logging: ' . $th->getMessage());
    }
}