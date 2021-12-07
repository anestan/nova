<?php
// no direct access allowed
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
$plugin_url = plugins_url( '', __FILE__ );
$plugin_url = str_replace( '/includes', '', $plugin_url );
define( 'WOOCOMMERCE_PHOTO_REVIEWS_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woocommerce-photo-reviews" . DIRECTORY_SEPARATOR );
define( 'WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES', WOOCOMMERCE_PHOTO_REVIEWS_DIR . "includes" . DIRECTORY_SEPARATOR );
define( 'WOOCOMMERCE_PHOTO_REVIEWS_ADMIN', WOOCOMMERCE_PHOTO_REVIEWS_DIR . "admin" . DIRECTORY_SEPARATOR );
define( 'WOOCOMMERCE_PHOTO_REVIEWS_FRONTEND', WOOCOMMERCE_PHOTO_REVIEWS_DIR . "frontend" . DIRECTORY_SEPARATOR );
define( 'WOOCOMMERCE_PHOTO_REVIEWS_TEMPLATES', WOOCOMMERCE_PHOTO_REVIEWS_DIR . "templates" . DIRECTORY_SEPARATOR );
define( 'WOOCOMMERCE_PHOTO_REVIEWS_PLUGINS', WOOCOMMERCE_PHOTO_REVIEWS_DIR . "plugins" . DIRECTORY_SEPARATOR );
define( 'VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS', $plugin_url . "/css/" );
define( 'VI_WOOCOMMERCE_PHOTO_REVIEWS_JS', $plugin_url . "/js/" );
define( 'VI_WOOCOMMERCE_PHOTO_REVIEWS_IMAGES', $plugin_url . "/images/" );
require_once WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . "data.php";
require_once WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . "check_update.php";
require_once WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . "update.php";
require_once WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . "mobile_detect.php";
if ( is_file( WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . "wp-async-request.php" ) ) {
	require_once WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . "wp-async-request.php";
}
if ( is_file( WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . "wp-background-process.php" ) ) {
	require_once WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . "wp-background-process.php";
}
if ( is_file( WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . "class-background-process-functions.php" ) ) {
	require_once WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . "class-background-process-functions.php";
}
if ( is_file( WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . "class-process.php" ) ) {
	require_once WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . "class-process.php";
}
if ( is_file( WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . 'elementor/elementor.php' ) ) {
	require_once WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . 'elementor/elementor.php';
}
global $wcpr_detect;
$wcpr_detect = new VillaTheme_Mobile_Detect();
require_once WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . "support.php";
require_once WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . "functions.php";
vi_include_folder( WOOCOMMERCE_PHOTO_REVIEWS_ADMIN, 'VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_' );
vi_include_folder( WOOCOMMERCE_PHOTO_REVIEWS_FRONTEND, 'VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_' );
vi_include_folder( WOOCOMMERCE_PHOTO_REVIEWS_PLUGINS, 'VI_WOOCOMMERCE_PHOTO_REVIEWS_Plugins_' );