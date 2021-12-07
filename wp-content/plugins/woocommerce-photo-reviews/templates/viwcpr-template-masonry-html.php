<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( empty( $my_comments ) || ! is_array( $my_comments ) || empty( $settings ) ) {
	return;
}
$prefix = $is_shortcode ? 'shortcode_' : '';
$prefix_class = $is_shortcode ? 'shortcode-' : '';
global $product;
$return_product = $product;
$grid_class='';
if (isset($cols)) {
	$grid_class = array(
		$prefix_class . 'wcpr-grid',
		$prefix_class . 'wcpr-masonry-' . $cols . '-col',
		$prefix_class . 'wcpr-masonry-popup-' . $masonry_popup,
	);
	if ( $enable_box_shadow ) {
		$grid_class[] = $prefix_class .'wcpr-enable-box-shadow';
	}
	if (!empty($loadmore_button)&& in_array($loadmore_button ,['on','1'])){
		$grid_class[] = 'wcpr-grid-loadmore';
    }
}
$countries           = VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Import_Ali::get_countries();
$show_review_country = $settings->get_params( 'show_review_country' );
$review_title_enable = $settings->get_params( 'review_title_enable' );
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
$caption_enable     = $settings->get_params( 'image_caption_enable' );
$image_title        = $masonry_popup === 'off' ? '' : esc_attr__( 'Click to view full screen', 'woocommerce-photo-reviews' );
$max_content_length = intval( $settings->get_params( 'photo', 'max_content_length' ) );
if ($grid_class){
    echo sprintf('<div class="%s" data-wcpr_columns="%s">',esc_attr( trim( implode( ' ', $grid_class ) ) ), esc_attr($cols ?? '3') );
}
foreach ( $my_comments as $v ) {
	if ( $v->comment_parent ) {
		continue;
	}
	$comment = $v;
	$product = $is_shortcode ? wc_get_product( $comment->comment_post_ID ) : $product;
	if ( $product ) {
		$product_title    = $product->get_title() . ' photo review';
		$comment_children = $comment->get_children();
		$rating           = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );
		echo sprintf( '<div id="%scomment-%s" class="%swcpr-grid-item"><div class="%swcpr-content">', $prefix_class, $v->comment_ID, $prefix_class, $prefix_class );
		do_action( 'woocommerce_photo_reviews_'.$prefix.'masonry_item_top', $comment, $product );
		$img_post_ids = get_comment_meta( $v->comment_ID, 'reviews-images', true );
		if ( is_array( $img_post_ids ) && count( $img_post_ids ) > 0 ) {
			?>
            <div class="<?php echo esc_attr( $prefix_class ); ?>reviews-images-container">
                <div class="<?php echo esc_attr( $prefix_class ); ?>reviews-images-wrap-left">
					<?php
					if ( count( $img_post_ids ) > 1 ) {
						foreach ( $img_post_ids as $img_post_ids_k => $img_post_id ) {
							if ( ! wc_is_valid_url( $img_post_id ) ) {
								$image_post = get_post( $img_post_id );
								if ( ! $image_post ) {
									continue;
								}
								$image_data         = wp_get_attachment_metadata( $img_post_id );
								$alt                = get_post_meta( $img_post_id, '_wp_attachment_image_alt', true );
								$image_alt          = $alt ? $alt : $product_title;
								$data_image_src     = wp_get_attachment_image_url( $img_post_id, 'full' );
								$data_image_caption = $caption_enable ? $image_post->post_excerpt : '';
								$thumb              = wp_get_attachment_thumb_url( $img_post_id );
								$href               = ( isset( $image_data['sizes']['wcpr-photo-reviews'] ) ? wp_get_attachment_image_url( $img_post_id, 'wcpr-photo-reviews' ) : ( isset( $image_data['sizes']['medium_large'] ) ? wp_get_attachment_image_url( $img_post_id, 'medium_large' ) : ( isset( $image_data['sizes']['medium'] ) ? wp_get_attachment_image_url( $img_post_id, 'medium' ) : $data_image_src ) ) );
								echo sprintf( '<div class="%sreviews-images-wrap"><a data-image_index="%s" data-image_src="%s" data-image_caption="%s" href="%s"><img class="%sreviews-images" src="%s" alt="%s"></a></div>',
									esc_attr( $prefix_class ), esc_attr( $img_post_ids_k ), esc_attr( $data_image_src ), esc_attr( $data_image_caption ),
									esc_url( apply_filters( 'woocommerce_photo_reviews_masonry_thumbnail_main', $href, $img_post_id ) ),
									esc_attr( $prefix_class ),esc_url( $thumb ),esc_attr( $image_alt )
								);
							}else{
								echo sprintf( '<div class="%sreviews-images-wrap"><a data-image_index="%s" href="%s"><img class="%sreviews-images" src="%s" alt="%s"></a></div>',
									esc_attr( $prefix_class ), esc_attr( $img_post_ids_k ), esc_attr( $img_post_id ),
									esc_attr( $prefix_class ),esc_url( $img_post_id ),esc_attr( $product_title )
								);
							}
						}
					}
					?>
                </div>
				<?php
				$clones     = $img_post_ids;
				$first_ele  = array_shift( $clones );
				$image_post = get_post( $first_ele );
				if (! wc_is_valid_url( $first_ele )){
					$image_data        = wp_get_attachment_metadata( $first_ele );
					$alt               = get_post_meta( $first_ele, '_wp_attachment_image_alt', true );
					$image_alt         = $alt ? $alt : $product_title;
					$data_original_src = wp_get_attachment_url( $first_ele );
					$img_type = ( isset( $image_data['sizes']['wcpr-photo-reviews'] ) ? 'wcpr-photo-reviews' : ( isset( $image_data['sizes']['medium_large'] ) ? 'medium_large' : ( isset( $image_data['sizes']['medium'] ) ? 'medium': '' ) ) );
					if ($img_type) {
						$src = wp_get_attachment_image_url( $first_ele, $img_type );
						$img_width = $image_data['sizes'][$img_type]['width'] ??'';
						$img_height = $image_data['sizes'][$img_type]['height'] ??'';
					}else {
						$src = $data_original_src;
						$img_width = '';
						$img_height = '';
					}
					if ( $caption_enable ) {
						echo sprintf('<div class="%sreviews-images-wrap-right"><div class="%swcpr-review-image-container">', esc_attr($prefix_class), esc_attr($prefix_class));
						echo sprintf('<div class="%swcpr-review-image-caption">%s</div><img class="%sreviews-images" data-original_src="%s" src="%s" alt="%s" title="%s" width="%s" height="%s">',
							esc_attr($prefix_class),$image_post->post_excerpt,esc_attr($prefix_class),
							esc_attr( $data_original_src ),esc_url( apply_filters( 'woocommerce_photo_reviews_masonry_thumbnail_main', $src, $first_ele ) ),
							esc_attr( $image_alt ),esc_attr( $image_title ),esc_attr( $img_width ),esc_attr( $img_height )
						);
						echo sprintf('</div></div>');
					} else {
						echo sprintf('<div class="%sreviews-images-wrap-right"><img class="%sreviews-images" data-original_src="%s" src="%s" alt="%s" title="%s" width="%s" height="%s"></div>',
							esc_attr($prefix_class),esc_attr($prefix_class),esc_attr( $data_original_src ),
							esc_url( apply_filters( 'woocommerce_photo_reviews_masonry_thumbnail_main', $src, $first_ele ) ),
							esc_attr( $image_alt ), esc_attr( $image_title ),esc_attr( $img_width ),esc_attr( $img_height )
						);
					}
				}else{
					echo sprintf('<div class="%sreviews-images-wrap-right"><img class="%sreviews-images" src="%s" alt="%s"></div>',
						esc_attr($prefix_class),esc_attr($prefix_class),esc_url( $first_ele ),esc_attr( $product_title )
					);
				}
				if ( count( $img_post_ids ) > 1 ) {
					echo sprintf('<div class="%simages-qty">+%s</div>',esc_attr($prefix_class),count( $img_post_ids ) - 1 );
				}
				?>
            </div>
			<?php
		}
		do_action( 'woocommerce_photo_reviews_'.$prefix.'masonry_item_before_main_content', $comment, $product );
		echo sprintf( '<div class="%sreview-content-container">', esc_attr( $prefix_class ) );
		if ( '0' === $v->comment_approved ){
			echo sprintf('<p class="meta"><em class="woocommerce-review__awaiting-approval">%s</em></p>',__( 'Your review is awaiting approval', 'woocommerce-photo-reviews' ));
		}else{
			?>
            <div class="<?php echo esc_attr($prefix_class); ?>review-content-container-top">
                <div class="<?php echo esc_attr($prefix_class); ?>review-content-container-top-left">
					<?php do_action( 'woocommerce_photo_reviews_'.$prefix.'masonry_item_main_content_top_left', $comment, $product ); ?>
                </div>
                <div class="<?php echo esc_attr($prefix_class); ?>review-content-container-top-right">
					<?php
					do_action( 'woocommerce_photo_reviews_'.$prefix.'masonry_item_main_content_top_right', $comment, $product );
					$review_country_html  = '';
					$comment_author_class = [$prefix_class.'wcpr-comment-author'];
					if ( $show_review_country ) {
						$review_country = get_comment_meta( $comment->comment_ID, 'wcpr_review_country', true );
						if ( $review_country ) {
							$comment_author_class[]= $prefix_class.'wcpr-comment-author-with-country';
							ob_start();
							?>
                            <div class="<?php echo esc_attr($prefix_class); ?>wcpr-review-country"
                                 title="<?php echo esc_attr( isset( $countries[ $review_country ] ) ? $countries[ $review_country ] : $review_country ); ?>">
                                <i style="<?php echo VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend::fix_style( 0.4 ) ?>"
                                   class="vi-flag-64 flag-<?php echo strtolower( $review_country ) ?>"></i>
                            </div>
							<?php
							$review_country_html = ob_get_clean();
						}
					}
					?>
                    <div class="<?php echo esc_attr( trim(implode(' ',$comment_author_class)) ) ?>">
						<?php
						echo $review_country_html;
						comment_author( $comment );
						if ( 'yes' === get_option( 'woocommerce_review_rating_verification_label' ) && 1 == get_comment_meta( $comment->comment_ID, 'verified', true ) ) {
							switch ($settings->get_params( 'photo', 'verified' )){
								case 'badge':
									echo sprintf('<em class="woocommerce-review__verified verified woocommerce-photo-reviews-verified %s"></em>',$settings->get_params( 'photo', 'verified_badge' ));
									break;
								case 'text':
									echo sprintf('<em class="woocommerce-review__verified verified woocommerce-photo-reviews-verified">%s</em>',$settings->get_params( 'photo', 'verified_text' ));
									break;
								default:
									echo sprintf('<em class="woocommerce-review__verified verified woocommerce-photo-reviews-verified %swcpr-icon-badge"></em>', esc_attr($prefix_class));
							}
						}
						?>
                    </div>
                    <div class="wcpr-review-rating">
						<?php
						if ( $rating > 0 ) {
							echo wc_get_rating_html( $rating );
						}
						if ( $settings->get_params( 'photo', 'show_review_date' ) ) {
							?>
                            <div class="wcpr-review-date">
								<?php
								$review_date_format = $settings->get_params( 'photo', 'custom_review_date_format' );
								if ( ! $review_date_format ) {
									$review_date_format = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_date_format();
								}
								comment_date( $review_date_format, $comment )
								?>
                            </div>
							<?php
						}
						?>
                    </div>
                </div>
            </div>
			<?php
			if ( $settings->get_params( 'custom_fields_enable' )){
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
                        <div class="wcpr-review-custom-fields <?php esc_attr_e( 'wcpr-review-custom-fields-' . $number_of_fields ) ?>">
							<?php
							echo $custom_fields_html;
							?>
                        </div>
						<?php
					}
				}
			}
		}
		if ( $review_title_enable  && ($review_title = get_comment_meta( $comment->comment_ID, 'wcpr_review_title', true)) ){
			echo sprintf('<div class="%swcpr-review-title" title="%s">%s</div>',esc_attr( $prefix_class ),esc_attr( $review_title ),esc_html( $review_title ));
		}
		$comment_content          = $comment->comment_content;
		$stripped_comment_content = strip_tags( $comment_content );
		$comment_content_length   = function_exists( 'mb_strlen' ) ? mb_strlen( $stripped_comment_content ) : strlen( $stripped_comment_content );
		if ( $comment_content_length > $max_content_length ) {
			$comment_content = function_exists( 'mb_substr' ) ? mb_substr( $stripped_comment_content, 0, $max_content_length ) : substr( $stripped_comment_content, 0, $max_content_length );
			$comment_content = '<div class="'.$prefix_class.'wcpr-review-content-short">' . $comment_content . '<span class="'.$prefix_class.'wcpr-read-more" title="' . esc_html__( 'Read more', 'woocommerce-photo-reviews' ) . '">' . esc_html__( '...More', 'woocommerce-photo-reviews' ) . '</span></div><div class="'.$prefix_class.'wcpr-review-content-full">' . apply_filters( 'woocommerce_photo_reviews_masonry_review_content', nl2br( $comment->comment_content ), $comment ) . '</div>';
		}
		?>
        <div class="<?php echo esc_attr($prefix_class); ?>wcpr-review-content"><?php echo nl2br( $comment_content ); ?></div>
		<?php
		if (is_array( $comment_children ) && count( $comment_children ) ){
			?>
            <div class="wcpr-comment-children">
                <div class="wcpr-comment-children-content">
					<?php
					foreach ( $comment_children as $comment_child ) {
						?>
                        <div class="wcpr-comment-child">
                            <div class="wcpr-comment-child-author">
								<?php
								ob_start();
								esc_html_e( 'Reply from ', 'woocommerce-photo-reviews' );
								?>
                                <span class="wcpr-comment-child-author-name"><?php comment_author( $comment_child ); ?></span>:
								<?php
								$comment_child_author = ob_get_clean();
								$comment_child_author = apply_filters( 'woocommerce_photo_reviews_reply_author_html', $comment_child_author, $comment_child );
								echo $comment_child_author;
								?>
                            </div>
                            <div class="wcpr-comment-child-content">
								<?php echo nl2br( $comment_child->comment_content ); ?>
                            </div>
                        </div>
						<?php
					}
					?>
                </div>
            </div>
			<?php
		}
		if ($settings->get_params( 'photo', 'helpful_button_enable' ) && 1 == $comment->comment_approved ){
			$helpful_label = $settings->get_params( 'photo', 'helpful_button_title', VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend::get_language() );
			$up_votes      = get_comment_meta( $comment->comment_ID, 'wcpr_vote_up', false );
			$down_votes    = get_comment_meta( $comment->comment_ID, 'wcpr_vote_down', false );
			$class         = 'wcpr-comment-helpful-button-container';
			if ( in_array( $vote_info, $up_votes ) ) {
				$class .= ' wcpr-comment-helpful-button-voted-up';
			} elseif ( in_array( $vote_info, $down_votes ) ) {
				$class .= ' wcpr-comment-helpful-button-voted-down';
			}
			?>
            <div class="<?php echo esc_attr( $class ) ?>"
                 data-comment_id="<?php echo esc_attr( $comment->comment_ID ) ?>">
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
		echo sprintf( '</div>' );
		if ( $show_product==='on'){
			wc_get_template( 'viwcpr-quickview-single-product-summary-html.php',
				array(
					'is_shortcode' => true,
					'comment' => $comment,
					'product' => $product
				),
				'woocommerce-photo-reviews' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
				WOOCOMMERCE_PHOTO_REVIEWS_TEMPLATES );
        }
		echo sprintf( '</div></div>' );
	}
	$product = $return_product;
}
if ($grid_class){
    if (!$is_shortcode) {
	    echo sprintf( '<div class="wcpr-grid-overlay wcpr-hidden"></div>' );
    }
    echo sprintf('</div>' );
}
?>
