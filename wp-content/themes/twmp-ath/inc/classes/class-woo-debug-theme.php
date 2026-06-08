<?php

namespace TWMP_THEME\Inc;

use TWMP_THEME\Inc\Traits\Singleton;

class Woo_Debug_Theme
{
    use Singleton;

    protected function __construct()
    {
        if ($this->is_enabled()) {
            $this->setup_hooks();
        }
    }

    protected function is_enabled()
    {
        return defined('TWMP_ENABLE_WOO_DEBUG') && TWMP_ENABLE_WOO_DEBUG;
    }

    protected function setup_hooks()
    {
        add_action('woocommerce_order_status_changed', [$this, 'log_processing_status_change'], 20, 4);
        add_action('woocommerce_checkout_order_processed', [$this, 'log_checkout_order_processed'], 5, 3);
    }

    public function log_processing_status_change($order_id, $from, $to, $order)
    {
        if ($to !== 'processing') {
            return;
        }

        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            error_log("ORDER {$order_id} -> processing but WP_DEBUG not enabled");
        }

        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $lines = array_map(function ($i, $frame) {
            $file = isset($frame['file']) ? $frame['file'] : '(unknown)';
            $line = isset($frame['line']) ? $frame['line'] : '';
            $func = isset($frame['function']) ? $frame['function'] : '';

            return sprintf('#%d %s:%s %s()', $i, $file, $line, $func);
        }, array_keys($bt), $bt);

        error_log("ORDER {$order_id} transitioned to processing. from={$from}. Backtrace:\n" . implode("\n", $lines));
    }

    public function log_checkout_order_processed($order_id, $posted_data, $order)
    {
        if (!$order instanceof \WC_Order) {
            $order = wc_get_order($order_id);
        }

        error_log('ORDER DEBUG #' . $order_id);
        error_log('total=' . $order->get_total());
        error_log('payment_method=' . $order->get_payment_method());
        error_log('needs_payment=' . ($order->needs_payment() ? 'yes' : 'no'));
    }
}
