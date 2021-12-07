<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( empty( $product ) || empty( $comment ) || $comment->comment_parent || empty( $settings ) ) {
	return;
}
$product_title = $product->get_title();
if ( $settings->get_params( 'custom_fields_enable' ) ) {
	$custom_fields = apply_filters( 'woocommerce_photo_reviews_custom_fields', get_comment_meta( $comment->comment_ID, 'wcpr_custom_fields', true ), $comment, $product );
	if ( is_array( $custom_fields ) && count( $custom_fields ) ) {
		$number_of_fields = 0;
		ob_start();
		foreach ( $custom_fields as $custom_field ) {
			$custom_field_name  = apply_filters( 'woocommerce_photo_reviews_custom_field_name', $custom_field['name'], $custom_field );
			$custom_field_value = apply_filters( 'woocommerce_photo_reviews_custom_field_value', $custom_field['value'], $custom_field );
			$custom_field_unit  = apply_filters( 'woocommerce_photo_reviews_custom_field_unit', $custom_field['unit'], $custom_field );
			if ( ! $custom_field_name || ! $custom_field_value ) {
				continue;
			}
			ob_start();
			?>
			<div class="wcpr-review-custom-field">
				<span class="wcpr-review-custom-field-name"><?php echo $custom_field_name ?></span>:
				<span class="wcpr-review-custom-field-value"><?php echo $custom_field_unit ? $custom_field_value . ' ' . $custom_field_unit : $custom_field_value ?></span>
			</div>
			<?php
			echo apply_filters( 'woocommerce_photo_reviews_custom_field_html', ob_get_clean(), $custom_field );
			$number_of_fields ++;
		}
		$custom_fields_html = apply_filters( 'woocommerce_photo_reviews_custom_fields_html', ob_get_clean(), $custom_fields );
		if ( $number_of_fields ) {
			?>
			<div class="wcpr-review-custom-fields <?php echo esc_attr( 'wcpr-review-custom-fields-' . $number_of_fields ) ?>">
				<?php
				echo $custom_fields_html;
				?>
			</div>
			<?php
		}
	}
}
echo sprintf('<div class="kt-reviews-image-container kt-reviews-image-container-image-popup-%s">', esc_attr($image_popup));
if ( get_comment_meta( $comment->comment_ID, 'reviews-images' ) ) {
	$image_post_ids = get_comment_meta( $comment->comment_ID, 'reviews-images', true );
	?>
	<div class="kt-wc-reviews-images-wrap-wrap">
		<?php
		$i = 0;
		foreach ($image_post_ids as $image_post_id){
			if (! wc_is_valid_url( $image_post_id ) ){
				$image_post = get_post( $image_post_id );
				if ( ! $image_post ) {
					continue;
				}
				$alt       = get_post_meta( $image_post_id, '_wp_attachment_image_alt', true );
				$image_alt = $alt ? $alt : $product_title;
				?>
				<div class="reviews-images-item" data-image_src="<?php echo esc_attr( wp_get_attachment_image_url( $image_post_id, 'full' ) ) ?>"
				     data-index="<?php echo esc_attr( $i ); ?>" data-image_caption="<?php esc_attr_e( $image_post->post_excerpt ) ?>">
					<img class="review-images"
					     src="<?php echo esc_url( apply_filters( 'woocommerce_photo_reviews_thumbnail_photo', wp_get_attachment_image_url( $image_post_id ), $image_post_id, $comment ) ); ?>"
					     alt="<?php echo esc_attr( apply_filters( 'woocommerce_photo_reviews_image_alt', $image_alt, $image_post_id, $comment ) ) ?>"/>
				</div>
				<?php
			}else{
				?>
				<div class="reviews-images-item" data-image_src="<?php echo esc_attr( $image_post_id ) ?>" data-index="<?php echo esc_attr( $i ); ?>">
					<img class="review-images" src="<?php echo esc_url( $image_post_id ); ?>" alt="<?php echo esc_attr( $product_title ) ?>"/>
				</div>
				<?php
			}
			$i ++;
		}
		?>
	</div>
	<div class="big-review-images">
		<div class="big-review-images-content-container">
			<div class="big-review-images-content"></div>
			<?php
			if ( $caption_enable ) {
				?>
				<div class="wcpr-review-image-caption"></div>
				<?php
			}
			?>
		</div>
		<span class="wcpr-close-normal"></span>
		<div class="wcpr-rotate">
			<input type="hidden" class="wcpr-rotate-value" value="0">
			<span class="wcpr-rotate-left wcpr_rotate-rotate-left-circular-arrow-interface-symbol" title="<?php esc_attr_e( 'Rotate left 90 degrees', 'woocommerce-photo-reviews' ) ?>"></span>
            <span class="wcpr-rotate-right wcpr_rotate-rotating-arrow-to-the-right" title="<?php esc_attr_e( 'Rotate right 90 degrees', 'woocommerce-photo-reviews' ) ?>"></span>
		</div>
		<?php
		if ( count( $image_post_ids ) > 1 ) {
			?>
			<span class="wcpr-prev-normal"></span>
			<span class="wcpr-next-normal"></span>
			<?php
		}
		?>
	</div>
	<?php
}
echo sprintf('</div>');
if ( $settings->get_params( 'photo', 'helpful_button_enable' ) ){
	$helpful_label = $settings->get_params( 'photo', 'helpful_button_title' , VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend::get_language());
	$up_votes      = get_comment_meta( $comment->comment_ID, 'wcpr_vote_up', false );
	$down_votes    = get_comment_meta( $comment->comment_ID, 'wcpr_vote_down', false );
	$class         = 'wcpr-comment-helpful-button-container';
	if ( in_array( $vote_info, $up_votes ) ) {
		$class .= ' wcpr-comment-helpful-button-voted-up';
	} elseif ( in_array( $vote_info, $down_votes ) ) {
		$class .= ' wcpr-comment-helpful-button-voted-down';
	}
	?>
    <div class="<?php echo esc_attr( $class ) ?>" data-comment_id="<?php echo esc_attr( $comment->comment_ID ) ?>">
        <div class="wcpr-comment-helpful-button-voting-overlay"></div>
		<?php
		if ( $helpful_label ) {
			?>
            <span class="wcpr-comment-helpful-button-label"><?php echo $helpful_label ?></span>
			<?php
		}
		?>
        <div class="wcpr-comment-helpful-button-vote-container">
            <span class="wcpr-comment-helpful-button-up-vote-count"><?php echo( count( $up_votes ) + absint( get_comment_meta( $comment->comment_ID, 'wcpr_vote_up_count', true ) ) ) ?></span>
            <span class="wcpr-comment-helpful-button wcpr-comment-helpful-button-up-vote woocommerce-photo-reviews-vote-like"></span>
            <span class="wcpr-comment-helpful-button wcpr-comment-helpful-button-down-vote woocommerce-photo-reviews-vote-like"></span>
            <span class="wcpr-comment-helpful-button-down-vote-count"><?php echo( count( $down_votes ) + absint( get_comment_meta( $comment->comment_ID, 'wcpr_vote_down_count', true ) ) ) ?></span>
        </div>
    </div>
	<?php
}
?>