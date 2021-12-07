<?php
/**
 * 美化数量输入框
 */

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
function wp_footer_add_theme_js(){
?>
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri();?>/js/theme.js"></script>
<?php
}
add_action('wp_footer', 'wp_footer_add_theme_js');