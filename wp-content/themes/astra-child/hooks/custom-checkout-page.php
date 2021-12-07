<?php
/**
 * 结帐字段排序 有些字段必须
 */

add_filter( 'woocommerce_default_address_fields', 'custom_override_default_locale_fields' );
function custom_override_default_locale_fields( $fields ) {
    
    $fields['first_name']['placeholder'] = $fields['first_name']['label'];

    $fields['last_name']['placeholder'] = $fields['last_name']['label'];
    
    $fields['postcode']['placeholder'] = $fields['postcode']['label'];

    $fields['country']['class'][0] = 'form-row-first';

    $fields['state']['priority'] = 41;
    $fields['state']['class'][0] = 'form-row-last';

    $fields['city']['priority'] = 42;
    $fields['city']['class'][0] = 'form-row-first';

    $fields['postcode']['priority'] = 43;
    $fields['postcode']['class'][0] = 'form-row-last';

    return $fields;
}

add_filter("woocommerce_checkout_fields", "custom_override_checkout_fields", 1);
function custom_override_checkout_fields($fields) {
    
    $fields['billing']['billing_phone']['placeholder'] = $fields['billing']['billing_phone']['label']; 
    $fields['billing']['billing_email']['placeholder'] = 'Email address'; 
    $fields['billing']['billing_email']['priority'] = 1;
    
    return $fields;
}

// 自定义结算页脚本
add_action('wp_footer', 'wp_footer_add_theme_checkout_js');
function wp_footer_add_theme_checkout_js(){
	if( !is_checkout() ) return;
?>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri();?>/js/checkout.js"></script>
<?php
}



// 自定义优惠卷输入框
remove_action("woocommerce_before_checkout_form", "woocommerce_checkout_coupon_form");
add_action('woocommerce_review_order_before_payment', 'add_custom_coupon_form', 10); //支付方式之前
// add_action("woocommerce_checkout_before_order_review_heading", "add_custom_coupon_form"); //结算订单之前
function add_custom_coupon_form(){ 
    if (!wc_coupons_enabled()) {
        return;
    }
    ?> 
<div class="coupon container">
    <div class="coupon wrapper">
        <input type="text" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" id="coupon_code" />
        <span class="coupon button"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></span>
    </div>
</div>
<?php 
}


/**
 * 登陆 注册按钮
 */ 
add_action("woocommerce_checkout_billing", "add_custom_login_button");
function add_custom_login_button(){
    if(is_user_logged_in()) return;

    $link = '<a href="'.get_permalink(get_option('woocommerce_myaccount_page_id')).'?redirect_to='.wc_get_checkout_url().'">';
    wc_print_notice(
        esc_html__('Returning customer?', 'woocommerce').$link.esc_html__( 'Click here to login', 'woocommerce' ) .'</a>',
        'notice'
    );
}