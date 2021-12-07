<?php

/*
  Plugin Name: WOOCS - WooCommerce Currency Switcher
  Plugin URI: https://currency-switcher.com/
  Description: Currency Switcher for WooCommerce that allows to the visitors and customers on your woocommerce store site switch currencies and optionally apply selected currency on checkout
  Author: realmag777
  Version: 2.3.7
  Requires at least: WP 4.9.0
  Tested up to: WP 5.8
  Requires PHP: 7.0
  Text Domain: woocommerce-currency-switcher
  Domain Path: /languages
  Forum URI: https://pluginus.net/support/forum/woocs-woocommerce-currency-switcher-multi-currency-and-multi-pay-for-woocommerce/
  Author URI: https://pluginus.net/
  WC requires at least: 3.6
  WC tested up to: 5.5
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (isset($_GET['woocommerce_gpf'])) {
    return false;
}
//disable WOOCS influence for REST api requests
if (isset($_SERVER['SCRIPT_URI'])) {
    $uri = parse_url(trim($_SERVER['SCRIPT_URI']));
    $uri = explode('/', trim($uri['path'], ' /'));
    if ($uri[0] === 'wp-json') {

        $show_legacy1 = in_array('widget-types', $uri); //tmp for wp 5.8
        $show_legacy2 = in_array('sidebars', $uri); //tmp for wp 5.8

        if (!$show_legacy1 && !$show_legacy2) {
            $allow = ['woocs'];
            if (isset($uri[1]) AND!in_array($uri[1], $allow)) {
                return; //!!it is important for different reports to exclude WOOCS influence
            }
        }
    }
}


if (defined('DOING_AJAX')) {

    if (isset($_REQUEST['action'])) {
        //do not recalculate refund amounts when we are in order backend
        if ($_REQUEST['action'] == 'woocommerce_refund_line_items') {
            return;
        }

        if (isset($_REQUEST['order_id']) AND $_REQUEST['action'] == 'woocommerce_load_order_items') {
            return;
        }
    }
}

define('WOOCS_VERSION', '2.3.7');
//define('WOOCS_VERSION', uniqid('woocs-'));
define('WOOCS_MIN_WOOCOMMERCE', '3.6');
define('WOOCS_PATH', plugin_dir_path(__FILE__));
define('WOOCS_LINK', plugin_dir_url(__FILE__));
define('WOOCS_PLUGIN_NAME', plugin_basename(__FILE__));

//classes
include_once WOOCS_PATH . 'classes/storage.php';
include_once WOOCS_PATH . 'classes/cron.php';
include_once WOOCS_PATH . 'classes/alert.php';
include_once WOOCS_PATH . 'classes/smart-designer.php';
include_once WOOCS_PATH . 'classes/fixed/fixed_amount.php';
include_once WOOCS_PATH . 'classes/fixed/fixed_price.php';
include_once WOOCS_PATH . 'classes/statistic.php';
include_once WOOCS_PATH . 'classes/reports.php';
include_once WOOCS_PATH . 'classes/dashboard_stat.php';
include_once WOOCS_PATH . 'classes/profiles.php';

//19-07-2021
class WOOCS_STARTER {

    private $default_woo_version = 3.6; //from version 2.3.1 woo lower 3.6 are obsolets and not supported
    private $actualized = 0.0;
    private $version_key = "woocs_woo_version";
    private $_woocs = null;
    public $disable_plugin = array(); // add a slug of the  page  to  disble  the plugin. Example: 'account','cart'
    public $reverse_disable_plugin = 0; // set: true - to activate the plugin  on exact  pages

    public function __construct() {
        $this->actualized = floatval(get_option($this->version_key, $this->default_woo_version));
        $apl = get_option('woocs_activate_page_list', '');
        if ($apl) {
            $this->disable_plugin = array_map('trim', explode(',', $apl));
        } else {
            $this->disable_plugin = [];
        }
        $this->reverse_disable_plugin = get_option('woocs_activate_page_list_reverse', 1);
    }

    public function update_version() {
        if (defined('WOOCOMMERCE_VERSION') AND ( $this->actualized !== floatval(WOOCOMMERCE_VERSION))) {
            update_option('woocs_woo_version', WOOCOMMERCE_VERSION);
        }
    }

    public function get_actual_obj() {

        if (count($this->disable_plugin) AND!is_admin()) {
            $exclude = false;
            if (preg_match("/\/([-a-zA-Z0-9_]+)[\/]$/", $_SERVER['SCRIPT_URI'], $matches)) {
                $exclude = in_array($matches[1], $this->disable_plugin);
            }

            if ($this->reverse_disable_plugin) {
                $exclude = !$exclude;
            }

            if ($exclude) {
                return false;
            }
        }

        if ($this->_woocs != null) {
            return $this->_woocs;
        }

        if (version_compare($this->actualized, '3.3', '<')) {
            //include_once WOOCS_PATH . 'classes/woocs_before_33.php';
            //from 05-05-2021 woocommerce versions lower v.3.3 not supported
        } else {
            include_once WOOCS_PATH . 'classes/woocs.php'; //woocs_after_33.php
            include_once WOOCS_PATH . 'classes/fixed/fixed_coupon.php';
            include_once WOOCS_PATH . 'classes/fixed/fixed_shipping.php';
            include_once WOOCS_PATH . 'classes/fixed/fixed_shipping_free.php';
            include_once WOOCS_PATH . 'classes/fixed/fixed_user_role.php';
            include_once WOOCS_PATH . 'classes/auto_switcher.php';
        }
        $this->_woocs = new WOOCS();
        return $this->_woocs;
    }

}

//+++
if (isset($_GET['P3_NOCACHE'])) {
    //stupid trick for that who believes in P3
    return;
}

//+++
//fix: because of long id which prevent js script working
function woocs_short_id($smth) {
    return substr(md5($smth), 1, 7);
}

//+++
$WOOCS_STARTER = new WOOCS_STARTER();

$WOOCS = $WOOCS_STARTER->get_actual_obj();
if ($WOOCS) {
    $GLOBALS['WOOCS'] = $WOOCS;
    add_action('init', array($WOOCS, 'init'), 1);
}

//****
//rate + interes
add_filter('woocs_currency_data_manipulation', function ($currencies) {
    foreach ($currencies as $key => $value) {
        if (isset($value['rate_plus'])) {
            $interes = 0;
            if (!strpos($value['rate_plus'], '%')) {
                $interes = floatval($value['rate_plus']);
            } else {
                //example: 20%
                $interes = floatval(floatval($value['rate']) / 100) * intval($value['rate_plus']);
            }
            $currencies[$key]['rate'] = floatval($value['rate']) + $interes;
        }
    }

    return $currencies;
}, 1, 1);

//hide WOOCS meta in the order
add_filter('woocommerce_order_item_get_formatted_meta_data', function ($formatted_meta) {
    foreach ($formatted_meta as $key => $meta) {
        if (in_array($meta->key, ['_woocs_order_rate', '_woocs_order_base_currency', '_woocs_order_currency_changed_mannualy'])) {
            unset($formatted_meta[$key]);
        }
    }
    return $formatted_meta;
}, 10, 1);

//for bots
function woocs_is_bot(&$botname = '') {
    $bots = array(
        'rambler', 'googlebot', 'aport', 'yahoo', 'msnbot', 'turtle', 'mail.ru', 'omsktele',
        'yetibot', 'picsearch', 'sape.bot', 'sape_context', 'gigabot', 'snapbot', 'alexa.com',
        'megadownload.net', 'askpeter.info', 'igde.ru', 'ask.com', 'qwartabot', 'yanga.co.uk',
        'scoutjet', 'similarpages', 'oozbot', 'shrinktheweb.com', 'aboutusbot', 'followsite.com',
        'dataparksearch', 'google-sitemaps', 'appEngine-google', 'feedfetcher-google',
        'liveinternet.ru', 'xml-sitemaps.com', 'agama', 'metadatalabs.com', 'h1.hrn.ru',
        'googlealert.com', 'seo-rus.com', 'yaDirectBot', 'yandeG', 'yandex',
        'yandexSomething', 'Copyscape.com', 'AdsBot-Google', 'domaintools.com',
        'Nigma.ru', 'bing.com', 'dotnetdotcom'
    );

    $HTTP_USER_AGENT = "";
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
    }
    foreach ($bots as $bot) {
        if (stripos($HTTP_USER_AGENT, $bot) !== false) {
            $botname = $bot;
            return true;
        }
    }

    return false;
}

add_action('wp_head', function() {
    if (woocs_is_bot()) {
        if (class_exists('WOOCS')) {
            global $WOOCS;
            $WOOCS->reset_currency();
        }
    }
}, 1);

//for separators
add_filter('option_woocommerce_price_thousand_sep', function($value) {
    global $WOOCS;

    if (is_object($WOOCS)) {
        $current_currency = $WOOCS->get_woocommerce_currency();
        $value = $WOOCS->get_thousand_sep($current_currency, $value);
    }

    return $value;
});

//for separators
add_filter('option_woocommerce_price_decimal_sep', function($value) {
    global $WOOCS;

    if (is_object($WOOCS)) {
        $current_currency = $WOOCS->get_woocommerce_currency();
        $value = $WOOCS->get_decimal_sep($current_currency, $value);
    }

    return $value;
});
