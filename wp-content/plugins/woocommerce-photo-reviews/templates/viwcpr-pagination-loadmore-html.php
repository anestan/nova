<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$button_class ='wcpr-load-more-reviews-button';
$button_class .= $is_shortcode ? ' shortcode-wcpr-load-more-reviews-button':'';
if (!empty($only_button)){
    echo sprintf('<div class="wcpr-load-more-reviews-button-container"><span class="%s" data-cpage="%s">%s</span></div>',
	    esc_attr($button_class),esc_attr($cpage), esc_html__( 'Load more', 'woocommerce-photo-reviews' ));
    return;
}
if (!$product_id ){
	return;
}
?>
<div class="wcpr-load-more-reviews-button-modal" style="display: none;">
	<div class="wcpr-load-more-reviews-button-container">
		<span class="<?php echo esc_attr($button_class) ?>"><?php esc_html_e( 'Load more', 'woocommerce-photo-reviews' ); ?></span>
	</div>
	<input type="hidden" class="wcpr-load-more-reviews-cpage" value="<?php echo esc_attr($cpage) ?>">
	<input type="hidden" class="wcpr-load-more-reviews-product-id" value="<?php echo esc_attr(is_array($product_id)? implode('',$product_id): $product_id); ?>">
	<input type="hidden" class="wcpr-load-more-reviews-rating" value="<?php echo esc_attr($rating) ?>">
	<input type="hidden" class="wcpr-load-more-reviews-verified" value="<?php echo esc_attr($verified) ?>">
	<input type="hidden" class="wcpr-load-more-reviews-image" value="<?php echo esc_attr($image); ?>">
</div>
