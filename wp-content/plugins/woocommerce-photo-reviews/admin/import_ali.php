<?php

/**
 * Class VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Import_Ali
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Import_Ali {
	protected $settings;
	protected $import_settings;

	public function __construct() {
		$this->settings        = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
		$this->import_settings = wp_parse_args( get_option( 'wcpr_import_ali_option', array() ), array(
			'import_method'     => 'sku',
			'ratings'           => array( 1, 2, 3, 4, 5 ),
			'countries'         => array(),
			'with_picture'      => 1,
			'translate'         => 1,
			'sort'              => '',
			'number_of_reviews' => 20,
			'download_images'   => 1,
			'verified'          => 1,
			'vote'              => 1,
			'review_status'     => '1',
		) );
		add_filter( 'manage_edit-product_columns', array( $this, 'import_reviews_in_product_list' ) );
		add_action( 'manage_product_posts_custom_column', array( $this, 'column_callback_product' ) );
		add_action( 'wp_ajax_wcpr_get_reviews_from_ali', array( $this, 'get_reviews' ) );
		add_action( 'wp_ajax_wcpr_download_image_from_ali', array( $this, 'download_images' ) );
		add_action( 'admin_footer', array( $this, 'import_reviews_form' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ), 999 );
		add_action( 'add_meta_boxes', array( $this, 'create_custom_meta_box' ) );
		/*download images from aliexpress for imported reviews*/
		add_filter( 'bulk_actions-edit-comments', array( $this, 'register_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-comments', array( $this, 'bulk_action_handler' ), 99, 3 );
		add_action( 'admin_notices', array( $this, 'bulk_action_admin_notice' ) );
	}

	public function bulk_action_admin_notice() {
		if ( ! empty( $_REQUEST['wcpr_bulk_download_reviews_images'] ) ) {
			$reviews_count = intval( $_REQUEST['wcpr_bulk_download_reviews_images'] );
			printf( '<div id="message" class="updated fade">' .
			        _n( 'Downloaded images of %s review.',
				        'Downloaded images of %s reviews.',
				        $reviews_count,
				        'woocommerce-photo-reviews'
			        ) . '</div>', $reviews_count );
		}
	}

	public function bulk_action_handler( $redirect_to, $doaction, $comment_ids ) {
		if ( $doaction !== 'wcpr_download_reviews_images' ) {
			return $redirect_to;
		}
		$count = 0;
		if ( is_array( $comment_ids ) && count( $comment_ids ) ) {
//	        Need to require these files
			add_filter( 'big_image_size_threshold', '__return_false' );
			if ( ! function_exists( 'media_handle_upload' ) ) {
				require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
				require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
				require_once( ABSPATH . "wp-admin" . '/includes/media.php' );
			}
			foreach ( $comment_ids as $comment_id ) {
				$reviews_images = array();
				$images         = get_comment_meta( $comment_id, 'reviews-images', true );
				$downloaded     = 0;
				if ( is_array( $images ) && count( $images ) ) {
					foreach ( $images as $image ) {
						if ( wc_is_valid_url( $image ) ) {
							$tmp = download_url( $image );
							if ( is_wp_error( $tmp ) ) {
								$reviews_images[] = $image;
								continue;
							}

							$desc       = "WCPR reviews images";
							$file_array = array();

							// Set variables for storage
							// fix file filename for query strings
							preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $image, $matches );
							$file_array['name']     = basename( $matches[0] );
							$file_array['tmp_name'] = $tmp;

							// If error storing temporarily, unlink
							if ( is_wp_error( $tmp ) ) {
								@unlink( $file_array['tmp_name'] );
								$file_array['tmp_name'] = '';
								$reviews_images[]       = $image;
								continue;
							}
							$product_id = get_comment( $comment_id )->comment_post_ID;
							// do the validation and storage stuff
							$id = media_handle_sideload( $file_array, $product_id, $desc );
							// If error storing permanently, unlink
							if ( is_wp_error( $id ) ) {
								@unlink( $file_array['tmp_name'] );

								$reviews_images[] = $image;
								continue;
							}
							$downloaded ++;
							$reviews_images[] = $id;
						} else {
							$reviews_images[] = $image;
						}
					}

				}
				if ( $downloaded > 0 ) {
					$count ++;
				}
				update_comment_meta( $comment_id, 'reviews-images', $reviews_images );
			}
			$redirect_to = add_query_arg( 'wcpr_bulk_download_reviews_images', $count, $redirect_to );
		}

		return $redirect_to;
	}

	public function register_bulk_actions( $bulk_actions ) {
		$bulk_actions['wcpr_download_reviews_images'] = __( 'Download reviews images', 'woocommerce-photo-reviews' );

		return $bulk_actions;
	}

	public function create_custom_meta_box() {
		add_meta_box(
			'wcpr_custom_product_meta_box',
			__( 'Import reviews from AliExpress <em>(optional)</em>', 'woocommerce-photo-reviews' ),
			array( $this, 'add_custom_content_meta_box' ),
			'product',
			'normal',
			'default'
		);
	}

	public function add_custom_content_meta_box() {
		global $post;
		$post_id = $post->ID;
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$total       = get_comments( array(
			'post_id'    => $post_id,
			'status'     => array( 'approve', 'trash', 'spam' ),
			'meta_query' => array(
				array(
					'key'     => 'id_import_reviews_from_ali',
					'compare' => 'EXISTS'
				)
			),
			'count'      => 1
		) );
		$import_info = array(
			'status'  => '',
			'message' => __( 'Never', 'woocommerce-photo-reviews' ),
			'time'    => '',
			'number'  => ''
		);
		if ( get_post_meta( $post_id, '_wcpr_product_ali_import_info', true ) ) {
			$import_info = get_post_meta( $post_id, '_wcpr_product_ali_import_info', true );
		}
		?>
        <span class="wcpr-import-ali-import-info-total"><?php esc_html_e( 'Total: ', 'woocommerce-photo-reviews' ); ?>
            <span class="wcpr-import-ali-import-info-total-data"><?php echo $total; ?></span></span>
        <span class="wcpr-import-ali-import-info-time"><?php esc_html_e( 'Last: ', 'woocommerce-photo-reviews' ); ?>
            <span class="wcpr-import-ali-import-info-time-data"><?php echo ( isset( $import_info['time'] ) && $import_info['time'] ) ? date( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_datetime_format(), $import_info['time'] ) : '' ?></span></span>

        <span class="wcpr-import-ali-import-info-status"><?php esc_html_e( 'Status: ', 'woocommerce-photo-reviews' ); ?>
            <span class="wcpr-import-ali-import-info-status-data <?php
            switch ( $import_info['status'] ) {
	            case 'failed':
		            echo 'wcpr-failed';
		            break;
	            case 'successful':
		            echo 'wcpr-successful';
		            break;
	            default:
            }
            ?> "><?php echo isset( $import_info['message'] ) ? $import_info['message'] : esc_html__( 'Never', 'woocommerce-photo-reviews' ) ?></span></span>
        <span class="wcpr-import-ali-import-info-number"><?php esc_html_e( 'Number: ', 'woocommerce-photo-reviews' ); ?>
            <span class="wcpr-import-ali-import-info-number-data"><?php echo isset( $import_info['number'] ) ? absint( $import_info['number'] ) : '' ?></span></span>

        <p><span class="wcpr-import-ali-button-popup-single button"><?php esc_html_e( 'Import', 'woocommerce-photo-reviews' ) ?>

                <input
                        class="wcpr-import-ali-product-id" type="hidden"
                        value="<?php echo $post_id; ?>">
        </p>

		<?php
	}

	public function admin_enqueue() {
		$screen = get_current_screen();
		if ( $screen->id == 'edit-product' ) {
			if ( ! wp_script_is( 'select2' ) ) {
				wp_enqueue_script( 'wcpr_admin_select2_script', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'select2.js', array( 'jquery' ) );
				wp_enqueue_style( 'wcpr_admin_seletct2', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'select2.min.css' );
			}
			wp_enqueue_style( 'wcpr-admin-import-ali-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'import-ali.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_enqueue_script( 'wcpr-admin-import-ali-script', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'import-ali.js', array( 'jquery' ) );
		} elseif ( $screen->id == 'product' ) {
			if ( ! wp_script_is( 'select2' ) ) {
				wp_enqueue_script( 'wcpr_admin_select2_script', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'select2.js', array( 'jquery' ) );
				wp_enqueue_style( 'wcpr_admin_seletct2', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'select2.min.css' );
			}
			wp_enqueue_style( 'wcpr-admin-import-ali-single-product-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'import-ali-single-product.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_enqueue_script( 'wcpr-admin-import-ali-single-product-script', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'import-ali-single-product.js', array( 'jquery' ) );
		}
	}

	public function import_reviews_form() {

		$screen = get_current_screen();
		if ( $screen->id == 'edit-product' ) {
			?>
            <div class="wcpr-import-ali-container-wrap">
                <div class="wcpr-import-ali-overlay"></div>
                <div class="wcpr-import-ali-container">
                    <div class="wcpr-import-ali-current-product">
                        <span class="wcpr-import-ali-current-product-title"></span>
                    </div>
                    <div class="wcpr-import-ali-table-check">
                        <table>
                            <tbody>
                            <tr class="wcpr-import-ali-sku-wrap">
                                <th>
                                    <label for="wcpr-import-ali-sku"><?php esc_html_e( 'AliExpress Product ID', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <input type="text" class="wcpr-import-ali-sku" id="wcpr-import-ali-sku">
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    <label for="wcpr-import-ali-countries"><?php esc_html_e( 'Countries', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <select class="wcpr-import-ali-countries" id="wcpr-import-ali-countries"
                                            name="wcpr_import_ali_countries" multiple>
										<?php
										$countries     = $this->import_settings['countries'];
										$all_countries = $this->get_countries();
										foreach ( $all_countries as $key => $value ) {
											?>
                                            <option value="<?php echo $key ?>" <?php if ( in_array( $key, $countries ) ) {
												echo esc_attr( 'selected' );
											} ?>><?php echo $value ?></option>
											<?php
										}
										?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="wcpr-import-ali-rating"><?php esc_html_e( 'Ratings', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <div class="wcpr-import-ali-ratings-container">
										<?php
										$rating_options = array(
											1 => esc_html__( '1 star', 'woocommerce-photo-reviews' ),
											2 => esc_html__( '2 stars', 'woocommerce-photo-reviews' ),
											3 => esc_html__( '3 stars', 'woocommerce-photo-reviews' ),
											4 => esc_html__( '4 stars', 'woocommerce-photo-reviews' ),
											5 => esc_html__( '5 stars', 'woocommerce-photo-reviews' ),
										);
										foreach ( $rating_options as $rating_options_k => $rating_options_v ) {
											?>
                                            <div class="wcpr-import-ali-ratings">
                                                <input class="wcpr-import-ali-rating"
                                                       id="<?php echo esc_attr( "wcpr-import-ali-rating-{$rating_options_k}" ) ?>"
                                                       name="wcpr_import_ali_ratings"
                                                       type="checkbox" <?php if ( in_array( $rating_options_k, $this->import_settings['ratings'] ) ) {
													echo esc_attr( 'checked' );
												} ?>
                                                       value="<?php echo esc_attr( $rating_options_k ) ?>"><label
                                                        for="<?php echo esc_attr( "wcpr-import-ali-rating-{$rating_options_k}" ) ?>"><?php echo esc_attr( $rating_options_v ) ?></label>
                                            </div>
											<?php
										}

										?>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    <label for="wcpr-import-ali-translate"><?php esc_html_e( 'Translate to English', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" name="wcpr_import_ali_translate"
                                           class="wcpr-import-ali-translate"
                                           id="wcpr-import-ali-translate" <?php if ( $this->import_settings['translate'] ) {
										echo 'checked';
									} ?>><label
                                            for="wcpr-import-ali-translate"><?php esc_html_e( 'Use translated content from AliExpress if available', 'woocommerce-photo-reviews' ) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="wcpr-import-ali-with-picture"><?php esc_html_e( 'With images', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" name="wcpr_import_ali_with_picture"
                                           class="wcpr-import-ali-with-picture"
                                           id="wcpr-import-ali-with-picture" <?php if ( $this->import_settings['with_picture'] ) {
										echo 'checked';
									} ?>><label
                                            for="wcpr-import-ali-with-picture"><?php esc_html_e( 'Only import reviews with images', 'woocommerce-photo-reviews' ) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="wcpr-import-ali-download-images"><?php esc_html_e( 'Download images', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" name="wcpr_import_ali_download_images"
                                           class="wcpr-import-ali-download-images"
                                           id="wcpr-import-ali-download-images" <?php if ( $this->import_settings['download_images'] ) {
										echo 'checked';
									} ?>><label
                                            for="wcpr-import-ali-download-images"><?php esc_html_e( 'If a review has images, download them to your server', 'woocommerce-photo-reviews' ) ?></label>
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    <label for="wcpr-import-ali-verified"><?php esc_html_e( 'Verified owner', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" name="wcpr_import_ali_verified"
                                           class="wcpr-import-ali-verified"
                                           id="wcpr-import-ali-verified" <?php if ( $this->import_settings['verified'] ) {
										echo 'checked';
									} ?>><label
                                            for="wcpr-import-ali-verified"><?php esc_html_e( 'Mark imported reviews as Verified owner', 'woocommerce-photo-reviews' ) ?></label>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="wcpr-import-ali-vote"><?php esc_html_e( 'Vote count', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" name="wcpr_import_ali_vote"
                                           class="wcpr-import-ali-vote"
                                           id="wcpr-import-ali-vote" <?php if ( $this->import_settings['vote'] ) {
										echo 'checked';
									} ?>>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="wcpr-import-ali-number"><?php esc_html_e( 'Number of reviews', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <input type="number" min="1" max="100" class="wcpr-import-ali-number"
                                           id="wcpr-import-ali-number" name="wcpr_import_ali_number"
                                           value="<?php echo esc_attr( intval( $this->import_settings['number_of_reviews'] ) ) ?>">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="wcpr-import-ali-number"><?php esc_html_e( 'Phrases filter', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=woocommerce-photo-reviews#/phrases_filter' ) ) ?>"
                                       target="_blank"><?php esc_html_e( 'Change phrases filter settings', 'woocommerce-photo-reviews' ) ?></a>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="wcpr-import-ali-review-status"><?php esc_html_e( 'Set review status', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <select class="wcpr-import-ali-review-status" id="wcpr-import-ali-review-status">
                                        <option value="1" <?php selected( $this->import_settings['review_status'], '1' ) ?>><?php esc_html_e( 'Approved', 'woocommerce-photo-reviews' ) ?></option>
                                        <option value="0" <?php selected( $this->import_settings['review_status'], '0' ) ?>><?php esc_html_e( 'Pending', 'woocommerce-photo-reviews' ) ?></option>
                                        <option value="spam" <?php selected( $this->import_settings['review_status'], 'spam' ) ?>><?php esc_html_e( 'Spam', 'woocommerce-photo-reviews' ) ?></option>
                                    </select>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <p>
                        <span class="wcpr-import-ali-check button-primary"><?php esc_html_e( 'Import', 'woocommerce-photo-reviews' ) ?></span>
                        <span class="wcpr-import-ali-import-all"><?php esc_html_e( 'Import', 'woocommerce-photo-reviews' ) ?></span>
                        <span class="wcpr-import-ali-close-form button"><?php esc_html_e( 'Close', 'woocommerce-photo-reviews' ) ?></span>
                    </p>

                </div>
            </div>
			<?php
		} elseif ( $screen->id == 'product' ) {
			global $post;
			$post_id = $post->ID;
			$product = wc_get_product( $post_id );
			if ( $product ) {
				?>
                <div class="wcpr-import-ali-container-wrap">
                    <div class="wcpr-import-ali-overlay"></div>
                    <div class="wcpr-import-ali-container">
                        <div class="wcpr-import-ali-current-product">
                            <span class="wcpr-import-ali-current-product-title"><?php echo $product->get_title(); ?></span>
                        </div>
                        <div class="wcpr-import-ali-table-check">
                            <table>
                                <tbody>

                                <tr class="wcpr-import-ali-sku-wrap">
                                    <th>
                                        <label for="wcpr-import-ali-sku"><?php esc_html_e( 'AliExpress Product ID', 'woocommerce-photo-reviews' ) ?></label>
                                    </th>
                                    <td>
                                        <input type="text" class="wcpr-import-ali-sku" id="wcpr-import-ali-sku"
                                               value="<?php echo $product->get_sku(); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcpr-import-ali-countries"><?php esc_html_e( 'Countries', 'woocommerce-photo-reviews' ) ?></label>
                                    </th>
                                    <td>
                                        <select class="wcpr-import-ali-countries" id="wcpr-import-ali-countries"
                                                name="wcpr_import_ali_countries" multiple>
											<?php
											$countries     = $this->import_settings['countries'];
											$all_countries = $this->get_countries();
											foreach ( $all_countries as $key => $value ) {
												?>
                                                <option value="<?php echo $key ?>" <?php if ( in_array( $key, $countries ) ) {
													echo esc_attr( 'selected' );
												} ?>><?php echo $value ?></option>
												<?php
											}
											?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcpr-import-ali-rating"><?php esc_html_e( 'Ratings', 'woocommerce-photo-reviews' ) ?></label>
                                    </th>
                                    <td>
                                        <div class="wcpr-import-ali-ratings-container">
											<?php
											$rating_options = array(
												1 => esc_html__( '1 star', 'woocommerce-photo-reviews' ),
												2 => esc_html__( '2 stars', 'woocommerce-photo-reviews' ),
												3 => esc_html__( '3 stars', 'woocommerce-photo-reviews' ),
												4 => esc_html__( '4 stars', 'woocommerce-photo-reviews' ),
												5 => esc_html__( '5 stars', 'woocommerce-photo-reviews' ),
											);
											foreach ( $rating_options as $rating_options_k => $rating_options_v ) {
												?>
                                                <div class="wcpr-import-ali-ratings">
                                                    <input class="wcpr-import-ali-rating"
                                                           id="<?php echo esc_attr( "wcpr-import-ali-rating-{$rating_options_k}" ) ?>"
                                                           name="wcpr_import_ali_ratings"
                                                           type="checkbox" <?php if ( in_array( $rating_options_k, $this->import_settings['ratings'] ) ) {
														echo esc_attr( 'checked' );
													} ?>
                                                           value="<?php echo esc_attr( $rating_options_k ) ?>"><label
                                                            for="<?php echo esc_attr( "wcpr-import-ali-rating-{$rating_options_k}" ) ?>"><?php echo esc_attr( $rating_options_v ) ?></label>
                                                </div>
												<?php
											}
											?>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        <label for="wcpr-import-ali-translate"><?php esc_html_e( 'Translate to English', 'woocommerce-photo-reviews' ) ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" name="wcpr_import_ali_translate"
                                               class="wcpr-import-ali-translate"
                                               id="wcpr-import-ali-translate" <?php if ( $this->import_settings['translate'] ) {
											echo 'checked';
										} ?>><label
                                                for="wcpr-import-ali-translate"><?php esc_html_e( 'Use translated content from AliExpress if available', 'woocommerce-photo-reviews' ) ?></label>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcpr-import-ali-with-picture"><?php esc_html_e( 'With images', 'woocommerce-photo-reviews' ) ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" name="wcpr_import_ali_with_picture"
                                               class="wcpr-import-ali-with-picture"
                                               id="wcpr-import-ali-with-picture" <?php if ( $this->import_settings['with_picture'] ) {
											echo 'checked';
										} ?>><label
                                                for="wcpr-import-ali-with-picture"><?php esc_html_e( 'Only import reviews with images', 'woocommerce-photo-reviews' ) ?></label>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcpr-import-ali-download-images"><?php esc_html_e( 'Download images', 'woocommerce-photo-reviews' ) ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" name="wcpr_import_ali_download_images"
                                               class="wcpr-import-ali-download-images"
                                               id="wcpr-import-ali-download-images" <?php if ( $this->import_settings['download_images'] ) {
											echo 'checked';
										} ?>><label
                                                for="wcpr-import-ali-download-images"><?php esc_html_e( 'If a review has images, download them to your server', 'woocommerce-photo-reviews' ) ?></label>
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        <label for="wcpr-import-ali-verified"><?php esc_html_e( 'Verified owner', 'woocommerce-photo-reviews' ) ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" name="wcpr_import_ali_verified"
                                               class="wcpr-import-ali-verified"
                                               id="wcpr-import-ali-verified" <?php if ( $this->import_settings['verified'] ) {
											echo 'checked';
										} ?>><label
                                                for="wcpr-import-ali-verified"><?php esc_html_e( 'Mark imported reviews as Verified owner', 'woocommerce-photo-reviews' ) ?></label>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcpr-import-ali-vote"><?php esc_html_e( 'Vote count', 'woocommerce-photo-reviews' ) ?></label>
                                    </th>
                                    <td>
                                        <input type="checkbox" name="wcpr_import_ali_vote"
                                               class="wcpr-import-ali-vote"
                                               id="wcpr-import-ali-vote" <?php if ( $this->import_settings['vote'] ) {
											echo 'checked';
										} ?>>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcpr-import-ali-number"><?php esc_html_e( 'Number of reviews', 'woocommerce-photo-reviews' ) ?></label>
                                    </th>
                                    <td>
                                        <input type="number" min="1" max="100" class="wcpr-import-ali-number"
                                               id="wcpr-import-ali-number" name="wcpr_import_ali_number"
                                               value="<?php echo esc_attr( intval( $this->import_settings['number_of_reviews'] ) ) ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcpr-import-ali-number"><?php esc_html_e( 'Phrases filter', 'woocommerce-photo-reviews' ) ?></label>
                                    </th>
                                    <td>
                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=woocommerce-photo-reviews#/phrases_filter' ) ) ?>"
                                           target="_blank"><?php esc_html_e( 'Change phrases filter settings', 'woocommerce-photo-reviews' ) ?></a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcpr-import-ali-review-status"><?php esc_html_e( 'Set review status', 'woocommerce-photo-reviews' ) ?></label>
                                    </th>
                                    <td>
                                        <select class="wcpr-import-ali-review-status"
                                                id="wcpr-import-ali-review-status">
                                            <option value="1" <?php selected( $this->import_settings['review_status'], '1' ) ?>><?php esc_html_e( 'Approved', 'woocommerce-photo-reviews' ) ?></option>
                                            <option value="0" <?php selected( $this->import_settings['review_status'], '0' ) ?>><?php esc_html_e( 'Pending', 'woocommerce-photo-reviews' ) ?></option>
                                            <option value="spam" <?php selected( $this->import_settings['review_status'], 'spam' ) ?>><?php esc_html_e( 'Spam', 'woocommerce-photo-reviews' ) ?></option>
                                        </select>
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                        </div>
                        <p>
                            <span class="wcpr-import-ali-check button-primary"><?php esc_html_e( 'Import', 'woocommerce-photo-reviews' ) ?></span>
                            <span class="wcpr-import-ali-close-form button"><?php esc_html_e( 'Close', 'woocommerce-photo-reviews' ) ?></span>
                        </p>

                    </div>
                </div>
				<?php
			}
		}
	}

	public function import_reviews_in_product_list( $cols ) {
		$cols['wcpr_import_ali'] = '<span class="button">' . __( 'Import reviews', 'woocommerce-photo-reviews' ) . '</span>';

		return $cols;
	}

	public function column_callback_product( $col ) {
		global $post;
		$post_id = $post->ID;
		if ( $col != 'wcpr_import_ali' ) {
			return;
		}
		$total       = get_comments( array(
			'post_id'    => $post_id,
			'status'     => array( 'approve', 'trash', 'spam' ),
			'meta_query' => array(
				array(
					'key'     => 'id_import_reviews_from_ali',
					'compare' => 'EXISTS'
				)
			),
			'count'      => 1
		) );
		$import_info = array(
			'status'  => '',
			'message' => __( 'Never', 'woocommerce-photo-reviews' ),
			'time'    => '',
			'number'  => ''
		);
		if ( get_post_meta( $post_id, '_wcpr_product_ali_import_info', true ) ) {
			$import_info = get_post_meta( $post_id, '_wcpr_product_ali_import_info', true );
		}
		?>
        <span class="wcpr-import-ali-import-info-total"><?php esc_html_e( 'Total: ', 'woocommerce-photo-reviews' ); ?>
            <span class="wcpr-import-ali-import-info-total-data"><?php echo $total; ?></span></span>
        <span class="wcpr-import-ali-button-popup button"><?php esc_html_e( 'Import', 'woocommerce-photo-reviews' ) ?>
            <input class="wcpr-import-ali-product-sku" type="hidden"
                   value="<?php echo wc_get_product( $post_id )->get_sku(); ?>">
            <input class="wcpr-import-ali-product-id" type="hidden" value="<?php echo $post_id; ?>">
            <input class="wcpr-import-ali-product-ali-url" type="hidden"
                   value="<?php echo( get_post_meta( $post_id, '_wcpr_product_ali_url', true ) ? get_post_meta( $post_id, '_wcpr_product_ali_url', true ) : '' ); ?>"></span>
        <span class="wcpr-import-ali-import-info-status"><?php esc_html_e( 'Status: ', 'woocommerce-photo-reviews' ); ?>
            <span class="wcpr-import-ali-import-info-status-data <?php
            switch ( $import_info['status'] ) {
	            case 'failed':
		            echo 'wcpr-failed';
		            break;
	            case 'successful':
		            echo 'wcpr-successful';
		            break;
	            default:
            }
            ?> "><?php echo isset( $import_info['message'] ) ? $import_info['message'] : esc_html__( 'Never', 'woocommerce-photo-reviews' ) ?></span></span>
        <span class="wcpr-import-ali-import-info-time"><?php esc_html_e( 'Last: ', 'woocommerce-photo-reviews' ); ?>
            <span class="wcpr-import-ali-import-info-time-data"><?php echo ( isset( $import_info['time'] ) && $import_info['time'] ) ? date( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_datetime_format(), $import_info['time'] ) : '' ?></span></span>
        <span class="wcpr-import-ali-import-info-number"><?php esc_html_e( 'Number: ', 'woocommerce-photo-reviews' ); ?>
            <span class="wcpr-import-ali-import-info-number-data"><?php echo isset( $import_info['number'] ) ? absint( $import_info['number'] ) : '' ?></span></span>
		<?php
	}


	public function get_reviews() {
		global $wpdb;
		if ( ! current_user_can( 'manage_options' ) ) {
			die;
		}
		$datetime_format   = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_datetime_format();
		$ali_product_id    = isset( $_POST['product_sku'] ) ? sanitize_text_field( $_POST['product_sku'] ) : '';
		$product_id        = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
		$import_method     = isset( $_POST['import_method'] ) ? sanitize_text_field( $_POST['import_method'] ) : '';
		$countries         = isset( $_POST['countries'] ) ? stripslashes_deep( $_POST['countries'] ) : array();
		$ratings           = isset( $_POST['ratings'] ) ? stripslashes_deep( $_POST['ratings'] ) : array();
		$with_picture      = isset( $_POST['with_picture'] ) ? sanitize_text_field( $_POST['with_picture'] ) : '';
		$translate         = isset( $_POST['translate'] ) ? sanitize_text_field( $_POST['translate'] ) : '';
		$sort              = isset( $_POST['sort'] ) ? sanitize_text_field( $_POST['sort'] ) : '';
		$number_of_reviews = isset( $_POST['number_of_reviews'] ) ? sanitize_text_field( $_POST['number_of_reviews'] ) : '';
		$download_images   = isset( $_POST['download_images'] ) ? sanitize_text_field( $_POST['download_images'] ) : '';
		$verified          = isset( $_POST['verified'] ) ? sanitize_text_field( $_POST['verified'] ) : '';
		$vote              = isset( $_POST['vote'] ) ? sanitize_text_field( $_POST['vote'] ) : '';
		$review_status     = isset( $_POST['review_status'] ) ? sanitize_text_field( $_POST['review_status'] ) : '';

		$import_info = array(
			'status'  => 'failed',
			'message' => __( 'Failed', 'woocommerce-photo-reviews' ),
			'time'    => time(),
			'number'  => 0
		);
		if ( $import_method ) {
			//save import option
			update_option( 'wcpr_import_ali_option', array(
				'import_method'     => $import_method,
				'ratings'           => $ratings,
				'countries'         => $countries,
				'with_picture'      => $with_picture,
				'translate'         => $translate,
				'sort'              => $sort,
				'number_of_reviews' => $number_of_reviews,
				'download_images'   => $download_images,
				'verified'          => $verified,
				'vote'              => $vote,
				'review_status'     => $review_status,
			) );

			if ( $ali_product_id ) {
				$page           = 1;
				$total_reviews  = 0;
				$rating         = $with_picture ? 'image' : 'all';
				$has_review     = false;
				$phrases_filter = $this->settings->get_params( 'phrases_filter' );
				while ( $total_reviews < $number_of_reviews ) {
					$url     = "https://m.aliexpress.com/api/products/{$ali_product_id}/feedbacks?page={$page}&filter=$rating&country=all";
					$request = wp_remote_get(
						$url, array(
							'user-agent' => $this->get_user_agent(),
							'timeout'    => 60,
						)
					);
					if ( ! is_wp_error( $request ) ) {
						$comment_args          = array(
							'post_id'    => $product_id,
							'status'     => array( 0, 1, 'spam' ),
							'meta_query' => array(
								array(
									'key'     => 'id_import_reviews_from_ali',
									'compare' => 'EXISTS'
								)
							),
						);
						$old_imported_comments = get_comments( $comment_args );
						$comment_metas         = array();

						if ( count( $old_imported_comments ) ) {
							foreach ( $old_imported_comments as $old_imported_comment ) {
								$comment_metas[] = get_comment_meta( $old_imported_comment->comment_ID, 'id_import_reviews_from_ali', true );
							}
						}
						$body        = json_decode( $request['body'], true, 512, JSON_BIGINT_AS_STRING );
						$data        = $body['data'];
						$currentPage = $data['currentPage'];
						$pageSize    = $data['pageSize'];
						$totalNum    = $data['totalNum'];
						$totalPage   = $data['totalPage'];
						$evaViewList = $data['evaViewList'];
						if ( is_array( $evaViewList ) && count( $evaViewList ) ) {
							$has_review = true;
							$dispatch   = false;
							foreach ( $evaViewList as $review_data_k => $review_data ) {
								if ( ! in_array( $review_data['evaluationId'], $comment_metas ) ) {
									$comment_rating = intval( $review_data['buyerEval'] / 20 );
									if ( $comment_rating < 1 || $comment_rating > 5 || ! in_array( $comment_rating, $ratings ) || ( count( $countries ) && ! in_array( strtoupper( $review_data['buyerCountry'] ), $countries ) ) ) {
										continue;
									}
									$comment_author  = apply_filters( 'woocommerce_photo_reviews_import_ali_comment_author', $review_data['buyerName'] );
									$comment_content = apply_filters( 'woocommerce_photo_reviews_import_ali_comment_content', ( $translate && ! empty( $review_data['buyerTranslationFeedback'] ) ) ? $review_data['buyerTranslationFeedback'] : ( isset( $review_data['buyerFeedback'] ) ? $review_data['buyerFeedback'] : '' ) );
									if ( isset( $phrases_filter['to_string'] ) && is_array( $phrases_filter['to_string'] ) && $str_replace_count = count( $phrases_filter['to_string'] ) ) {
										for ( $i = 0; $i < $str_replace_count; $i ++ ) {
											if ( $phrases_filter['sensitive'][ $i ] ) {
												$comment_author  = function_exists( 'mb_str_replace' ) ? mb_str_replace( $phrases_filter['from_string'][ $i ], $phrases_filter['to_string'][ $i ], $comment_author ) : str_replace( $phrases_filter['from_string'][ $i ], $phrases_filter['to_string'][ $i ], $comment_author );
												$comment_content = function_exists( 'mb_str_replace' ) ? mb_str_replace( $phrases_filter['from_string'][ $i ], $phrases_filter['to_string'][ $i ], $comment_content ) : str_replace( $phrases_filter['from_string'][ $i ], $phrases_filter['to_string'][ $i ], $comment_content );
											} else {
												$comment_author  = str_ireplace( $phrases_filter['from_string'][ $i ], $phrases_filter['to_string'][ $i ], $comment_author );
												$comment_content = str_ireplace( $phrases_filter['from_string'][ $i ], $phrases_filter['to_string'][ $i ], $comment_content );
											}
										}
									}
									$comment_id = $this->insert_comment( array(
										'comment_post_ID'      => $product_id,
										'comment_author'       => $comment_author,
										'comment_author_email' => '',
										'comment_author_url'   => '',
										'comment_content'      => $comment_content,
										'comment_type'         => 'review',
										'comment_parent'       => 0,
										'user_id'              => '',
										'comment_author_IP'    => '',
										'comment_agent'        => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
										'comment_date'         => date( 'Y-m-d h:i:s', strtotime( $review_data['evalDate'] ) ),
										'comment_date_gmt'     => date( 'Y-m-d h:i:s', strtotime( $review_data['evalDate'] ) ),
										'comment_approved'     => $review_status,
										'comment_meta'         => array( 'rating' => $comment_rating ),
									) );
									if ( $comment_id ) {
										$total_reviews ++;
										if ( $review_data['buyerCountry'] ) {
											update_comment_meta( $comment_id, 'wcpr_review_country', $review_data['buyerCountry'] );
										}
										if ( $verified ) {
											update_comment_meta( $comment_id, 'verified', '1' );
										}
										if ( $vote ) {
											$upVoteCount   = intval( $review_data['upVoteCount'] );
											$downVoteCount = intval( $review_data['downVoteCount'] );
											if ( $upVoteCount > 0 ) {
												update_comment_meta( $comment_id, 'wcpr_vote_up_count', $upVoteCount );
											}
											if ( $downVoteCount > 0 ) {
												update_comment_meta( $comment_id, 'wcpr_vote_down_count', $downVoteCount );
											}
										}
										update_comment_meta( $comment_id, 'id_import_reviews_from_ali', $review_data['evaluationId'] );
										$review_images = array();
										if ( isset( $review_data['images'] ) && is_array( $review_data['images'] ) && count( $review_data['images'] ) ) {
											$review_images = $review_data['images'];
										}
										if ( isset( $review_data['buyerAddFbImages'] ) && is_array( $review_data['buyerAddFbImages'] ) && count( $review_data['buyerAddFbImages'] ) ) {
											$review_images = array_merge( $review_data['buyerAddFbImages'], $review_images );
										}
										if ( count( $review_images ) ) {
											update_comment_meta( $comment_id, 'reviews-images', $review_images );
											if ( $download_images ) {
												$dispatch = true;
												$images   = array( 'comment_id' => $comment_id );
												VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Import_Csv::$background_process->push_to_queue( $images );
											}
										}
										if($review_status==1) {
											VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Import_Csv::update_product_reviews_and_rating( $product_id, $comment_rating );
										}
										if ( $total_reviews >= $number_of_reviews ) {
											break;
										}
									}
								}
							}
							if ( $dispatch ) {
								VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Import_Csv::$background_process->save()->dispatch();
							}
						}

						if ( $page >= $totalPage || $total_reviews >= $number_of_reviews ) {
							break;
						}
						$page ++;
					} else {
						update_post_meta( $product_id, '_wcpr_product_ali_import_info', $import_info );
						$import_info['time']    = date( $datetime_format, $import_info['time'] );
						$import_info['message'] = $request->get_error_message();
						wp_send_json( $import_info );
					}
				}
				if ( $total_reviews > 0 ) {
					$import_info['status']  = 'successful';
					$import_info['message'] = __( 'Successful', 'woocommerce-photo-reviews' );
					$import_info['time']    = time();
					$import_info['number']  = $total_reviews;
					update_post_meta( $product_id, '_wcpr_product_ali_import_info', $import_info );
					$import_info['time'] = date( $datetime_format, $import_info['time'] );
					wp_send_json( $import_info );
				} else {
					update_post_meta( $product_id, '_wcpr_product_ali_import_info', $import_info );
					$import_info['time'] = date( $datetime_format, $import_info['time'] );
					if ( $has_review ) {
						$import_info['message'] = __( 'No more reviews to import', 'woocommerce-photo-reviews' );
					}
					wp_send_json( $import_info );
				}

			} else {
				update_post_meta( $product_id, '_wcpr_product_ali_import_info', $import_info );
				$import_info['time'] = date( $datetime_format, $import_info['time'] );
				wp_send_json( $import_info );
			}
		}

		update_post_meta( $product_id, '_wcpr_product_ali_import_info', $import_info );
		$import_info['time'] = date( $datetime_format, $import_info['time'] );
		wp_send_json( $import_info );
	}

	public function download_images() {
		$datetime_format = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_datetime_format();
		$import_info     = array(
			'status'  => 'failed',
			'message' => __( 'Failed', 'woocommerce-photo-reviews' ),
			'time'    => time(),
			'number'  => 0
		);
		$imported        = isset( $_POST['imported'] ) ? $_POST['imported'] : 0;
		if ( isset( $_POST['reviews_with_images'] ) ) {
			$allreviews  = isset( $_POST['reviews_with_images'] ) ? array_map( 'stripslashes', $_POST['reviews_with_images'] ) : array();
			$product_ids = isset( $_POST['product_ids'] ) ? array_map( 'stripslashes', $_POST['product_ids'] ) : array();
			if ( is_array( $allreviews ) && sizeof( $allreviews ) ) {
				$reviews_images = array();
				$curr           = array_pop( $allreviews );
				$product_id     = array_pop( $product_ids );
				foreach ( get_comment_meta( $curr, 'reviews-images', true ) as $image_url ) {
//			         Need to require these files
					add_filter( 'big_image_size_threshold', '__return_false' );
					if ( ! function_exists( 'media_handle_upload' ) ) {
						require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
						require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
						require_once( ABSPATH . "wp-admin" . '/includes/media.php' );
					}

					$tmp = download_url( $image_url );
					if ( is_wp_error( $tmp ) ) {
						$reviews_images[] = $image_url;
						continue;
					}

					$desc       = "WCPR reviews images";
					$file_array = array();

					// Set variables for storage
					// fix file filename for query strings
					preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $image_url, $matches );
					$file_array['name']     = basename( $matches[0] );
					$file_array['tmp_name'] = $tmp;

					// If error storing temporarily, unlink
					if ( is_wp_error( $tmp ) ) {
						@unlink( $file_array['tmp_name'] );
						$file_array['tmp_name'] = '';
						$reviews_images[]       = $image_url;
						continue;
					}

					// do the validation and storage stuff
					$id = media_handle_sideload( $file_array, $product_id, $desc );

					// If error storing permanently, unlink
					if ( is_wp_error( $id ) ) {
						@unlink( $file_array['tmp_name'] );

						$reviews_images[] = $image_url;
						continue;
					}
					$reviews_images[] = $id;
				}
				update_comment_meta( $curr, 'reviews-images', $reviews_images );
				$imported ++;
				if ( ! sizeof( $allreviews ) ) {
					$import_info['product_ids'] = $product_ids;
					$import_info['status']      = 'downloaded';
					$import_info['message']     = __( 'Downloaded', 'woocommerce-photo-reviews' );
					$import_info['number']      = $imported;
					$import_info['reviews']     = $allreviews;
					$import_info['time']        = time();
					$import_info['time']        = date( $datetime_format, $import_info['time'] );
					$import_info['id']          = $product_id;
					wp_send_json( $import_info );
				} else {
					$import_info['product_ids'] = $product_ids;
					$import_info['status']      = 'downloading';
					$import_info['message']     = __( 'Downloading', 'woocommerce-photo-reviews' );
					$import_info['number']      = $imported;
					$import_info['reviews']     = $allreviews;
					$import_info['time']        = time();
					$import_info['time']        = date( $datetime_format, $import_info['time'] );
					wp_send_json( $import_info );
				}
			}
		} else {
			die;
		}
	}

	public static function get_countries() {
		return array(
			"AF"  => "Afghanistan",
			"ALA" => "Aland Islands",
			"AL"  => "Albania",
			"GBA" => "Alderney",
			"DZ"  => "Algeria",
			"AS"  => "American Samoa",
			"AD"  => "Andorra",
			"AO"  => "Angola",
			"AI"  => "Anguilla",
			"AG"  => "Antigua and Barbuda",
			"AR"  => "Argentina",
			"AM"  => "Armenia",
			"AW"  => "Aruba",
			"ASC" => "Ascension Island",
			"AU"  => "Australia",
			"AT"  => "Austria",
			"AZ"  => "Azerbaijan",
			"BH"  => "Bahrain",
			"GGY" => "Guernsey",
			"BD"  => "Bangladesh",
			"BB"  => "Barbados",
			"BY"  => "Belarus",
			"BE"  => "Belgium",
			"BZ"  => "Belize",
			"BJ"  => "Benin",
			"BM"  => "Bermuda",
			"BT"  => "Bhutan",
			"BO"  => "Bolivia",
			"BA"  => "Bosnia and Herzegovina",
			"BW"  => "Botswana",
			"BR"  => "Brazil",
			"VG"  => "Virgin Islands (British)",
			"BG"  => "Bulgaria",
			"BF"  => "Burkina Faso",
			"BI"  => "Burundi",
			"KH"  => "Cambodia",
			"CM"  => "Cameroon",
			"CA"  => "Canada",
			"CV"  => "Cape Verde",
			"BQ"  => "Caribbean Netherlands",
			"KY"  => "Cayman Islands",
			"CF"  => "Central African Republic",
			"TD"  => "Chad",
			"CL"  => "Chile",
			"CX"  => "Christmas Island",
			"CC"  => "Cocos (Keeling) Islands",
			"CO"  => "Colombia",
			"KM"  => "Comoros",
			"CK"  => "Cook Islands",
			"CR"  => "Costa Rica",
			"CI"  => "Cote D'Ivoire",
			"HR"  => "Croatia (local name: Hrvatska)",
			"CW"  => "Curacao",
			"CY"  => "Cyprus",
			"CZ"  => "Czech Republic",
			"DK"  => "Denmark",
			"DJ"  => "Djibouti",
			"DM"  => "Dominica",
			"DO"  => "Dominican Republic",
			"EC"  => "Ecuador",
			"EG"  => "Egypt",
			"SV"  => "El Salvador",
			"GQ"  => "Equatorial Guinea",
			"ER"  => "Eritrea",
			"EE"  => "Estonia",
			"ET"  => "Ethiopia",
			"FK"  => "Falkland Islands (Malvinas)",
			"FO"  => "Faroe Islands",
			"FJ"  => "Fiji",
			"FI"  => "Finland",
			"FR"  => "France",
			"PF"  => "French Polynesia",
			"GA"  => "Gabon",
			"GM"  => "Gambia",
			"GE"  => "Georgia",
			"DE"  => "Germany",
			"GH"  => "Ghana",
			"GI"  => "Gibraltar",
			"GR"  => "Greece",
			"GL"  => "Greenland",
			"GD"  => "Grenada",
			"GP"  => "Guadeloupe",
			"GU"  => "Guam",
			"GT"  => "Guatemala",
			"GN"  => "Guinea",
			"GW"  => "Guinea-Bissau",
			"GY"  => "Guyana",
			"GF"  => "French Guiana",
			"HT"  => "Haiti",
			"HN"  => "Honduras",
			"HK"  => "Hong Kong,China",
			"HU"  => "Hungary",
			"IS"  => "Iceland",
			"IN"  => "India",
			"ID"  => "Indonesia",
			"IQ"  => "Iraq",
			"IE"  => "Ireland",
			"IL"  => "Israel",
			"IT"  => "Italy",
			"JM"  => "Jamaica",
			"JP"  => "Japan",
			"JEY" => "Jersey",
			"JO"  => "Jordan",
			"KZ"  => "Kazakhstan",
			"KE"  => "Kenya",
			"KI"  => "Kiribati",
			"KR"  => "Korea",
			"KS"  => "Kosovo",
			"KW"  => "Kuwait",
			"KG"  => "Kyrgyzstan",
			"LA"  => "Lao People's Democratic Republic",
			"LV"  => "Latvia",
			"LB"  => "Lebanon",
			"LS"  => "Lesotho",
			"LR"  => "Liberia",
			"LY"  => "Libya",
			"LI"  => "Liechtenstein",
			"LT"  => "Lithuania",
			"LU"  => "Luxembourg",
			"MO"  => "Macau,China",
			"MK"  => "Macedonia",
			"MG"  => "Madagascar",
			"MW"  => "Malawi",
			"MY"  => "Malaysia",
			"MV"  => "Maldives",
			"ML"  => "Mali",
			"MT"  => "Malta",
			"MQ"  => "Martinique",
			"MR"  => "Mauritania",
			"MU"  => "Mauritius",
			"YT"  => "Mayotte",
			"MX"  => "Mexico",
			"FM"  => "Micronesia",
			"MC"  => "Monaco",
			"MN"  => "Mongolia",
			"MNE" => "Montenegro",
			"MS"  => "Montserrat",
			"MA"  => "Morocco",
			"MZ"  => "Mozambique",
			"MM"  => "Myanmar",
			"NA"  => "Namibia",
			"NR"  => "Nauru",
			"BN"  => "Negara Brunei Darussalam",
			"NP"  => "Nepal",
			"NL"  => "Netherlands",
			"AN"  => "Netherlands Antilles",
			"NC"  => "New Caledonia",
			"NZ"  => "New Zealand",
			"NI"  => "Nicaragua",
			"NE"  => "Niger",
			"NG"  => "Nigeria",
			"NU"  => "Niue",
			"NF"  => "Norfolk Island",
			"MP"  => "Northern Mariana Islands",
			"NO"  => "Norway",
			"OM"  => "Oman",
			"PK"  => "Pakistan",
			"PW"  => "Palau",
			"PS"  => "Palestine",
			"PA"  => "Panama",
			"PG"  => "Papua New Guinea",
			"PY"  => "Paraguay",
			"PE"  => "Peru",
			"PH"  => "Philippines",
			"PL"  => "Poland",
			"PT"  => "Portugal",
			"PR"  => "Puerto Rico",
			"QA"  => "Qatar",
			"MD"  => "Moldova",
			"RE"  => "Reunion",
			"RO"  => "Romania",
			"RU"  => "Russian Federation",
			"RW"  => "Rwanda",
			"BLM" => "Saint Barthelemy",
			"KN"  => "Saint Kitts and Nevis",
			"LC"  => "Saint Lucia",
			"MAF" => "Saint Martin",
			"PM"  => "St. Pierre and Miquelon",
			"VC"  => "Saint Vincent and the Grenadines",
			"WS"  => "Samoa",
			"SM"  => "San Marino",
			"ST"  => "Sao Tome and Principe",
			"SA"  => "Saudi Arabia",
			"SN"  => "Senegal",
			"SRB" => "Serbia",
			"SC"  => "Seychelles",
			"SL"  => "Sierra Leone",
			"SG"  => "Singapore",
			"SX"  => "Sint Maarten (Netherlands)",
			"SK"  => "Slovakia (Slovak Republic)",
			"SI"  => "Slovenia",
			"SB"  => "Solomon Islands",
			"SO"  => "Somalia",
			"ZA"  => "South Africa",
			"SGS" => "South Georgia and the South Sandwich Islands",
			"SS"  => "South Sudan",
			"ES"  => "Spain",
			"LK"  => "Sri Lanka",
			"SR"  => "Suriname",
			"SZ"  => "Swaziland",
			"SE"  => "Sweden",
			"CH"  => "Switzerland",
			"TW"  => "Taiwan,China",
			"TJ"  => "Tajikistan",
			"TH"  => "Thailand",
			"BS"  => "Bahamas",
			"ZR"  => "Congo, The Democratic Republic Of The",
			"CG"  => "Congo, The Republic of Congo",
			"MH"  => "Marshall Islands",
			"VA"  => "Vatican City State (Holy See)",
			"TLS" => "Timor-Leste",
			"TG"  => "Togo",
			"TO"  => "Tonga",
			"TT"  => "Trinidad and Tobago",
			"TN"  => "Tunisia",
			"TR"  => "Turkey",
			"TM"  => "Turkmenistan",
			"TC"  => "Turks and Caicos Islands",
			"TV"  => "Tuvalu",
			"VI"  => "Virgin Islands (U.S.)",
			"UG"  => "Uganda",
			"UA"  => "Ukraine",
			"AE"  => "United Arab Emirates",
			"UK"  => "United Kingdom",
			"TZ"  => "Tanzania",
			"US"  => "United States",
			"UY"  => "Uruguay",
			"UZ"  => "Uzbekistan",
			"VU"  => "Vanuatu",
			"VE"  => "Venezuela",
			"VN"  => "Vietnam",
			"WF"  => "Wallis And Futuna Islands",
			"YE"  => "Yemen",
			"ZM"  => "Zambia",
			"EAZ" => "Zanzibar",
			"ZW"  => "Zimbabwe"
		);
	}

	public function get_user_agent() {
		$user_agent_list = get_option( 'vi_wcpr_user_agent_list' );
		if ( ! $user_agent_list ) {
			$user_agent_list = '["Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36","Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.100 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko\/20100101 Firefox\/67.0","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit\/605.1.15 (KHTML, like Gecko) Version\/12.1.1 Safari\/605.1.15","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.80 Safari\/537.36","Mozilla\/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 Safari\/537.36","Mozilla\/5.0 (X11; Ubuntu; Linux x86_64; rv:67.0) Gecko\/20100101 Firefox\/67.0","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10.14; rv:67.0) Gecko\/20100101 Firefox\/67.0","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.100 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; WOW64) AppleWebKit\/537.36 (KHTML, like Gecko) HeadlessChrome\/60.0.3112.78 Safari\/537.36","Mozilla\/5.0 (Windows NT 6.1; rv:60.0) Gecko\/20100101 Firefox\/60.0","Mozilla\/5.0 (Windows NT 6.1; Win64; x64; rv:67.0) Gecko\/20100101 Firefox\/67.0","Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.90 Safari\/537.36","Mozilla\/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.100 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/64.0.3282.140 Safari\/537.36 Edge\/17.17134","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 Safari\/537.36","Mozilla\/5.0 (X11; Linux x86_64; rv:67.0) Gecko\/20100101 Firefox\/67.0","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.131 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/64.0.3282.140 Safari\/537.36 Edge\/18.17763","Mozilla\/5.0 (X11; Linux x86_64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.80 Safari\/537.36","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit\/605.1.15 (KHTML, like Gecko) Version\/12.1 Safari\/605.1.15","Mozilla\/5.0 (Windows NT 10.0; WOW64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 Safari\/537.36","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit\/605.1.15 (KHTML, like Gecko) Version\/12.1.1 Safari\/605.1.15","Mozilla\/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 Safari\/537.36","Mozilla\/5.0 (X11; Linux x86_64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.100 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; WOW64; Trident\/7.0; rv:11.0) like Gecko","Mozilla\/5.0 (X11; Linux x86_64; rv:60.0) Gecko\/20100101 Firefox\/60.0","Mozilla\/5.0 (X11; Linux x86_64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/73.0.3683.103 Safari\/537.36 OPR\/60.0.3255.151","Mozilla\/5.0 (Windows NT 6.1; WOW64; Trident\/7.0; rv:11.0) like Gecko","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.80 Safari\/537.36","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 Safari\/537.36","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10.13; rv:67.0) Gecko\/20100101 Firefox\/67.0","Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/73.0.3683.103 Safari\/537.36","Mozilla\/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.80 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/62.0.3202.94 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.157 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; Win64; x64; rv:66.0) Gecko\/20100101 Firefox\/66.0","Mozilla\/5.0 (Windows NT 10.0; Win64; x64; rv:68.0) Gecko\/20100101 Firefox\/68.0","Mozilla\/5.0 (X11; Linux x86_64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/72.0.3626.109 Safari\/537.36","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 Safari\/537.36","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.90 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/73.0.3683.103 Safari\/537.36 OPR\/60.0.3255.109","Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/73.0.3683.103 Safari\/537.36 OPR\/60.0.3255.170","Mozilla\/5.0 (Windows NT 6.3; Win64; x64; rv:67.0) Gecko\/20100101 Firefox\/67.0","Mozilla\/5.0 (Windows NT 10.0; WOW64; rv:67.0) Gecko\/20100101 Firefox\/67.0","Mozilla\/5.0 (iPad; CPU OS 12_3_1 like Mac OS X) AppleWebKit\/605.1.15 (KHTML, like Gecko) Version\/12.1.1 Mobile\/15E148 Safari\/604.1","Mozilla\/5.0 (Windows NT 6.1; WOW64) AppleWebKit\/537.36 (KHTML, like Gecko) HeadlessChrome\/60.0.3112.78 Safari\/537.36","Mozilla\/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.100 Safari\/537.36","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.100 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; WOW64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 YaBrowser\/19.6.1.153 Yowser\/2.5 Safari\/537.36","Mozilla\/5.0 (X11; Linux x86_64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/70.0.3538.77 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; WOW64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/73.0.3683.103 YaBrowser\/19.4.3.370 Yowser\/2.5 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; WOW64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 YaBrowser\/19.6.0.1574 Yowser\/2.5 Safari\/537.36","Mozilla\/5.0 (X11; Linux x86_64) AppleWebKit\/537.36 (KHTML, like Gecko) Ubuntu Chromium\/74.0.3729.169 Chrome\/74.0.3729.169 Safari\/537.36","Mozilla\/5.0 (Windows NT 6.1) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 Safari\/537.36","Mozilla\/5.0 (X11; Linux x86_64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.131 Safari\/537.36","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_14) AppleWebKit\/605.1.15 (KHTML, like Gecko) Version\/12.0 Safari\/605.1.15","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 Safari\/537.36","Mozilla\/5.0 (X11; Linux x86_64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/73.0.3683.86 Safari\/537.36","Mozilla\/5.0 (Linux; U; Android 4.3; en-us; SM-N900T Build\/JSS15J) AppleWebKit\/534.30 (KHTML, like Gecko) Version\/4.0 Mobile Safari\/534.30","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit\/605.1.15 (KHTML, like Gecko) Version\/12.0.3 Safari\/605.1.15","Mozilla\/5.0 (Windows NT 6.1) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.100 Safari\/537.36","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 Safari\/537.36","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit\/605.1.15 (KHTML, like Gecko) Version\/11.1.2 Safari\/605.1.15","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.80 Safari\/537.36","Mozilla\/5.0 (Windows NT 6.1; WOW64; rv:67.0) Gecko\/20100101 Firefox\/67.0","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_14_2) AppleWebKit\/605.1.15 (KHTML, like Gecko) Version\/12.0.2 Safari\/605.1.15","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_14_4) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.100 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; WOW64; rv:45.0) Gecko\/20100101 Firefox\/45.0","Mozilla\/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.90 Safari\/537.36","Mozilla\/5.0 (X11; Linux x86_64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.157 Safari\/537.36","Mozilla\/5.0 (X11; Linux x86_64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.90 Safari\/537.36","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_14_2) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.169 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/72.0.3626.121 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/73.0.3683.86 Safari\/537.36","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/75.0.3770.100 Safari\/537.36","Mozilla\/5.0 (Windows NT 10.0; Win64; x64; rv:60.0) Gecko\/20100101 Firefox\/60.0","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10.12; rv:67.0) Gecko\/20100101 Firefox\/67.0","Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit\/605.1.15 (KHTML, like Gecko) Version\/13.0 Safari\/605.1.15","Mozilla\/5.0 (Windows NT 6.1; rv:67.0) Gecko\/20100101 Firefox\/67.0","Mozilla\/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/73.0.3683.103 Safari\/537.36 OPR\/60.0.3255.151","Mozilla\/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/73.0.3683.103 Safari\/537.36 OPR\/60.0.3255.170","Mozilla\/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/74.0.3729.131 Safari\/537.36","Mozilla\/5.0 (Windows NT 6.1; WOW64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/73.0.3683.103 YaBrowser\/19.4.3.370 Yowser\/2.5 Safari\/537.36","Mozilla\/5.0 (Windows NT 6.1; WOW64; rv:56.0) Gecko\/20100101 Firefox\/56.0","Mozilla\/5.0 (Windows NT 6.1; WOW64; rv:56.0) Gecko\/20100101 Firefox\/56.0"]';
			update_option( 'vi_wcpr_user_agent_list', $user_agent_list );
		}
		$user_agent_list = json_decode( $user_agent_list, true );
		$return_agent    = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36';
		$last_used       = get_option( 'vi_wcpr_last_used_user_agent', 0 );
		if ( $last_used == count( $user_agent_list ) - 1 ) {
			$last_used = 0;
			shuffle( $user_agent_list );
			update_option( 'vi_wcpr_user_agent_list', json_encode( $user_agent_list ) );
		} else {
			$last_used ++;
		}
		update_option( 'vi_wcpr_last_used_user_agent', $last_used );
		if ( isset( $user_agent_list[ $last_used ] ) && $user_agent_list[ $last_used ] ) {
			$return_agent = $user_agent_list[ $last_used ];
		}

		return $return_agent;
	}

	public static function insert_comment( $commentdata ) {
		global $wpdb;
		$comment_ID = wp_insert_comment( $commentdata );
		if ( ! $comment_ID ) {
			$fields = array( 'comment_author', 'comment_content' );

			foreach ( $fields as $field ) {
				if ( isset( $commentdata[ $field ] ) ) {
					$commentdata[ $field ] = $wpdb->strip_invalid_text_for_column( $wpdb->comments, $field, $commentdata[ $field ] );
				}
			}

			$comment_ID = wp_insert_comment( $commentdata );
			if ( ! $comment_ID ) {
				return false;
			}
		}

		return $comment_ID;
	}

}
