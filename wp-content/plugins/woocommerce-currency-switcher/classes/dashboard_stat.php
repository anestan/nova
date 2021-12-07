<?php

class woocs_woo_stat {

    public $orders_table = "";

    public function __construct() {
        global $wpdb;
        $this->orders_table = $wpdb->prefix . 'wc_order_stats';
    }

    public function init() {
        add_action('woocommerce_analytics_update_order_stats', array($this, 'recalculate_order_stats'));
        //  add_action('woocommerce_order_status_changed', 'recalculate_order_stats', 10, 3);
    }

    public function recalculate_order_stats($order_id) {
        global $wpdb;
        global $WOOCS;
        $_order_currency = get_post_meta($order_id, '_order_currency', true);
        $order_rate = get_post_meta($order_id, '_woocs_order_rate', true);
        $order_recalculated = get_post_meta($order_id, 'woocs_order_stat_recalculated', true);
        $currencies = $WOOCS->get_currencies();
        $decimals = 2;
        if (isset($currencies[$_order_currency])) {
            $decimals = $currencies[$_order_currency]['decimals'];
        }
        if (!$order_rate) {
            if (isset($currencies[$_order_currency])) {
                $order_rate = $currencies[$_order_currency]['rate'];
            } else {
                return false;
            }
        }
        if ($_order_currency == $WOOCS->default_currency) {
            return false;
        }
        $order_data = $wpdb->get_row($wpdb->prepare("SELECT total_sales,tax_total,shipping_total,net_total FROM {$this->orders_table} WHERE order_id = %d", $order_id), ARRAY_A);

        foreach ($order_data as $key => $value) {
            $order_data[$key] = $WOOCS->back_convert($value, $order_rate, $decimals);
        }
        $format = array(
            '%f',
            '%f',
            '%f',
            '%f',
        );
        $wpdb->update($this->orders_table,
                $order_data,
                array('order_id' => $order_id),
                $format,
                array('%d')
        );
        update_post_meta($order_id, 'woocs_order_stat_recalculated', true);
    }

    public function recalculate_all_stats() {
        global $wpdb;
        $order_ids = $wpdb->get_col("SELECT order_id FROM {$this->orders_table} WHERE 1 ");
        foreach ($order_ids as $order_id) {
            $this->recalculate_order_stats($order_id);
        }
    }

}
