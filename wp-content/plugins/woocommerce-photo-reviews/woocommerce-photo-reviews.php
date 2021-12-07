<?php
/**
 * Plugin Name: WooCommerce Photo Reviews Premium
 * Plugin URI: https://villatheme.com/extensions/woocommerce-photo-reviews/
 * Description: Allow you to automatically send email to your customers to request reviews. Customers can include photos in their reviews.
 * Version: 1.1.5.4
 * Author: VillaTheme
 * Author URI: https://villatheme.com
 * Text Domain: woocommerce-photo-reviews
 * Domain Path: /languages
 * Copyright 2018-2021 VillaTheme.com. All rights reserved.
 * Requires PHP: 7.0
 * Requires at least: 5.0
 * Tested up to: 5.8
 * WC requires at least: 4.0
 * WC tested up to: 5.5
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION', '1.1.5.4' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	$init_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woocommerce-photo-reviews" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "includes.php";
	require_once $init_file;
} else {
	add_action( 'admin_notices', array( 'VI_WooCommerce_Photo_Reviews', 'global_note' ) );
}

if ( ! class_exists( 'VI_WooCommerce_Photo_Reviews' ) ) {
	class VI_WooCommerce_Photo_Reviews {

		public function __construct() {
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		}

		public function load_plugin_textdomain() {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'woocommerce-photo-reviews' );
			load_textdomain( 'woocommerce-photo-reviews', WP_PLUGIN_DIR . "/woocommerce-photo-reviews/languages/woocommerce-photo-reviews-$locale.mo" );
			load_plugin_textdomain( 'woocommerce-photo-reviews', false, basename( dirname( __FILE__ ) ) . "/languages" );
			if ( class_exists( 'VillaTheme_Support_Pro' ) ) {
				new VillaTheme_Support_Pro(
					array(
						'support'   => 'https://villatheme.com/supports/forum/plugins/woocommerce-photo-reviews/',
						'docs'      => 'http://docs.villatheme.com/?item=woocommerce-photo-reviews',
						'review'    => 'https://codecanyon.net/downloads',
						'css'       => VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS,
						'image'     => VI_WOOCOMMERCE_PHOTO_REVIEWS_IMAGES,
						'slug'      => 'woocommerce-photo-reviews',
						'menu_slug' => 'woocommerce-photo-reviews',
						'version'   => VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION,
					)
				);
			}
		}

		public static function global_note() {
			?>
            <div id="message" class="error">
                <p><?php _e( 'Please install and activate WooCommerce to use WooCommerce Photo Reviews.', 'woocommerce-photo-reviews' ); ?></p>
            </div>
			<?php
		}
	}
}

new VI_WooCommerce_Photo_Reviews();