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

// 添加谷歌标签管理器代码
// require get_stylesheet_directory() . "/hooks/add-google-tag-manager-code.php";

// 定义结算订单详情页标题
require get_stylesheet_directory() . "/hooks/custom-page-info.php";
// 定制结算页面
require get_stylesheet_directory() . "/hooks/custom-checkout-page.php";
// 定制数量输入框
require get_stylesheet_directory() . "/hooks/custom-quantity-input.php";

// FaceBook 验证
add_action('wp_head', 'add_code_facebook');
function add_code_facebook(){
	echo '<meta name="facebook-domain-verification" content="80c9cw8ixfdlsd4trmnz659zzd4awh" />';
}

add_action('wp_head', 'add_facebook_event_code', 9999999);
function add_facebook_event_code(){
	?>

<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '1220144328453982');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=1220144328453982&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->

<?php
}

// remove_action( 'woocommerce_proceed_to_checkout', array( WC_Stripe_Payment_Request::instance(), 'display_payment_request_button_html' ), 1 );

// add_action("woocommerce_checkout_before_order_review_heading", [WC_Stripe_Payment_Request::instance(), 'display_payment_request_button_html'], 1 ); //结算订单之前


// remove_action( 'woocommerce_proceed_to_checkout', array( WC_Stripe_Payment_Request::instance(), 'display_payment_request_button_html' ), 1 );
// remove_action( 'woocommerce_proceed_to_checkout', array( WC_Stripe_Payment_Request::instance(), 'display_payment_request_button_separator_html' ), 2 );

// add_action( 'woocommerce_checkout_before_customer_details', array( WC_Stripe_Payment_Request::instance(), 'display_payment_request_button_html' ), 1 );
// add_action( 'woocommerce_checkout_before_customer_details', array( WC_Stripe_Payment_Request::instance(), 'display_payment_request_button_separator_html' ), 2 );
		
		
		
		
		