<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$prefix_class = $is_shortcode ? 'shortcode-' : '';
?>
<div class="<?php echo esc_attr($prefix_class); ?>wcpr-modal-light-box">
	<div class="<?php echo esc_attr($prefix_class); ?>wcpr-modal-light-box-wrapper">
		<div class="<?php echo esc_attr($prefix_class); ?>wcpr-overlay"></div>
		<div class="<?php echo esc_attr($prefix_class); ?>wcpr-modal-wrap-container">
			<span class="<?php echo esc_attr($prefix_class); ?>wcpr-prev"></span>
			<span class="<?php echo esc_attr($prefix_class); ?>wcpr-next"></span>
			<span class="<?php echo esc_attr($prefix_class); ?>wcpr-close"></span>
			<div id="<?php echo esc_attr($prefix_class); ?>wcpr-modal-wrap" class="<?php echo esc_attr($prefix_class); ?>wcpr-modal-wrap">
				<div id="<?php echo esc_attr($prefix_class); ?>reviews-content-left" class="<?php echo esc_attr($prefix_class); ?>wcpr-modal-content">
					<div id="<?php echo esc_attr($prefix_class); ?>reviews-content-left-main"></div>
					<div id="<?php echo esc_attr($prefix_class); ?>reviews-content-left-modal"></div>
				</div>
				<div id="<?php echo esc_attr($prefix_class); ?>reviews-content-right" class="<?php echo esc_attr($prefix_class); ?>wcpr-modal-content">
					<div class="<?php echo esc_attr($prefix_class); ?>reviews-content-right-meta"></div>
					<div class="<?php echo esc_attr($prefix_class); ?>wcpr-single-product-summary">
						<?php
						if (!empty($product) && is_a($product, 'WC_Product') ){
							do_action( 'wcpr_woocommerce_single_product_summary', $product );
                        }
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>