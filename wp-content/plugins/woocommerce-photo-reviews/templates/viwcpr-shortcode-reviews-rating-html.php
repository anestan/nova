<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="woocommerce-photo-reviews-rating-html-shortcode">
	<?php
	echo wc_get_rating_html( $rating );
	if ('on' === $review_count_enable && $review_count){
		echo sprintf('<span class="woocommerce-photo-reviews-review-count-container">(<span class="woocommerce-photo-reviews-review-count">%s</span>)</span>',$review_count);
	}
	?>
</div>
