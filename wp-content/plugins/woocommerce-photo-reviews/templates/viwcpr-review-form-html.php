<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$settings = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
$language = '';
if ( $settings->get_params( 'multi_language' ) ) {
	if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
		$default_lang     = apply_filters( 'wpml_default_language', null );
		$current_language = apply_filters( 'wpml_current_language', null );
		if ( $current_language && $current_language !== $default_lang ) {
			$language = $current_language;
		}
	}
}
$class         = [ 'woocommerce-photo-reviews-form-container' ];
$button_review = '';
$button_close  = '';
if ( $type === 'popup' ) {
	$class[]       = 'woocommerce-photo-reviews-form-popup woocommerce-photo-reviews-form-popup-hide';
	$button_review = sprintf( '<div class="woocommerce-photo-reviews-form-button-add-review-container"><span class="woocommerce-photo-reviews-form-button-add-review">%s</span></div>',
		apply_filters( 'wcpr_reviews_form_button_add_review_title', __( 'Write Your Review', 'woocommerce-photo-reviews' ) ) );
	$button_close  = sprintf( '<div class="woocommerce-photo-reviews-form-main-top"><span>%s</span><span class="woocommerce-photo-reviews-form-main-close"></span></div>',
		__( 'Add your review', 'woocommerce-photo-reviews' ) );
}
if ( is_product() ) {
	global $product;
	if ( $language ) {
		$product_id   = icl_object_id( $product->get_id(), 'product', true, $language );
		$product_wpml = wc_get_product( $product_id );
		if ( $product_wpml ) {
			$product = $product_wpml;
		}
	}
	$product = apply_filters( 'woocommerce_photo_reviews_form_product_object', $product );
	if ( ! $product ) {
		return;
	}
	?>
    <div class="<?php echo esc_attr( trim( implode( ' ', $class ) ) ) ?>">
		<?php echo $button_review; ?>
        <div class="woocommerce-photo-reviews-form-main">
            <div class="woocommerce-photo-reviews-form-main-inner">
				<?php echo $button_close; ?>
                <div class="woocommerce-photo-reviews-form-main-content">
                    <div class="woocommerce-photo-reviews-form">
						<?php
						if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) {
							?>
                            <div class="wcpr_review_form_wrapper">
                                <div class="wcpr_review_form">
									<?php
									$commenter        = wp_get_current_commenter();
									$comment_form     = array(
										/* translators: %s is product title */
										'title_reply'         => __( '', 'woocommerce-photo-reviews' ),
										/* translators: %s is product title */
										'title_reply_to'      => __( 'Leave a Reply to %s', 'woocommerce-photo-reviews' ),
										'title_reply_before'  => '<span id="reply-title" class="comment-reply-title">',
										'title_reply_after'   => '</span>',
										'comment_notes_after' => '',
										'fields'              => array(
											'author' => '<p class="comment-form-author"><label for="author">' . esc_html__( 'Name', 'woocommerce-photo-reviews' ) . '&nbsp;<span class="required">*</span></label> ' .
											            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" required /></p>',
											'email'  => '<p class="comment-form-email"><label for="email">' . esc_html__( 'Email', 'woocommerce-photo-reviews' ) . '&nbsp;<span class="required">*</span></label> ' .
											            '<input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" required /></p>',
										),
										'label_submit'        => __( 'Submit', 'woocommerce-photo-reviews' ),
										'logged_in_as'        => '',
										'comment_field'       => '',
										'id_form'             => 'wcpr_comment_form',
										'class_form'          => 'comment-form wcpr-comment-form',
									);
									$account_page_url = wc_get_page_permalink( 'myaccount' );
									if ( $account_page_url ) {
										/* translators: %s opening and closing link tags respectively */
										$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce-photo-reviews' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
									}
									if ( wc_review_ratings_enabled() ) {
										$comment_form['comment_field'] = '<div class="comment-form-rating"><label for="wcpr-rating">' . esc_html__( 'Your rating', 'woocommerce-photo-reviews' ) . '</label><select class="wcpr-rating" name="rating" id="wcpr-rating" required>
                                                                        <option value="">' . esc_html__( 'Rate&hellip;', 'woocommerce-photo-reviews' ) . '</option>
                                                                        <option value="5">' . esc_html__( 'Perfect', 'woocommerce-photo-reviews' ) . '</option>
                                                                        <option value="4">' . esc_html__( 'Good', 'woocommerce-photo-reviews' ) . '</option>
                                                                        <option value="3">' . esc_html__( 'Average', 'woocommerce-photo-reviews' ) . '</option>
                                                                        <option value="2">' . esc_html__( 'Not that bad', 'woocommerce-photo-reviews' ) . '</option>
                                                                        <option value="1">' . esc_html__( 'Very poor', 'woocommerce-photo-reviews' ) . '</option>
                                                                    </select></div>';
									}
									$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="wcpr-comment">' . esc_html__( 'Your review', 'woocommerce-photo-reviews' ) . '&nbsp;<span class="required">*</span></label><textarea id="wcpr-comment" name="comment" cols="45" rows="8" required placeholder="' . apply_filters( 'woocommerce_photo_reviews_form_comment_placeholder', esc_html__( 'What do you think of this product?', 'woocommerce-photo-reviews' ), $product ) . '"></textarea></p>';
									comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ), $product->get_id() );
									?>
                                </div>
                            </div>
							<?php
						}else {
							?>
                            <p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce-photo-reviews' ); ?></p>
							<?php
						}
						?>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<?php
} else {
	if ( isset( $_GET['product_id'] ) && $_GET['product_id'] ) {
		$product_id = sanitize_text_field( $_GET['product_id'] );
	}
	if ( ! $product_id ) {
		return;
	}
	if ( $language ) {
		$product_id_wpml = icl_object_id( $product_id, 'product', true, $language );
		if ( wc_get_product( $product_id_wpml ) ) {
			$product_id = $product_id_wpml;
		}
	}
	if ( is_user_logged_in() ) {
		$user       = wp_get_current_user();
		$user_name  = $user->display_name;
		$user_email = $user->user_email;
		$user_id    = $user->ID;
	} else {
		$user_name  = isset( $_GET['user_name'] ) ? base64_decode( urldecode( sanitize_text_field( $_GET['user_name'] ) ) ) : '';
		$user_email = isset( $_GET['user_email'] ) ? base64_decode( urldecode( sanitize_text_field( $_GET['user_email'] ) ) ) : '';
		$user_id    = isset( $_GET['user_id'] ) ? base64_decode( urldecode( sanitize_text_field( $_GET['user_id'] ) ) ) : '';
	}
	if ( ! comments_open( $product_id ) ) {
		return;
	}
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return;
	}
	$product_url   = $product->get_permalink();
	$product_title = $product->get_title();
	$product_image = $product->get_image();
	?>
    <div class="<?php echo esc_attr( trim( implode( ' ', $class ) ) ) ?>">
		<?php echo $button_review; ?>
        <div class="woocommerce-photo-reviews-form-main">
            <div class="woocommerce-photo-reviews-form-main-inner">
				<?php echo $button_close; ?>
                <div class="woocommerce-photo-reviews-form-main-content">
					<?php
					if ( $hide_product_details !== 'on' ) {
						?>
                        <div class="woocommerce-photo-reviews-form-product">
                            <div class="woocommerce-photo-reviews-form-content-wrapper">
                                <div class="woocommerce-photo-reviews-form-content">
                                    <div class="woocommerce-photo-reviews-form-image">
                                        <a href="<?php esc_attr_e( $product_url ) ?>" target="_blank"><?php echo $product_image; ?></a>
                                    </div>
                                    <div class="woocommerce-photo-reviews-form-meta">
                                        <div class="woocommerce-photo-reviews-form-meta-title">
                                            <a href="<?php esc_attr_e( $product_url ) ?>" target="_blank"><span><?php echo $product_title; ?></span></a>
                                        </div>
                                        <div class="woocommerce-photo-reviews-form-meta-reviews">
											<?php
											$rating_count = $product->get_rating_count();
											$review_count = $product->get_review_count();
											$average      = $product->get_average_rating();
											if ( $rating_count > 0 ) {
												?>
                                                <div class="woocommerce-product-rating">
													<?php echo wc_get_rating_html( $average, $rating_count ); ?>
                                                    (<?php printf( _n( '%s customer review', '%s customer reviews', $review_count, 'woocommerce-photo-reviews' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?>
                                                    )
                                                </div>
												<?php
											}
											?>
                                        </div>
										<?php
										if ( $hide_product_price !== 'on' ) {
											?>
                                            <div class="woocommerce-photo-reviews-form-meta-price">
                                                <p class="price"><?php echo $product->get_price_html(); ?></p>
                                            </div>
											<?php
										}
										?>
                                    </div>
                                </div>
                            </div>
                        </div>
						<?php
					}
					?>
                    <div class="woocommerce-photo-reviews-form">
						<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( $user_email, $user_id, $product_id ) ) {
							?>
                            <div class="wcpr_review_form_wrapper">
                                <div class="wcpr_review_form">
									<?php
									$comment_form     = array(
										/* translators: %s is product title */
										'title_reply'         => __( '', 'woocommerce-photo-reviews' ),
										/* translators: %s is product title */
										'title_reply_to'      => __( 'Leave your Reply to %s', 'woocommerce-photo-reviews' ),
										'title_reply_before'  => '<span id="reply-title" class="comment-reply-title">',
										'title_reply_after'   => '</span>',
										'comment_notes_after' => '',
										'fields'              => array(
											'author' => '<p class="comment-form-author"><label for="author">' . esc_html__( 'Name', 'woocommerce-photo-reviews' ) . '&nbsp;<span class="required">*</span></label> ' .
											            '<input id="author" name="author" type="text" value="' . esc_attr( $user_name ) . '" size="30" required /></p>',
											'email'  => empty( $user_email ) ? '<p class="comment-form-email"><label for="email">' . esc_html__( 'Email', 'woocommerce-photo-reviews' ) . '&nbsp;<span class="required">*</span></label> ' .
											                                   '<input id="email" name="email" type="email" value="' . esc_attr( $user_email ) . '" size="30" required /></p>' : '<input id="email" name="email" type="hidden" value="' . esc_attr( $user_email ) . '"/>',
										),
										'label_submit'        => __( 'Submit', 'woocommerce-photo-reviews' ),
										'logged_in_as'        => '',
										'comment_field'       => '',
										'id_form'             => 'wcpr_comment_form',
										'class_form'          => 'comment-form wcpr-comment-form'
									);
									$account_page_url = wc_get_page_permalink( 'myaccount' );
									if ( $account_page_url ) {
										/* translators: %s opening and closing link tags respectively */
										$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce-photo-reviews' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
									}
									if ( wc_review_ratings_enabled() ) {
										$comment_form['comment_field'] = '<div class="comment-form-rating"><label for="wcpr-rating">' . esc_html__( 'Your rating', 'woocommerce-photo-reviews' ) . '</label><select class="wcpr-rating" name="rating" id="wcpr-rating" required>
						<option value="">' . esc_html__( 'Rate&hellip;', 'woocommerce-photo-reviews' ) . '</option>
						<option value="5">' . esc_html__( 'Perfect', 'woocommerce-photo-reviews' ) . '</option>
						<option value="4">' . esc_html__( 'Good', 'woocommerce-photo-reviews' ) . '</option>
						<option value="3">' . esc_html__( 'Average', 'woocommerce-photo-reviews' ) . '</option>
						<option value="2">' . esc_html__( 'Not that bad', 'woocommerce-photo-reviews' ) . '</option>
						<option value="1">' . esc_html__( 'Very poor', 'woocommerce-photo-reviews' ) . '</option>
					</select></div>';
									}
									$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your review', 'woocommerce-photo-reviews' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required placeholder="' . apply_filters( 'woocommerce_photo_reviews_form_comment_placeholder', esc_html__( 'What do you think of this product?', 'woocommerce-photo-reviews' ), $product ) . '"></textarea></p>';
									comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ), $product_id );
									?>
                                </div>
                            </div>
							<?php
						} else {
							?>
                            <p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce-photo-reviews' ); ?></p>
							<?php
						}
						?>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<?php
}
?>