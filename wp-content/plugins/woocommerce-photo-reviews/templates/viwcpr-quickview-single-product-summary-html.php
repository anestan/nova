<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$product_title = $product->get_title();
$product_image = $product->get_image();
if ($is_shortcode){
	?>
	<div class="shortcode-wcpr-single-product-summary-content-wrapper" style="display: none;">
		<div class="shortcode-wcpr-single-product-summary-content-container" style="border-top: 1px solid;">
			<div class="shortcode-wcpr-single-product-summary-content">
				<?php
				if ( $product->get_image_id() ) {
					echo '<div class="shortcode-wcpr-single-product-summary-image"><img src = "' . esc_url( wp_get_attachment_thumb_url( $product->get_image_id() ) ) . '" ></div>';
				}
				?>
				<div class="shortcode-wcpr-single-product-summary-meta">
					<div class="shortcode-wcpr-single-product-summary-meta-title">
						<a href="<?php echo esc_url( $product->get_permalink() ); ?>" target="_blank"><?php echo $product->get_title(); ?></a>
					</div>
					<?php do_action( 'woocommerce_photo_reviews_shortcode_masonry_item_after_summary_meta_title', $comment, $product ); ?>
					<div class="shortcode-wcpr-single-product-summary-meta-reviews">
						<?php echo do_shortcode( '[wc_photo_reviews_rating_html]' ); ?>
					</div>
					<div class="shortcode-wcpr-single-product-summary-meta-price">
						<p class="price"><?php echo $product->get_price_html(); ?></p>
					</div>
				</div>
			</div>
			<?php do_action( 'woocommerce_photo_reviews_shortcode_masonry_item_after_product_summary_content', $comment, $product ); ?>
			<div class="shortcode-wcpr-single-product-summary-meta-shop">
				<?php
				if ( apply_filters( 'woocommerce_photo_reviews_shortcode_use_add_to_cart_template', is_product() ) ) {
					do_action( 'woocommerce_' . $product->get_type() . '_add_to_cart' );
				} else {
					echo do_shortcode( '[add_to_cart show_price="false" style="" id="' . $comment->comment_post_ID . '"]' );
				}
				?>
			</div>
		</div>
	</div>
	<?php
}else{
	?>
	<div class="wcpr-single-product-summary-content-wrapper">
		<div class="wcpr-single-product-summary-content">

			<div class="wcpr-single-product-summary-image">
				<?php echo $product_image ? $product_image : wc_placeholder_img(); ?>
			</div>

			<div class="wcpr-single-product-summary-meta">
				<div class="wcpr-single-product-summary-meta-title">
					<span><?php echo $product_title; ?></span>
				</div>
				<div class="wcpr-single-product-summary-meta-reviews">
					<?php
					echo do_shortcode( '[wc_photo_reviews_rating_html]' );
					?>
				</div>
				<div class="wcpr-single-product-summary-meta-price">
					<p class="price"><?php echo $product->get_price_html(); ?></p>
				</div>
			</div>
		</div>
		<div class="wcpr-single-product-summary-meta-shop">
			<?php do_action( 'woocommerce_' . $product->get_type() . '_add_to_cart' ); ?>
		</div>
	</div>
	<?php
}
?>