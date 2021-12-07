<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( empty( $my_comments ) || ! is_array( $my_comments ) || empty( $settings ) ) {
	return;
}
global $product;
$return_product = $product;
$user                = wp_get_current_user();
if ( $user ) {
	if ( ! empty( $user->ID ) ) {
		$vote_info = $user->ID;
	} else {
		$vote_info = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_the_user_ip();
	}
} else {
	$vote_info = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_the_user_ip();
}
$show_review_country = $settings->get_params( 'show_review_country' );
$review_title_enable = $settings->get_params( 'review_title_enable' );
?>
<div class="shortcode-wcpr-reviews woocommerce-Reviews">
	<div class="shortcode-wcpr-comments">
		<ol class="commentlist">
			<?php
			foreach ($my_comments as $comment){
				if ( $comment->comment_parent ) {
					continue;
				}
				$product = wc_get_product( $comment->comment_post_ID );
				if ( ! $product ) {
					$product = $return_product;
					continue;
				}
				?>
				<li  <?php comment_class(); ?> id="li-comment-<?php echo $comment->comment_ID ?>">
					<div id="comment-<?php echo $comment->comment_ID; ?>" class="comment_container">
						<?php
						/**
						 * The woocommerce_review_before hook
						 *
						 * @hooked woocommerce_review_display_gravatar - 10
						 */
						?>
						<div class="shortcode-wcpr-review-before">
							<?php
							echo get_avatar( $comment, apply_filters( 'woocommerce_review_gravatar_size', '60' ), '' );
							if ( $show_review_country ) {
								$review_country = get_comment_meta( $comment->comment_ID, 'wcpr_review_country', true );
								if ( $review_country ) {
									?>
									<div class="wcpr-review-country"
									     title="<?php echo esc_attr( isset( $countries[ $review_country ] ) ? $countries[ $review_country ] : $review_country ); ?>">
										<i style="<?php echo VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend::fix_style( 0.4 ) ?>"
										   class="vi-flag-64 flag-<?php echo strtolower( $review_country ) ?> "></i><?php echo esc_html( $review_country ); ?>
									</div>
									<?php
								}
							}
							?>
						</div>
						<div class="comment-text">
							<?php
							/**
							 * The woocommerce_review_before_comment_meta hook.
							 *
							 * @hooked woocommerce_review_display_rating - 10
							 */
							$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );
							if ( $rating && 'yes' === get_option( 'woocommerce_enable_review_rating' ) ) {
								echo wc_get_rating_html( $rating );
							}
							/**
							 * The woocommerce_review_meta hook.
							 *
							 * @hooked woocommerce_review_display_meta - 10
							 * @hooked WC_Structured_Data::generate_review_data() - 20
							 */
							if ( '0' === $comment->comment_approved){
								?>
								<p class="meta">
									<em class="woocommerce-review__awaiting-approval">
										<?php esc_html_e( 'Your review is awaiting approval', 'woocommerce-photo-reviews' ); ?>
									</em>
								</p>
								<?php
							}else{
								?>
								<p class="meta">
									<strong class="woocommerce-review__author"><?php comment_author( $comment->comment_ID ); ?> </strong>
									<?php
									if ( 'yes' === get_option( 'woocommerce_review_rating_verification_label' ) && 1 == get_comment_meta( $comment->comment_ID, 'verified', true ) ) {
										if ( $settings->get_params( 'photo', 'verified' ) == 'badge' ) {
											echo '<em class="woocommerce-review__verified woocommerce-photo-reviews-verified ' . $settings->get_params( 'photo', 'verified_badge' ) . '"></em> ';
										} elseif ( $settings->get_params( 'photo', 'verified' ) == 'text' ) {
											echo '<em class="woocommerce-review__verified woocommerce-photo-reviews-verified">' . $settings->get_params( 'photo', 'verified_text' ) . '</em> ';
										} else {
											echo '<em class="woocommerce-review__verified woocommerce-photo-reviews-verified wcpr-icon-badge"></em>';
										}
									}
									?>
									<span class="woocommerce-review__dash">&ndash;</span>
									<time class="woocommerce-review__published-date" datetime="<?php echo esc_attr( get_comment_date( 'c', $comment->comment_ID ) ); ?>">
										<?php echo esc_html( get_comment_date( wc_date_format(), $comment->comment_ID ) ); ?>
									</time>
								</p>
								<?php
							}
							if ( $review_title_enable ) {
								$review_title = get_comment_meta( $comment->comment_ID, 'wcpr_review_title', true );
								if ( $review_title ) {
									?>
									<div class="shortcode-wcpr-review-title" title="<?php echo esc_attr( $review_title ); ?>"><?php echo esc_html( $review_title ); ?></div>
									<?php
								}
							}
							/**
							 * The woocommerce_review_comment_text hook
							 *
							 * @hooked woocommerce_review_display_comment_text - 10
							 */
							?>
							<div class="description"><?php comment_text( $comment->comment_ID ); ?></div>
							<?php
							do_action( 'viwcpr_get_template_basic_html', array(
								'settings'       => $settings,
								'comment'        => $comment,
								'product'        => $product,
								'vote_info'      => $vote_info,
								'image_popup'    => $image_popup_type,
								'caption_enable' => $caption_enable,
								'is_shortcode'   => true,
							) );
							$comment_children = $comment->get_children();
							if ( is_array( $comment_children ) && count( $comment_children ) ) {
								?>
								<ul class="children">
									<?php
									foreach ( $comment_children as $comment_child){
										?>
										<li <?php comment_class(); ?> id="li-comment-<?php echo $comment_child->comment_ID ?>">
											<div id="comment-<?php echo $comment_child->comment_ID; ?>" class="comment_container">
												<?php
												/**
												 * The woocommerce_review_before hook
												 *
												 * @hooked woocommerce_review_display_gravatar - 10
												 */
												echo get_avatar( $comment_child, apply_filters( 'woocommerce_review_gravatar_size', '60' ), '' );
												?>
												<div class="comment-text">
													<p class="meta">
														<strong class="woocommerce-review__author"><?php comment_author( $comment_child->comment_ID ); ?> </strong>
														<?php
														if ( 'yes' === get_option( 'woocommerce_review_rating_verification_label' ) && 1 == get_comment_meta( $comment_child->comment_ID, 'verified', true ) ) {
															if ( $settings->get_params( 'photo', 'verified' ) == 'badge' ) {
																echo '<em class="woocommerce-review__verified woocommerce-photo-reviews-verified ' . $settings->get_params( 'photo', 'verified_badge' ) . '"></em> ';
															} elseif ( $settings->get_params( 'photo', 'verified' ) == 'text' ) {
																echo '<em class="woocommerce-review__verified woocommerce-photo-reviews-verified">' . $settings->get_params( 'photo', 'verified_text' ) . '</em> ';
															} else {
																echo '<em class="woocommerce-review__verified woocommerce-photo-reviews-verified wcpr-icon-badge"></em>';
															}
														}
														?>
														<span class="woocommerce-review__dash">&ndash;</span>
														<time class="woocommerce-review__published-date" datetime="<?php echo esc_attr( get_comment_date( 'c', $comment_child->comment_ID ) ); ?>">
															<?php echo esc_html( get_comment_date( wc_date_format(), $comment_child->comment_ID ) ); ?>
														</time>
													</p>
													<div class="description"><?php comment_text( $comment_child->comment_ID ); ?></div>
												</div>
											</div>
										</li>
										<?php
									}
									?>
								</ul>
								<?php
							}
							?>
						</div>
					</div>
				</li>
				<?php
				$product = $return_product;
			}
			?>
		</ol>
	</div>
</div>
