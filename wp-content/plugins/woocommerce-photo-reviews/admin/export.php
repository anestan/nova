<?php

/**
 * Class VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Export
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Export {
	protected $settings;

	public function __construct() {
		$this->settings = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
		add_action( 'admin_menu', array( $this, 'add_menu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_init', array( $this, 'export' ) );
	}

	public function admin_enqueue_scripts() {
		global $pagenow;
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
		if ( $pagenow === 'admin.php' && $page === 'wcpr_export_reviews' ) {
			global $wp_scripts;
			$scripts = $wp_scripts->registered;
			foreach ( $scripts as $k => $script ) {
				preg_match( '/select2/i', $k, $result );
				if ( count( array_filter( $result ) ) ) {
					unset( $wp_scripts->registered[ $k ] );
					wp_dequeue_script( $script->handle );
				}
				preg_match( '/bootstrap/i', $k, $result );
				if ( count( array_filter( $result ) ) ) {
					unset( $wp_scripts->registered[ $k ] );
					wp_dequeue_script( $script->handle );
				}
			}
			wp_enqueue_script( 'wcpr-semantic-js-form', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'form.min.js', array( 'jquery' ) );
			wp_enqueue_style( 'wcpr-semantic-css-form', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'form.min.css' );
			wp_enqueue_script( 'wcpr-semantic-js-checkbox', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'checkbox.min.js', array( 'jquery' ) );
			wp_enqueue_style( 'wcpr-semantic-css-checkbox', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'checkbox.min.css' );
			wp_enqueue_script( 'wcpr-semantic-js-tab', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'tab.js', array( 'jquery' ) );
			wp_enqueue_style( 'wcpr-semantic-css-tab', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'tab.min.css' );
			wp_enqueue_style( 'wcpr-semantic-css-input', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'input.min.css' );
			wp_enqueue_style( 'wcpr-semantic-css-table', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'table.min.css' );
			wp_enqueue_style( 'wcpr-semantic-css-segment', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'segment.min.css' );
			wp_enqueue_style( 'wcpr-semantic-css-label', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'label.min.css' );
			wp_enqueue_style( 'wcpr-semantic-css-menu', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'menu.min.css' );
			wp_enqueue_style( 'wcpr-semantic-css-button', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'button.min.css' );
			wp_enqueue_style( 'wcpr-semantic-css-dropdown', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'dropdown.min.css' );
			wp_enqueue_style( 'wcpr-transition-css', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'transition.min.css' );
			wp_enqueue_style( 'wcpr-semantic-message-css', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'message.min.css' );
			wp_enqueue_style( 'wcpr-semantic-icon-css', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'icon.min.css' );
			wp_enqueue_script( 'wcpr-semantic-dropdown-js', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'dropdown.js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_enqueue_style( 'wcpr-verified-badge-icon', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'woocommerce-photo-reviews-badge.min.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_enqueue_script( 'wcpr_admin_select2_script', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'select2.js', array( 'jquery' ) );
			wp_enqueue_style( 'wcpr_admin_seletct2', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'select2.min.css' );
			/*Color picker*/
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script(
				'iris', admin_url( 'js/iris.min.js' ), array(
				'jquery-ui-draggable',
				'jquery-ui-slider',
				'jquery-touch-punch'
			), false, 1 );
			wp_enqueue_style( 'wcpr-transition-css', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'transition.min.css' );
			wp_enqueue_script( 'wcpr-transition', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'transition.min.js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_enqueue_script( 'woocommerce-photo-reviews-export', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'export.js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_enqueue_style( 'woocommerce-photo-reviews-export', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'export.css', '', VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		}
	}

	public function add_menu() {
		add_submenu_page(
			'woocommerce-photo-reviews', __( 'Export Reviews', 'woocommerce-photo-reviews' ), __( 'Export Reviews', 'woocommerce-photo-reviews' ), 'manage_options', 'wcpr_export_reviews', array(
				$this,
				'export_reviews_callback'
			)
		);
	}

	public function export_reviews_callback() {
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'Export Reviews', 'woocommerce-photo-reviews' ); ?></h2>
            <div class="vi-ui segment">
                <form class="vi-ui form" method="post">
					<?php
					wp_nonce_field( '_woocommerce_photo_reviews_action_nonce', '_woocommerce_photo_reviews_nonce' );
					?>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th>
                                <label for="start_date"><?php esc_html_e( 'Start date', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <input type="date" id="start_date" name="start_date">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="end_date"><?php esc_html_e( 'End date', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <input type="date" id="end_date" name="end_date">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="products"><?php esc_html_e( 'Products', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <select id="products" name="products[]" class="wcpr-product-search" multiple>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="categories"><?php esc_html_e( 'Categories', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <select id="categories" name="categories[]" class="wcpr-category-search" multiple>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="ratings"><?php esc_html_e( 'Ratings', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <select name="ratings[]" id="ratings" class="vi-ui fluid dropdown" multiple>
                                    <option value="1"
                                            selected><?php esc_html_e( '1 star', 'woocommerce-photo-reviews' ) ?></option>
                                    <option value="2"
                                            selected><?php esc_html_e( '2 stars', 'woocommerce-photo-reviews' ) ?></option>
                                    <option value="3"
                                            selected><?php esc_html_e( '3 stars', 'woocommerce-photo-reviews' ) ?></option>
                                    <option value="4"
                                            selected><?php esc_html_e( '4 stars', 'woocommerce-photo-reviews' ) ?></option>
                                    <option value="5"
                                            selected><?php esc_html_e( '5 stars', 'woocommerce-photo-reviews' ) ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="comment_status"><?php esc_html_e( 'Comment status', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <select name="comment_status[]" id="comment_status" class="vi-ui fluid dropdown"
                                        multiple>
                                    <option value="approve"
                                            selected><?php esc_html_e( 'Approved', 'woocommerce-photo-reviews' ) ?></option>
                                    <option value="hold"
                                            selected><?php esc_html_e( 'Hold', 'woocommerce-photo-reviews' ) ?></option>
                                    <option value="spam"><?php esc_html_e( 'Spam', 'woocommerce-photo-reviews' ) ?></option>
                                    <option value="trash"><?php esc_html_e( 'Trash', 'woocommerce-photo-reviews' ) ?></option>
                                </select>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <input type="submit" class="vi-ui button primary" name="wcpr_export"
                           value="<?php esc_html_e( 'Export', 'woocommerce-photo-reviews' ); ?>">
                </form>
            </div>
        </div>
		<?php
	}

	public function export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_POST['wcpr_export'] ) ) {
			return;
		}
		if ( isset( $_POST['_woocommerce_photo_reviews_nonce'] ) && wp_verify_nonce( $_POST['_woocommerce_photo_reviews_nonce'], '_woocommerce_photo_reviews_action_nonce' ) ) {
			$filename       = 'woocommerce_photo_reviews_export_';
			$start          = sanitize_text_field( $_POST['start_date'] );
			$end            = sanitize_text_field( $_POST['end_date'] );
			$products       = isset( $_POST['products'] ) ? stripslashes_deep( $_POST['products'] ) : array();
			$categories     = isset( $_POST['categories'] ) ? stripslashes_deep( $_POST['categories'] ) : array();
			$ratings        = isset( $_POST['ratings'] ) ? stripslashes_deep( $_POST['ratings'] ) : array();
			$comment_status = isset( $_POST['comment_status'] ) ? stripslashes_deep( $_POST['comment_status'] ) : array();
			$args           = array(
				'status'      => $comment_status,
				'post_type'   => 'product',
				'post_status' => 'publish',
				'number'      => 0,
				'count'       => false,
			);

			if ( is_array( $categories ) && count( $categories ) ) {
				$the_query = new WP_Query(
					array(
						'post_type'      => 'product',
						'post_status'    => VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::search_product_statuses(),
						'posts_per_page' => - 1,
						'tax_query'      => array(
							'relation' => 'AND',
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'ID',
								'terms'    => $categories,
								'operator' => 'IN'
							)
						)
					)
				);
				if ( $the_query->have_posts() ) {
					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						$products[] = get_the_ID();
					}
				}
				wp_reset_postdata();
			}

			$products = array_unique( $products );
			if ( count( $products ) ) {
				$args['post__in'] = $products;
			}
			if ( count( $ratings ) ) {
				$args['meta_query'] = array(
					'relation' => 'AND',
					array(
						'key'     => 'rating',
						'value'   => $ratings,
						'compare' => 'IN'
					)
				);
			} else {
				$args['meta_query'] = array(
					'relation' => 'AND',
					array(
						'key'     => 'rating',
						'compare' => 'EXISTS'
					)
				);
			}
			if ( ! $start && ! $end ) {
				$filename .= date( 'Y-m-d_H-i-s', time() ) . ".csv";
			} elseif ( ! $start ) {
				$args['date_query'] = array(
					array(
						'before'    => $end,
						'inclusive' => true
					)
				);
				$filename           .= 'before_' . $end . ".csv";
			} elseif ( ! $end ) {
				$args['date_query'] = array(
					array(
						'after'     => $start,
						'inclusive' => true
					)
				);

				$filename .= 'from_' . $start . '_to_' . date( 'Y-m-d' ) . ".csv";
			} else {
				if ( strtotime( $start ) > strtotime( $end ) ) {
					wp_die( 'Incorrect input date' );
				}

				$args['date_query'] = array(
					array(
						'before'    => $end,
						'after'     => $start,
						'inclusive' => true
					)
				);

				$filename .= 'from_' . $start . '_to_' . $end . ".csv";
			}
			$comments = get_comments( $args );
			if ( count( $comments ) ) {
				$data_rows  = array();
				$header_row = array(
					'comment_ID'           => esc_html__( 'Comment ID', 'woocommerce-photo-reviews' ),
					'comment_post_ID'      => esc_html__( 'Product ID', 'woocommerce-photo-reviews' ),
					'comment_author'       => esc_html__( 'Author name', 'woocommerce-photo-reviews' ),
					'comment_author_email' => esc_html__( 'Author email', 'woocommerce-photo-reviews' ),
					'comment_author_url'   => esc_html__( 'Author URL', 'woocommerce-photo-reviews' ),
					'comment_content'      => esc_html__( 'Content', 'woocommerce-photo-reviews' ),
					'comment_approved'     => esc_html__( 'Comment status', 'woocommerce-photo-reviews' ),
					'rating'               => esc_html__( 'Rating', 'woocommerce-photo-reviews' ),
					'verified'             => esc_html__( 'Verified', 'woocommerce-photo-reviews' ),
					'reviews-images'       => esc_html__( 'Photos', 'woocommerce-photo-reviews' ),
					'wcpr_custom_fields'   => esc_html__( 'Optional fields/Variations', 'woocommerce-photo-reviews' ),
					'wcpr_review_title'    => esc_html__( 'Review title', 'woocommerce-photo-reviews' ),
					'wcpr_vote_up'         => esc_html__( 'Up-vote count', 'woocommerce-photo-reviews' ),
					'wcpr_vote_down'       => esc_html__( 'Down-vote count', 'woocommerce-photo-reviews' ),
					'comment_parent'       => esc_html__( 'Comment parent', 'woocommerce-photo-reviews' ),
					'user_id'              => esc_html__( 'User id', 'woocommerce-photo-reviews' ),
					'comment_author_IP'    => esc_html__( 'Author IP', 'woocommerce-photo-reviews' ),
					'comment_agent'        => esc_html__( 'Comment agent', 'woocommerce-photo-reviews' ),
					'comment_date'         => esc_html__( 'Comment date', 'woocommerce-photo-reviews' ),
					'comment_date_gmt'     => esc_html__( 'Comment date gmt', 'woocommerce-photo-reviews' ),
				);
				foreach ( $comments as $comment ) {
					$data_row = array();
					foreach ( $header_row as $item => $item_v ) {
						if ( in_array( $item, array(
							'rating',
							'reviews-images',
							'verified',
							'wcpr_custom_fields',
							'wcpr_review_title',
						) ) ) {
							$meta = get_comment_meta( $comment->comment_ID, $item, true );
							switch ( $item ) {
								case 'reviews-images':
									if ( is_array( $meta ) && count( $meta ) ) {
										$reviews_images = array();
										foreach ( $meta as $reviews_image ) {
											if ( wc_is_valid_url( $reviews_image ) ) {
												$reviews_images[] = $reviews_image;
											} else {
												$image_url = wp_get_attachment_url( $reviews_image );
												if ( $image_url ) {
													$reviews_images[] = $image_url;
												}
											}
										}
										$data_row[] = implode( ',', $reviews_images );
									} else {
										$data_row[] = '';
									}
									break;
								case 'wcpr_custom_fields':
									if ( is_array( $meta ) && count( $meta ) ) {
										$optional_fields = array();
										foreach ( $meta as $meta_k => $meta_v ) {
											$optional_fields[] = implode( ':', $meta_v );
										}
										$data_row[] = implode( '/', $optional_fields );
									} else {
										$data_row[] = '';
									}
									break;
								default:
									$data_row[] = $meta;
							}
						} elseif ( in_array( $item, array(
							'wcpr_vote_up',
							'wcpr_vote_down',
						) ) ) {
							$meta       = get_comment_meta( $comment->comment_ID, $item, false );
							$data_row[] = count( $meta ) + absint( get_comment_meta( $comment->comment_ID, "{$item}_count", true ) );
						} else {
							$data_row[] = $comment->{$item};
						}
					}
					$data_rows[] = $data_row;
				}
				ob_end_clean();
				header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
				header( 'Content-Description: File Transfer' );
				header( 'Content-type: text/csv' );
				header( 'Content-Disposition: attachment; filename=' . $filename );
				header( 'Expires: 0' );
				header( 'Pragma: public' );
				$fh = @fopen( 'php://output', 'w' );
				fprintf( $fh, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
				fputcsv( $fh, array_values( $header_row ) );
				foreach ( $data_rows as $data_row ) {
					fputcsv( $fh, $data_row );
				}
				$csvFile = stream_get_contents( $fh );
				fclose( $fh );
				die;
			}
		}
	}
}
