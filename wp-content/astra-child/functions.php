<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}
add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

/**
* 移除WordPress加载的JS和CSS链接中的版本号
*/
function remove_cssjs_ver( $src ) {
	if( strpos( $src, 'ver=' ) ){
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter('style_loader_src', 'remove_cssjs_ver', 999 );
add_filter('script_loader_src', 'remove_cssjs_ver', 999 );

// 谷歌标签管理器Head
function wp_head_add_google_tag_manager_code(){
	?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NXN6S7X');</script>
<!-- End Google Tag Manager -->
<?php
}
// add_action('wp_head', 'wp_head_add_google_tag_manager_code');

// 谷歌标签管理器Body
function wp_body_add_google_tag_manager_code(){
	?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NXN6S7X"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?php
}
// add_action('wp_body_open', 'wp_body_add_google_tag_manager_code');

// 订单结算详情页标题
function order_received_title($title){
	if(is_order_received_page()){
		$title['title']='Oredr Checkout Detail';
		return $title;
	}
	return $title;
}
add_filter('document_title_parts', 'order_received_title', 10, 1);

// 产品数量表单前缀
function qtyBeforeBtn(){
	if (!is_cart()){
		echo '<div class="qty-text">Quantity</div>';
	}
	echo '<div class="qty-before-btn btnQ">-</div>';
}
add_action("woocommerce_before_quantity_input_field", "qtyBeforeBtn");

// 产品数量表单后缀
function qtyAfterBtn(){
	 echo '<div class="qty-after-btn btnQ">+</div>';
}
add_action("woocommerce_after_quantity_input_field", "qtyAfterBtn");

// 产品数量增减按钮脚本
function wp_footer_add_js(){
	?>
<script type="text/javascript" src="/wp-content/themes/astra-child/themeA.js"></script>
<?php
}
add_action('wp_footer', 'wp_footer_add_js');

