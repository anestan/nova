<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Admin
 */
class VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Admin {
	protected $settings;
	protected $anchor_link;
	protected $new_review_id;
	protected $language;
	protected $languages;
	protected $default_language;
	protected $languages_data;

	public function __construct() {
		$this->settings         = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
		$this->anchor_link      = '#' . $this->settings->get_params( 'reviews_anchor_link' );
		$this->languages        = array();
		$this->languages_data   = array();
		$this->default_language = '';
		$this->new_review_id    = array();
		add_filter(
			'plugin_action_links_woocommerce-photo-reviews/woocommerce-photo-reviews.php', array(
				$this,
				'settings_link'
			)
		);
		add_action( 'admin_init', array( $this, 'update_data' ) );
		add_action( 'admin_init', array( $this, 'admin_add_review_handle' ) );
		add_action( 'admin_init', array( $this, 'save_settings' ) );
		add_action( 'admin_init', array( $this, 'render_reviews_imported_by_alidswoo' ) );
		add_action( 'admin_init', array( $this, 'check_update' ) );
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_system_status' ), 30 );
		add_action( 'admin_notices', array( $this, 'render_reviews_imported_by_alidswoo_notice' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		add_action( 'wp_ajax_wcpr_select_all_products', array( $this, 'select_all_products' ) );
		add_action( 'wp_ajax_wcpr_search_coupon', array( $this, 'search_coupon' ) );
		add_action( 'wp_ajax_wcpr_search_parent_product', array( $this, 'search_parent_product' ) );
		add_action( 'wp_ajax_wcpr_search_cate', array( $this, 'search_cate' ) );
		add_action( 'wp_ajax_wcpr_search_page', array( $this, 'search_page' ) );
		//if a review is deleted, also delete the photos of that review
		add_action( 'delete_comment', array( $this, 'delete_reviews_image' ) );
		add_action( 'delete_attachment', array( $this, 'delete_attachment' ) );
		/*filter reviews*/
		add_action( 'restrict_manage_comments', array( $this, 'restrict_manage_comments' ), 10 );
		add_action( 'parse_comment_query', array( $this, 'wp_list_comments_args' ), 10 );
		add_action( 'admin_notices', array( $this, 'bulk_admin_notices' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box_title' ) );
		add_action( 'add_meta_boxes_comment', array( $this, 'add_meta_box_photo' ) );
		add_action( 'edit_comment', array( $this, 'save_comment_meta' ) );
		add_action( 'load-edit-comments.php', array( $this, 'load_photos_in_comment_list' ) );
		add_action( 'manage_comments_custom_column', array( $this, 'column_callback' ), 10, 2 );
		add_filter( 'bulk_actions-edit-comments', array( $this, 'register_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-comments', array( $this, 'bulk_action_handler' ), 99, 3 );

		add_filter( 'manage_shop_order_posts_columns', array( $this, 'load_review_in_shop_order_list' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'column_callback_shop_order' ) );
		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'shop_order_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-shop_order', array(
			$this,
			'handle_shop_order_bulk_actions'
		), 15, 3 );
		/*preview email*/
		add_action( 'media_buttons', array( $this, 'preview_emails_button' ) );
		add_action( 'wp_ajax_wcpr_preview_emails', array( $this, 'preview_emails_ajax' ) );
		add_action( 'admin_footer', array( $this, 'preview_emails_html' ) );
		/*add image size*/
		add_action( 'init', array( $this, 'add_image_size' ) );

		/*manage uploaded image sizes when uploading review images in backend*/
		add_filter( 'plupload_default_params', array( $this, 'plupload_default_params' ) );
		add_filter( 'intermediate_image_sizes', array( $this, 'reduce_image_sizes_for_media_upload' ) );
	}

	public function settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=woocommerce-photo-reviews" title="' . __( 'Settings', 'woocommerce-photo-reviews' ) . '">' . __( 'Settings', 'woocommerce-photo-reviews' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	public function check_update() {
		if ( class_exists( 'VillaTheme_Plugin_Check_Update' ) ) {
			$setting_url = admin_url( 'admin.php?page=woocommerce-photo-reviews' );
			$key         = $this->settings->get_params( 'key' );
			new VillaTheme_Plugin_Check_Update (
				VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION,                    // current version
				'https://villatheme.com/wp-json/downloads/v3',  // update path
				'woocommerce-photo-reviews/woocommerce-photo-reviews.php',                  // plugin file slug
				'woocommerce-photo-reviews', '11292', $key, $setting_url
			);
			new VillaTheme_Plugin_Updater( 'woocommerce-photo-reviews/woocommerce-photo-reviews.php', 'woocommerce-photo-reviews', $setting_url );
		}
	}

	public function render_reviews_imported_by_alidswoo() {
		if ( isset( $_GET['woocommerce_photo_review_render_reviews_imported_by_alidswoo'] ) ) {
			if ( $_GET['woocommerce_photo_review_render_reviews_imported_by_alidswoo'] === 'update' ) {
				$args     = array(
					'post_type'    => 'product',
					'status'       => 'all',
					'author_email' => false,
					'meta_query'   => array(
						'relation' => 'AND',
						array(
							'key'     => 'id_import_reviews_from_ali',
							'compare' => 'NOT EXISTS'
						),
						array(
							'key'     => 'images',
							'compare' => 'EXISTS',
						),
					)
				);
				$comments = get_comments( $args );
				if ( count( $comments ) ) {
					foreach ( $comments as $comment ) {
						$comment_id   = $comment->comment_ID;
						$alids_images = get_comment_meta( $comment_id, 'images', true );
						if ( is_array( $alids_images ) && count( $alids_images ) ) {
							update_comment_meta( $comment_id, 'reviews-images', $alids_images );
							delete_comment_meta( $comment_id, 'images' );
						}
					}
				}
				set_transient( 'woocommerce_photo_review_render_reviews_imported_by_alidswoo', current_time( 'timestamp' ) );
			} elseif ( $_GET['woocommerce_photo_review_render_reviews_imported_by_alidswoo'] === 'hide' ) {
				set_transient( 'woocommerce_photo_review_render_reviews_imported_by_alidswoo', current_time( 'timestamp' ) );
			}
		}
	}

	public function render_reviews_imported_by_alidswoo_notice() {
		if ( ! get_transient( 'woocommerce_photo_review_render_reviews_imported_by_alidswoo' ) ) {
			if ( is_plugin_active( 'alidswoo/alidswoo.php' ) ) {
				?>
                <p><?php _e( 'Your site is using AliDropship Woo Plugin, reviews with photos imported by this plugin are not displayed properly with WooCommerce Photo Reviews plugin. <a href="' . add_query_arg( array( 'woocommerce_photo_review_render_reviews_imported_by_alidswoo' => 'update' ) ) . '">Update now</a> or <a href="' . add_query_arg( array( 'woocommerce_photo_review_render_reviews_imported_by_alidswoo' => 'hide' ) ) . '">Hide</a>', 'woocommerce-photo-reviews' ) ?></p>
				<?php
			}
		}
	}

	public function update_data() {
		if ( ! get_transient( 'woocommerce_photo_review_update_data_version_1_1_0' ) ) {
			$args     = array(
				'post_type'  => 'product',
				'type'       => 'review',
				'status'     => 'approve',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'     => 'id_import_reviews_from_ali',
						'compare' => 'NOT EXISTS'
					)
				)
			);
			$comments = get_comments( $args );
			if ( count( $comments ) ) {
				foreach ( $comments as $comment ) {
					$user_id    = $comment->user_id;
					$product_id = $comment->comment_post_ID;
					if ( ! $product_id ) {
						continue;
					}
					if ( ! $user_id ) {
						$user_id = get_user_by( 'email', $comment->comment_author_email );
					}
					if ( $user_id ) {
						$user_coupon = get_user_meta( $user_id, 'wcpr_user_reviewed_product', false );
						if (empty($user_coupon) || (is_array($user_coupon) && ! in_array( $product_id, $user_coupon )) ) {
							add_user_meta( $user_id, 'wcpr_user_reviewed_product', $product_id );
						}
					}
				}
			}
			set_transient( 'woocommerce_photo_review_update_data_version_1_1_0', current_time( 'timestamp' ) );
		}
	}

	public function add_image_size() {
	    $img_size = apply_filters('viwcpr_reduce_image_sizes',['width'=>500, 'height' => 500]);
		if ( is_ajax() ) {
			/*for adding or updating reviews in admin and downloading image while importing*/
			if ( ( isset( $_POST['wcpr_adjust_image_sizes'] ) && $_POST['wcpr_adjust_image_sizes'] ) ) {
				add_image_size( 'wcpr-photo-reviews', $img_size['width'] ?? 500,  $img_size['height'] ?? 500 );
			}
		} elseif ( is_admin() ) {
			/*for bulk download review images of imported reviews from AliExpress*/
			global $pagenow;
			if ( $pagenow === 'edit-comments.php' ) {
				add_image_size( 'wcpr-photo-reviews', $img_size['width'] ?? 500,  $img_size['height'] ?? 500 );
				add_filter( 'intermediate_image_sizes', array(
					'VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend',
					'reduce_image_sizes'
				) );
			}
		} else {
			/*for frontend usage when a customer adds a review*/
			add_image_size( 'wcpr-photo-reviews', $img_size['width'] ?? 500,  $img_size['height'] ?? 500 );
		}
	}

	/**When using wp_media to upload images for adding or updating reviews in admin, set $params['wcpr_adjust_image_sizes'] to
	 * detect and reduce image sizes for those uploading only
	 *
	 * @param $params
	 *
	 * @return mixed
	 */
	public function plupload_default_params( $params ) {
		global $pagenow;
		if ( $pagenow === 'admin.php' && isset( $_REQUEST['page'] ) && $_REQUEST['page'] === 'kt-wcpr-add-review' ) {
			$params['wcpr_adjust_image_sizes'] = 1;
		} elseif ( $pagenow == 'comment.php' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'editcomment' ) {
			$params['wcpr_adjust_image_sizes'] = 1;
		} elseif ( $pagenow === 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'product' ) {
			$params['wcpr_adjust_image_sizes'] = 1;
		}

		return $params;
	}

	public function reduce_image_sizes_for_media_upload( $sizes ) {
		if ( isset( $_POST['wcpr_adjust_image_sizes'] ) && $_POST['wcpr_adjust_image_sizes'] ) {
			foreach ( $sizes as $k => $size ) {
				if ( in_array( $size, array( 'thumbnail', 'wcpr-photo-reviews', 'medium' ) ) ) {
					continue;
				}
				unset( $sizes[ $k ] );
			}
		}

		return $sizes;
	}

	public function reduce_image_sizes_for_import_reviews( $sizes ) {
		foreach ( $sizes as $k => $size ) {
			if ( in_array( $size, array( 'thumbnail', 'wcpr-photo-reviews', 'medium' ) ) ) {
				continue;
			}
			unset( $sizes[ $k ] );
		}

		return $sizes;
	}

	function preview_emails_html() {
		global $pagenow;
		if ( $pagenow === 'admin.php' && isset( $_REQUEST['page'] ) && $_REQUEST['page'] === 'woocommerce-photo-reviews' ) {
			?>
            <div class="preview-emails-html-container preview-html-hidden">
                <div class="preview-emails-html-overlay"></div>
                <div class="preview-emails-html"></div>
            </div>
			<?php
		}
	}

	public function preview_emails_button( $editor_id ) {
		global $pagenow;
		if ( $pagenow === 'admin.php' && isset( $_REQUEST['page'] ) && $_REQUEST['page'] === 'woocommerce-photo-reviews' ) {
			$editor_ids = array( 'content' );
			if ( count( $this->languages ) ) {
				foreach ( $this->languages as $key => $value ) {
					$editor_ids[] = 'content_' . $value;
				}
			}
			if ( in_array( $editor_id, $editor_ids ) ) {
				ob_start();
				?>
                <span class="button coupon-preview-emails-button"
                      data-wcpr_language="<?php echo str_replace( 'content', '', $editor_id ) ?>"><?php esc_html_e( 'Preview email', 'woocommerce-photo-reviews' ) ?></span>
				<?php
				echo ob_get_clean();
			}
			$editor_ids = array( 'follow_up_email_content' );
			if ( count( $this->languages ) ) {
				foreach ( $this->languages as $key => $value ) {
					$editor_ids[] = 'follow_up_email_content_' . $value;
				}
			}
			if ( in_array( $editor_id, $editor_ids ) ) {
				ob_start();
				?>
                <span class="button reminder-preview-emails-button"
                      data-wcpr_language="<?php echo str_replace( 'follow_up_email_content', '', $editor_id ) ?>"><?php esc_html_e( 'Preview email', 'woocommerce-photo-reviews' ) ?></span>
				<?php
				echo ob_get_clean();
			}
		}
	}

	public function preview_emails_ajax() {
		$date_format         = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_date_format();
		$email_type          = isset( $_GET['email_type'] ) ? sanitize_text_field( $_GET['email_type'] ) : 'coupon';
		$content             = isset( $_GET['content'] ) ? wp_kses_post( stripslashes( $_GET['content'] ) ) : '';
		$email_heading       = isset( $_GET['heading'] ) ? ( stripslashes( $_GET['heading'] ) ) : '';
		$review_button       = isset( $_GET['review_button'] ) ? ( stripslashes( $_GET['review_button'] ) ) : '';
		$product_image_width = isset( $_GET['product_image_width'] ) ? absint( stripslashes( $_GET['product_image_width'] ) ) : 150;
		$customer_name       = 'John';
		if ( $email_type === 'coupon' ) {
			$coupon_value = '10%';
			$coupon_code  = 'HAPPY';
			$date_expires = strtotime( '+30 days' );

			$content       = str_replace( '{customer_name}', $customer_name, $content );
			$content       = str_replace( '{coupon_code}', '<span style="font-size: x-large;">' . strtoupper( $coupon_code ) . '</span>', $content );
			$content       = str_replace( '{date_expires}', date_i18n( $date_format, $date_expires ), $content );
			$email_heading = str_replace( '{coupon_value}', $coupon_value, $email_heading );
		} else {
			$anchor                 = isset( $_GET['anchor'] ) ? sanitize_text_field( $_GET['anchor'] ) : '';
			$anchor                 = '#' . $anchor;
			$review_button_bg_color = isset( $_GET['review_button_bg_color'] ) ? sanitize_text_field( $_GET['review_button_bg_color'] ) : '';
			$review_button_color    = isset( $_GET['review_button_color'] ) ? sanitize_text_field( $_GET['review_button_color'] ) : '';
			$order_id               = 1;
			$now                    = strtotime( 'now' );
			$date_create            = date_i18n( $date_format, $now - 86400 );
			$date_complete          = date_i18n( $date_format, $now );
			$content                = str_replace( '{customer_name}', $customer_name, $content );
			$content                = str_replace( '{order_id}', $order_id, $content );
			$content                = str_replace( '{date_create}', $date_create, $content );
			$content                = str_replace( '{date_complete}', $date_complete, $content );
			$content                = str_replace( '{site_title}', get_bloginfo( 'name' ), $content );
			$content                .= '<table style="width: 100%;">';
			$sents                  = array();
			$products               = wc_get_products( array( 'numberposts' => 3, 'post_status' => 'public' ) );
			if ( count( $products ) ) {
				foreach ( $products as $p ) {
					$product = wc_get_product( $p );
					if ( $product ) {
						$product_image = wp_get_attachment_thumb_url( $product->get_image_id() );
						$product_url   = $product->get_permalink() . $anchor;
						$product_title = $product->get_title();
						$product_price = $product->get_price_html();
						if ( $product->is_type( 'variation' ) ) {
							$product_parent_id = $product->get_parent_id();
							if ( in_array( $product_parent_id, $sents ) ) {
								continue;
							}
							$product_parent = wc_get_product( $product_parent_id );
							if ( $product_parent ) {
								if ( ! $product_image ) {
									$product_image = wp_get_attachment_thumb_url( $product_parent->get_image_id() );
								}
								$product_url   = $product_parent->get_permalink() . $anchor;
								$product_title = $product_parent->get_title();
								$product_price = $product_parent->get_price_html();
								$sents[]       = $product_parent_id;
							}
						} else {
							if ( in_array( $p, $sents ) ) {
								continue;
							}
							$sents[] = $p;
						}
						ob_start();
						?>
                        <tr>
                            <td style="text-align: center;">
                                <a target="_blank" href="<?php echo $product_url ?>">
                                    <img style="width: <?php echo $product_image_width ?>px;"
                                         src="<?php echo $product_image ? $product_image : wc_placeholder_img_src() ?>"
                                         alt="<?php echo $product_title ?>">
                                </a>
                            </td>
                            <td>
                                <p>
                                    <a target="_blank"
                                       href="<?php echo $product_url ?>"><?php echo $product_title ?></a>
                                </p>
                                <p><?php echo $product_price ?></p>
                                <a target="_blank"
                                   style="text-align: center;padding: 10px;text-decoration: none;font-weight: 800;
                                           background-color:<?php echo $review_button_bg_color ?>;
                                           color:<?php echo $review_button_color ?>;"
                                   href="<?php echo $product_url ?>"><?php esc_html_e( $review_button ) ?>
                                </a>
                            </td>
                        </tr>
						<?php
						$content .= ob_get_clean();
					}

				}
			}
			$content .= '</table>';
		}


		// load the mailer class
		$mailer = WC()->mailer();

		// create a new email
		$email = new WC_Email();

		// wrap the content with the email template and then add styles
		$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $content ) ) );

		// print the preview email
		wp_send_json(
			array(
				'html' => $message,
			)
		);
	}

	//add review reminder in shop order
	public function load_review_in_shop_order_list( $columns ) {
		$screen = get_current_screen();
		add_filter( "manage_{$screen->id}_columns", array( $this, 'add_columns_shop_order' ) );

		return $columns;
	}

	public function add_columns_shop_order( $cols ) {
		$cols['wcpr_review_reminder'] = __( 'Review Reminder', 'woocommerce-photo-reviews' );

		return $cols;
	}

	public function column_callback_shop_order( $col ) {
		// you could expand the switch to take care of other custom columns
		global $post;

		switch ( $col ) {
			case 'wcpr_review_reminder':
				$message         = array(
					'pending' => __( 'Pending', 'woocommerce-photo-reviews' ),
					'sent'    => __( 'Sent', 'woocommerce-photo-reviews' ),
					'cancel'  => __( 'Cancel', 'woocommerce-photo-reviews' ),
				);
				$review_reminder = get_post_meta( $post->ID, '_wcpr_review_reminder', true );
				if ( $review_reminder ) {
					$status_message = isset( $message[ $review_reminder['status'] ] ) ? $message[ $review_reminder['status'] ] : $review_reminder['status'];
					$time           = isset( $review_reminder['time'] ) ? $review_reminder['time'] : 0;
					$time           = $time + get_option( 'gmt_offset' ) * 3600;
					echo $status_message . ' : ' . date_i18n( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_datetime_format(), $time );
				}
				break;
		}
	}

	public function shop_order_bulk_actions( $actions ) {
		$actions['send_reminder']   = __( 'Send Review Reminder', 'woocommerce-photo-reviews' );
		$actions['cancel_reminder'] = __( 'Cancel Review Reminder', 'woocommerce-photo-reviews' );

		return $actions;
	}

	public function handle_shop_order_bulk_actions( $redirect_to, $action, $ids ) {
		global $wcpr_products_to_review;
		switch ( $action ) {
			case 'cancel_reminder':
				$ids     = array_map( 'absint', $ids );
				$changed = 0;
				$crons   = _get_cron_array();
				foreach ( $ids as $id ) {
					$time = time();
					if ( get_post_meta( $id, '_wcpr_review_reminder', true ) ) {
						$review_reminder = get_post_meta( $id, '_wcpr_review_reminder', true );
						if ( isset( $review_reminder['token'] ) && $review_reminder['token'] ) {
							$token = $review_reminder['token'];
							delete_transient( $token );
						}
						if ( $review_reminder['time'] > $time ) {

							$unschedule = wp_unschedule_event(
								$review_reminder['time'], 'wcpr_schedule_email', array(
									$id
								)
							);
							if ( false === $unschedule ) {
								if ( isset( $crons[ $review_reminder['time'] ] ) && is_array( $crons[ $review_reminder['time'] ] ) ) {
									$my_cron = $crons[ $review_reminder['time'] ];
									foreach ( $my_cron as $my_cron_k => $my_cron_v ) {
										if ( $my_cron_k == 'wpr_schedule_email' && is_array( $my_cron_v ) ) {
											foreach ( $my_cron_v as $cron ) {
												if ( isset( $cron['args'] ) ) {
													$unschedule = wp_unschedule_event( $review_reminder['time'], 'wpr_schedule_email', $cron['args'] );
												}
											}
										}
									}
								} else {
									delete_post_meta( $id, '_wcpr_review_reminder' );
								}
							}

							if ( $unschedule !== false ) {
								$changed ++;
								update_post_meta(
									$id, '_wcpr_review_reminder', array(
										'status' => 'cancel',
										'time'   => $time,
									)
								);
							} else {
								delete_post_meta( $id, '_wcpr_review_reminder' );
							}

						}
					}
				}
				$redirect_to = add_query_arg(
					array(
						'post_type'         => 'shop_order',
						'changed'           => $changed,
						'ids'               => join( ',', $ids ),
						'marked_processing' => false,
						'marked_on-hold'    => false,
						'marked_completed'  => false,
						'trash'             => false,
						'bulk_action'       => $action,
						$action             => true,
					), $redirect_to
				);
				break;
			case 'send_reminder':
				$date_format = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_date_format();
				$ids = array_map( 'absint', $ids );
				$changed = 0;
				$crons = _get_cron_array();
				$order_statuses = $this->settings->get_params( 'followup_email', 'order_statuses' );
				foreach ( $ids as $id ) {
					$time  = time();
					$order = wc_get_order( $id );
					if ( ! $order || ! in_array( "wc-{$order->get_status()}", $order_statuses ) ) {
						continue;
					}
					$date_create = $order->get_date_created();
					if ( $date_create ) {
						$date_create = $date_create->date_i18n( $date_format );
					}
					$date_complete = $order->get_date_completed();
					if ( $date_complete ) {
						$date_complete = $date_complete->date_i18n( $date_format );
					}
					$items    = $order->get_items();
					$products = array();

					$products_restriction = $this->settings->get_params( 'followup_email', 'products_restriction' );
					$excluded_categories  = $this->settings->get_params( 'followup_email', 'excluded_categories' );
					$products_gene        = $this->settings->get_params( 'coupon', 'products_gene' );
					$categories_gene      = $this->settings->get_params( 'coupon', 'categories_gene' );
					if ( 'on' == $this->settings->get_params( 'followup_email', 'exclude_non_coupon_products' ) ) {
						$excluded_categories  = array_merge( $this->settings->get_params( 'coupon', 'excluded_categories_gene' ), $excluded_categories );
						$products_restriction = array_merge( $this->settings->get_params( 'coupon', 'excluded_products_gene' ), $products_restriction );
						foreach ( $items as $item ) {
							$product_id = $item->get_product_id();
							if ( in_array( $product_id, $products_restriction ) ) {
								continue;
							}
							if ( count( $products_gene ) && ! in_array( $product_id, $products_gene ) ) {
								continue;
							}
							$product = wc_get_product( $product_id );
							if ( $product ) {
								$categories = $product->get_category_ids();
								if ( count( $categories_gene ) && ! count( array_intersect( $categories, $categories_gene ) ) ) {
									continue;
								}
								if ( count( array_intersect( $categories, $excluded_categories ) ) ) {
									continue;
								}
							}

							$products[] = $product_id;
						}
					} else {
						foreach ( $items as $item ) {
							$product_id = $item->get_product_id();
							if ( in_array( $product_id, $products_restriction ) ) {
								continue;
							}
							if ( count( $excluded_categories ) ) {
								$product = wc_get_product( $product_id );
								if ( $product ) {
									$categories = $product->get_category_ids();
									if ( count( array_intersect( $categories, $excluded_categories ) ) ) {
										continue;
									}
								}
							}
							$products[] = $product_id;
						}
					}
					$products             = array_unique( $products );
					$user_email           = $order->get_billing_email();
					$customer_name        = $order->get_billing_first_name();
					$language             = '';
					$multi_language       = $this->settings->get_params( 'multi_language' );
					$review_form_page     = $this->settings->get_params( 'followup_email', 'review_form_page' );
					$product_image_width  = $this->settings->get_params( 'followup_email', 'product_image_width' );
					$review_form_page_url = '';
					if ( $review_form_page ) {
						$review_form_page_url = get_permalink( $review_form_page );
					}
					if ( $multi_language ) {
						$language = get_post_meta( $id, 'wpml_language', true );
						if ( ! $language && function_exists( 'pll_get_post_language' ) ) {
							$language = pll_get_post_language( $id );
						}
					}

					if ( get_post_meta( $id, '_wcpr_review_reminder', true ) ) {
						$review_reminder = get_post_meta( $id, '_wcpr_review_reminder', true );
						if ( $multi_language && isset( $review_reminder['language'] ) && ! $language ) {
							$language = $review_reminder['language'];
						}
						if ( $review_reminder['time'] > $time ) {
							if ( isset( $review_reminder['token'] ) && $review_reminder['token'] ) {
								$token = $review_reminder['token'];
								delete_transient( $token );
							}

							$unschedule = wp_unschedule_event(
								$review_reminder['time'], 'wcpr_schedule_email', array(
									$id
								)
							);

							if ( false === $unschedule ) {
								if ( isset( $crons[ $review_reminder['time'] ] ) && is_array( $crons[ $review_reminder['time'] ] ) ) {
									$my_cron = $crons[ $review_reminder['time'] ];
									foreach ( $my_cron as $my_cron_k => $my_cron_v ) {
										if ( $my_cron_k == 'wpr_schedule_email' && is_array( $my_cron_v ) ) {
											foreach ( $my_cron_v as $cron ) {
												if ( isset( $cron['args'] ) ) {
													$unschedule = wp_unschedule_event(
														$review_reminder['time'], 'wpr_schedule_email', $cron['args'] );
												}
											}
										}
									}
								} else {
									delete_post_meta( $id, '_wcpr_review_reminder' );
								}
							}
						}
					}

					$product_html = '';
					if ( count( $products ) ) {
						$user_id = $order->get_user_id();
						if ( ! $user_id ) {
							$user = get_user_by( 'email', $user_email );
							if ( $user ) {
								$user_id = $user->ID;
							}
						}
						if ( $user_id ) {
							$user_reviewed_products = get_user_meta( $user_id, 'wcpr_user_reviewed_product', false );
							$auto_login             = $this->settings->get_params( 'followup_email', 'auto_login' );
							$auto_login_exclude     = $this->settings->get_params( 'followup_email', 'auto_login_exclude' );
							$token                  = '';
							if ( ! count( $user_reviewed_products ) ) {
								if ( $auto_login ) {
									$user = new WP_User( $user_id );
									if ( ! count( array_intersect( $auto_login_exclude, $user->roles ) ) ) {
										$token = uniqid( md5( $id ) );
										set_transient( $token, $user_id, 2592000 );
									}
								}
								$changed ++;
								foreach ( $products as $p ) {
									if ( $language && is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
										$p = icl_object_id( $p, 'product', true, $language );
									}
									$product = wc_get_product( $p );
									if ( $product ) {
										$product_image = wp_get_attachment_thumb_url( $product->get_image_id() );
										$product_url   = $product->get_permalink() . $this->anchor_link;
										if ( $review_form_page_url ) {
											$product_url = add_query_arg( array(
												'product_id' => $p,
												'user_name'  => urlencode( base64_encode( $customer_name ) ),
												'user_email' => urlencode( base64_encode( $user_email ) ),
												'user_id'    => urlencode( base64_encode( $user_id ) ),
											), $review_form_page_url );
										}
										if ( $token ) {
											$product_url = add_query_arg( 'wcpr_token', $token, $product_url );
										}
										$product_title             = $product->get_title();
										$product_price             = apply_filters( 'woocommerce_photo_reviews_reminder_product_price', $product->get_price_html(), $product, $order );
										$wcpr_products_to_review[] = array(
											'image'      => $product_image,
											'name'       => $product_title,
											'price'      => $product_price,
											'review_url' => $product_url,
										);
										ob_start();
										?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <a target="_blank" href="<?php echo $product_url ?>">
                                                    <img style="width: <?php echo $product_image_width ?>px;"
                                                         src="<?php echo $product_image ?>"
                                                         alt="<?php echo $product_title ?>">
                                                </a>
                                            </td>
                                            <td>
                                                <p>
                                                    <a target="_blank"
                                                       href="<?php echo $product_url ?>"><?php echo $product_title ?></a>
                                                </p>
                                                <p><?php echo $product_price ?></p>
                                                <a target="_blank"
                                                   style="text-align: center;padding: 10px;text-decoration: none;font-weight: 800;
                                                           background-color:<?php echo $this->settings->get_params( 'followup_email', 'review_button_bg_color' ); ?>;
                                                           color:<?php echo $this->settings->get_params( 'followup_email', 'review_button_color' ) ?>;"
                                                   href="<?php echo $product_url ?>"><?php esc_html_e( $this->settings->get_params( 'followup_email', 'review_button', $language ) ) ?>
                                                </a>
                                            </td>
                                        </tr>
										<?php
										$product_html .= ob_get_clean();
									}

								}

							} else {
								$not_reviewed_products = array_diff( $products, $user_reviewed_products );
								if ( count( $not_reviewed_products ) ) {
									if ( $auto_login ) {
										$user = new WP_User( $user_id );
										if ( ! count( array_intersect( $auto_login_exclude, $user->roles ) ) ) {
											$token = uniqid( md5( $id ) );
											set_transient( $token, $user_id, 2592000 );
										}
									}
									$changed ++;
									foreach ( $not_reviewed_products as $p ) {
										if ( $language && is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
											$p = icl_object_id( $p, 'product', true, $language );
										}
										$product = wc_get_product( $p );
										if ( $product ) {
											$product_image = wp_get_attachment_thumb_url( $product->get_image_id() );
											$product_url   = $product->get_permalink() . $this->anchor_link;
											if ( $review_form_page_url ) {
												$product_url = add_query_arg( array(
													'product_id' => $p,
													'user_name'  => urlencode( base64_encode( $customer_name ) ),
													'user_email' => urlencode( base64_encode( $user_email ) ),
													'user_id'    => urlencode( base64_encode( $user_id ) ),
												), $review_form_page_url );
											}
											if ( $token ) {
												$product_url = add_query_arg( 'wcpr_token', $token, $product_url );
											}
											$product_title             = $product->get_title();
											$product_price             = apply_filters( 'woocommerce_photo_reviews_reminder_product_price', $product->get_price_html(), $product, $order );
											$wcpr_products_to_review[] = array(
												'image'      => $product_image,
												'name'       => $product_title,
												'price'      => $product_price,
												'review_url' => $product_url,
											);
											ob_start();
											?>
                                            <tr>
                                                <td style="text-align: center;">
                                                    <a target="_blank" href="<?php echo $product_url ?>">
                                                        <img style="width: <?php echo $product_image_width ?>px;"
                                                             src="<?php echo $product_image ?>"
                                                             alt="<?php echo $product_title ?>">
                                                    </a>
                                                </td>
                                                <td>
                                                    <p>
                                                        <a target="_blank"
                                                           href="<?php echo $product_url ?>"><?php echo $product_title ?></a>
                                                    </p>
                                                    <p><?php echo $product_price ?></p>
                                                    <a target="_blank"
                                                       style="text-align: center;padding: 10px;text-decoration: none;font-weight: 800;
                                                               background-color:<?php echo $this->settings->get_params( 'followup_email', 'review_button_bg_color' ); ?>;
                                                               color:<?php echo $this->settings->get_params( 'followup_email', 'review_button_color' ) ?>;"
                                                       href="<?php echo $product_url ?>"><?php esc_html_e( $this->settings->get_params( 'followup_email', 'review_button', $language ) ) ?>
                                                    </a>
                                                </td>
                                            </tr>
											<?php
											$product_html .= ob_get_clean();
										}

									}
								}
							}
						} else {
							foreach ( $products as $p ) {
								if ( $language && is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
									$p = icl_object_id( $p, 'product', true, $language );
								}
								$args     = array(
									'post_type'    => 'product',
									'type'         => 'review',
									'author_email' => $user_email,
									'post_id'      => $p,
									'meta_query'   => array(
										'relation' => 'AND',
										array(
											'key'     => 'id_import_reviews_from_ali',
											'compare' => 'NOT EXISTS'
										),
									)
								);
								$comments = get_comments( $args );
								if ( ! count( $comments ) ) {
									$product = wc_get_product( $p );
									if ( $product ) {
										$changed ++;
										$product_image = wp_get_attachment_thumb_url( $product->get_image_id() );
										$product_url   = $product->get_permalink() . $this->anchor_link;
										if ( $review_form_page_url ) {
											$product_url = add_query_arg( array(
												'product_id' => $p,
												'user_name'  => urlencode( base64_encode( $customer_name ) ),
												'user_email' => urlencode( base64_encode( $user_email ) ),
											), $review_form_page_url );
										}
										$product_title             = $product->get_title();
										$product_price             = apply_filters( 'woocommerce_photo_reviews_reminder_product_price', $product->get_price_html(), $product, $order );
										$wcpr_products_to_review[] = array(
											'image'      => $product_image,
											'name'       => $product_title,
											'price'      => $product_price,
											'review_url' => $product_url,
										);
										ob_start();
										?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <a target="_blank" href="<?php echo $product_url ?>">
                                                    <img style="width: <?php echo $product_image_width ?>px;"
                                                         src="<?php echo $product_image ?>"
                                                         alt="<?php echo $product_title ?>">
                                                </a>
                                            </td>
                                            <td>
                                                <p>
                                                    <a target="_blank"
                                                       href="<?php echo $product_url ?>"><?php echo $product_title ?></a>
                                                </p>
                                                <p><?php echo $product_price ?></p>
                                                <a target="_blank"
                                                   style="text-align: center;padding: 10px;text-decoration: none;font-weight: 800;
                                                           background-color:<?php echo $this->settings->get_params( 'followup_email', 'review_button_bg_color' ); ?>;
                                                           color:<?php echo $this->settings->get_params( 'followup_email', 'review_button_color' ) ?>;"
                                                   href="<?php echo $product_url ?>"><?php esc_html_e( $this->settings->get_params( 'followup_email', 'review_button', $language ) ) ?>
                                                </a>
                                            </td>
                                        </tr>
										<?php
										$product_html .= ob_get_clean();
									}
								}
							}
						}

						if ( $product_html ) {
							$email_template = $this->settings->get_params( 'reminder_email_template', '', $language );
							$use_template   = false;
							if ( $email_template && VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::is_email_template_customizer_active() ) {
								$email_template_obj = get_post( $email_template );
								if ( $email_template_obj && $email_template_obj->post_type === 'viwec_template' ) {
									$use_template = true;
									ob_start();
									viwec_render_email_template( $email_template );
									$content = ob_get_clean();
									$content = str_replace( '{wcpr_customer_name}', $customer_name, $content );
									$content = str_replace( '{wcpr_order_id}', $id, $content );
									$content = str_replace( '{wcpr_order_date_create}', $date_create, $content );
									$content = str_replace( '{wcpr_order_date_complete}', $date_complete, $content );
									$content = str_replace( '{site_title}', get_bloginfo( 'name' ), $content );
									$subject = $email_template_obj->post_title;
								}
							}
							$mailer  = WC()->mailer();
							$email   = new WC_Email();
							$headers = "Content-Type: text/html\r\nReply-to: {$email->get_from_name()} <{$email->get_from_address()}>\r\n";
							if ( ! $use_template ) {
								$subject       = stripslashes( $this->settings->get_params( 'followup_email', 'subject', $language ) );
								$content       = nl2br( stripslashes( $this->settings->get_params( 'followup_email', 'content', $language ) ) );
								$content       = str_replace( '{customer_name}', $customer_name, $content );
								$content       = str_replace( '{order_id}', $id, $content );
								$content       = str_replace( '{date_create}', $date_create, $content );
								$content       = str_replace( '{date_complete}', $date_complete, $content );
								$content       = str_replace( '{site_title}', get_bloginfo( 'name' ), $content );
								$content       .= '<table style="width: 100%;">' . $product_html . '</table>';
								$email_heading = $this->settings->get_params( 'followup_email', 'heading', $language );
								$content       = $email->style_inline( $mailer->wrap_message( $email_heading, $content ) );
							}
							add_filter( 'woocommerce_email_from_address', array(
								$this,
								'woocommerce_email_from_address'
							), 999 );
							$email->send( $user_email, $subject, $content, $headers, array() );
							update_post_meta( $id, '_wcpr_review_reminder', array(
								'status' => 'sent',
								'time'   => $time,
							) );
							remove_filter( 'woocommerce_email_from_address', array(
								$this,
								'woocommerce_email_from_address'
							), 999 );
						}
					}
				}
				$redirect_to = add_query_arg(
					array(
						'post_type'         => 'shop_order',
						'changed'           => $changed,
						'ids'               => join( ',', $ids ),
						'marked_processing' => false,
						'marked_on-hold'    => false,
						'marked_completed'  => false,
						'trash'             => false,
						'bulk_action'       => $action,
						$action             => true,
					), $redirect_to
				);
				break;
			default:
				$redirect_to = remove_query_arg( 'cancel_reminder', $redirect_to );
				$redirect_to = remove_query_arg( 'send_reminder', $redirect_to );
		}

		return $redirect_to;
	}

	public function woocommerce_email_from_address( $from_address ) {
		if ( $this->settings->get_params( 'followup_email', 'from_address' ) && is_email( $this->settings->get_params( 'followup_email', 'from_address' ) ) ) {
			$from_address = sanitize_email( $this->settings->get_params( 'followup_email', 'from_address' ) );
		}

		return $from_address;
	}

	public function status() {
		?>
        <div id="kt_status_page" class="wrap">
            <h1></h1>
            <table width="30%">
                <tr>
                    <th colspan="3"><?php esc_html_e( 'System status', 'woocommerce-photo-reviews' ) ?></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <td width="50%"><?php esc_html_e( 'File upload:', 'woocommerce-photo-reviews' ) ?></td>
                    <td width="25%"><?php if ( ini_get( 'file_uploads' ) == 1 ) {
							echo 'On';
						} else {
							echo 'Off';
						} ?></td>
                    <td><?php if ( ini_get( 'file_uploads' ) == 1 ) {
							echo '<span class="status-ok">OK</span>';
						} else {
							echo '<span class="status-bad">X</span>';
						} ?></td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Upload max filesize:', 'woocommerce-photo-reviews' ) ?></td>
                    <td><?php _e( ini_get( 'upload_max_filesize' ), 'woocommerce-photo-reviews' ); ?></td>
                    <td><?php if ( ini_get( 'post_max_size' ) > ( absint( ini_get( 'upload_max_filesize' ) ) * ini_get( 'max_file_uploads' ) + 1 ) && ini_get( 'upload_max_filesize' ) > 0 && ini_get( 'max_file_uploads' ) > 0 ) {
							echo '<span class="status-ok">OK</span>';
						} else {
							echo '<span class="status-bad">X</span>';
						} ?></td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Max file uploads:', 'woocommerce-photo-reviews' ) ?></td>
                    <td><?php _e( ini_get( 'max_file_uploads' ), 'woocommerce-photo-reviews' ); ?></td>
                    <td><?php if ( ini_get( 'post_max_size' ) > ( absint( ini_get( 'upload_max_filesize' ) ) * ini_get( 'max_file_uploads' ) + 1 ) && ini_get( 'upload_max_filesize' ) > 0 && ini_get( 'max_file_uploads' ) > 0 ) {
							echo '<span class="status-ok">OK</span>';
						} else {
							echo '<span class="status-bad">X</span>';
						} ?></td>
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Post maxsize:', 'woocommerce-photo-reviews' ) ?></td>
                    <td><?php _e( ini_get( 'post_max_size' ), 'woocommerce-photo-reviews' ); ?></td>
                    <td><?php if ( ini_get( 'post_max_size' ) > ( absint( ini_get( 'upload_max_filesize' ) ) * ini_get( 'max_file_uploads' ) + 1 ) && ini_get( 'upload_max_filesize' ) > 0 && ini_get( 'max_file_uploads' ) > 0 ) {
							echo '<span class="status-ok">OK</span>';
						} else {
							echo '<span class="status-bad">X</span>';
						} ?></td>
                </tr>
                <tr>
                    <td colspan="3"><p>
                            <i><?php esc_html_e( 'Post maxsize', 'woocommerce-photo-reviews' ) ?></i> <?php esc_html_e( 'should be greater than', 'woocommerce-photo-reviews' ) ?>
                            <i><?php esc_html_e( 'Max file upload', 'woocommerce-photo-reviews' ) ?></i> <?php esc_html_e( 'plus', 'woocommerce-photo-reviews' ) ?>
                            <i><?php esc_html_e( ' Upload max filesize.', 'woocommerce-photo-reviews' ) ?></i></p>
                    </td>
                </tr>
            </table>
        </div>
		<?php
	}

	public static function get_email_templates( $type = 'wcpr_coupon_email' ) {
		$email_templates = array();
		if ( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::is_email_template_customizer_active() ) {
			$email_templates = viwec_get_emails_list( $type );
		}

		return $email_templates;
	}

	public function settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$coupon_generate = $this->settings->get_params( 'coupon', 'unique_coupon' );
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'WooCommerce Photo Reviews Settings', 'woocommerce-photo-reviews' ); ?></h2>
            <div class="vi-ui styled fluid accordion">
                <div class="title">
                    <i class="dropdown icon"></i>
					<?php esc_html_e( 'Helper', 'woocommerce-photo-reviews' ) ?>
                </div>
                <div class="content">
                    <div class="vi-ui positive message">
                        <ul class="list">
                            <li><?php _e( 'Some related helpful settings about pagination, moderating reviews... can be found in <a target="_blank" href="' . admin_url( "options-discussion.php" ) . '">Discussion Settings</a> and  <a target="_blank" href="' . admin_url( "admin.php" ) . '?page=wc-settings&tab=products">WooCommerce Settings</a>', 'woocommerce-photo-reviews' ) ?></li>
                            <li><?php _e( 'To manually send a review reminder, please go to <a target="_blank" href="' . admin_url( "edit.php" ) . '?post_type=shop_order">Orders</a>.', 'woocommerce-photo-reviews' ) ?></li>
                            <li><?php _e( 'To import reviews from AliExpress, go to <a target="_blank" href="' . admin_url( "edit.php" ) . '?post_type=product">Products</a>.', 'woocommerce-photo-reviews' ) ?></li>
                            <li><?php _e( 'To change Emails design, go to <a target="_blank" href="' . admin_url( "admin.php" ) . '?page=wc-settings&tab=email#woocommerce_email_base_color">WooCommerce Emails Settings</a>.', 'woocommerce-photo-reviews' ) ?></li>
                        </ul>
                    </div>
                </div>
                <div class="title">
                    <i class="dropdown icon"></i>
					<?php esc_html_e( 'Shortcode', 'woocommerce-photo-reviews' ) ?>
                </div>
                <div class="content">
                    <div class="vi-ui positive message">
                        <ul class="list">
                            <li><?php _e( 'Overall rating shortcode <strong>[wc_photo_reviews_overall_rating_html product_id="" overall_rating_enable="on" rating_count_enable="on"]</strong> is used to display overall rating', 'woocommerce-photo-reviews' ) ?></li>
                            <li><?php _e( 'Product rating shortcode <strong>[wc_photo_reviews_rating_html product_id="" rating="" review_count="on"]</strong> is used to display rating of a product', 'woocommerce-photo-reviews' ) ?></li>
                            <li><?php _e( 'Review form shortcode <strong>[woocommerce_photo_reviews_form product_id="" hide_product_details="" hide_product_price="" type="popup" button_position="center"]</strong> can be used for single product page or review page of reminder email.', 'woocommerce-photo-reviews' ) ?></li>
                            <li><?php _e( 'Reviews shortcode is used to display all reviews or specific reviews of some products or categories. Usage with all available arguments: <strong>[wc_photo_reviews_shortcode comments_per_page="12" cols="3" cols_mobile="1" use_single_product="on" cols_gap="" products="" grid_bg_color="" grid_item_bg_color="" grid_item_border_color="" text_color="" star_color="" product_cat="" order="" orderby="comment_date_gmt" show_product="on" filter="on" pagination="on" pagination_ajax="on" pagination_pre="" pagination_next="" loadmore_button="off" filter_default_image="off" filter_default_verified="off" filter_default_rating="" pagination_position="" conditional_tag="" custom_css="" ratings="" mobile="on" style="masonry" masonry_popup="review" enable_box_shadow="on" full_screen_mobile="on" overall_rating="off" rating_count="off" only_images="off" image_popup="below_thumb"]</strong>', 'woocommerce-photo-reviews' ) ?></li>
                            <li><?php _e( 'To learn more about How to use these shortcodes, please read <a target="_blank" href="https://docs.villatheme.com/woocommerce-photo-reviews/#page_section_menu_3142">Docs</a>', 'woocommerce-photo-reviews' ) ?></li>
                        </ul>
                    </div>
                </div>
            </div>
			<?php
			if ( is_plugin_active( 'alidswoo/alidswoo.php' ) ) {
				?>
                <p><?php _e( 'Your site is using AliDropship Woo Plugin, reviews with photos imported by this plugin are not displayed properly with WooCommerce Photo Reviews plugin. <a href="' . add_query_arg( array( 'woocommerce_photo_review_render_reviews_imported_by_alidswoo' => 'update' ) ) . '">Update now</a>' ); ?></p>
				<?php
			}
			?>
            <form action="" method="post" class="vi-ui form">
				<?php wp_nonce_field( 'wcpr_settings_page_save', 'wcpr_nonce_field' ); ?>
                <div class="vi-ui top tabular menu">
                    <div class="item active"
                         data-tab="general"><?php esc_html_e( 'General', 'woocommerce-photo-reviews' ); ?></div>
                    <div class="item"
                         data-tab="photo"><?php esc_html_e( 'Reviews', 'woocommerce-photo-reviews' ); ?></div>
                    <div class="item"
                         data-tab="rating_filter"><?php esc_html_e( 'Rating Counts & Filters', 'woocommerce-photo-reviews' ); ?></div>
                    <div class="item"
                         data-tab="coupon"><?php esc_html_e( 'Coupon', 'woocommerce-photo-reviews' ); ?></div>
                    <div class="item"
                         data-tab="email"><?php esc_html_e( 'Review Reminder', 'woocommerce-photo-reviews' ); ?></div>
                    <div class="item"
                         data-tab="custom_fields"><?php esc_html_e( 'Optional Fields', 'woocommerce-photo-reviews' ); ?></div>
                    <div class="item"
                         data-tab="aliexpress_reviews"><?php esc_html_e( 'AliExpress Reviews', 'woocommerce-photo-reviews' ); ?></div>
                    <div class="item"
                         data-tab="chrome_extension"><?php esc_html_e( 'Chrome Extension', 'woocommerce-photo-reviews' ); ?></div>
                    <div class="item"
                         data-tab="share_reviews"><?php esc_html_e( 'Share reviews', 'woocommerce-photo-reviews' ); ?></div>
                    <div class="item"
                         data-tab="update"><?php esc_html_e( 'Update', 'woocommerce-photo-reviews' ); ?></div>
                </div>
                <div class="vi-ui bottom active tab segment" data-tab="general">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th>
                                <label for="wcpr-enable"><?php esc_html_e( 'Enable', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" name="wcpr-enable"
                                           id="wcpr-enable" <?php checked( $this->settings->get_params( 'enable' ), 'on' ) ?>>
                                    <label></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="wcpr-mobile"><?php esc_html_e( 'Mobile', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" name="wcpr-mobile"
                                           id="wcpr-mobile" <?php checked( $this->settings->get_params( 'mobile' ), 'on' ) ?>>
                                    <label></label>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label for="wcpr_multi_language"><?php esc_html_e( 'Enable Multilingual', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" name="wcpr_multi_language"
                                           id="wcpr_multi_language"
                                           value="1" <?php checked( $this->settings->get_params( 'multi_language' ), 1 ) ?>>
                                    <label><?php esc_html_e( 'Compatible with WPML and Polylang', 'woocommerce-photo-reviews' ); ?></label>
                                </div>
                            </td>
                        </tr>
						<?php
						$upload_dir = wp_upload_dir();
						$basedir    = $upload_dir['basedir'] . '/';
						?>
                        <tr>
                            <th>
                                <label for="user-upload-folder"><?php esc_html_e( 'Customer review photos folder', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui right labeled input fluid">
                                    <label for="user-upload-folder"
                                           class="vi-ui label"><?php esc_html_e( $basedir ) ?></label>
                                    <input type="text" id="user-upload-folder" class="user-upload-folder"
                                           name="user_upload_folder"
                                           value="<?php esc_attr_e( $this->settings->get_params( 'user_upload_folder' ) ) ?>">
                                </div>
                                <p class="description"><?php esc_html_e( 'A Sub folder will be created in your root upload folder to store photos uploaded by customers', 'woocommerce-photo-reviews' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="import-upload-folder"><?php esc_html_e( 'Import review photos folder', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui right labeled input fluid">
                                    <label for="import-upload-folder"
                                           class="vi-ui label"><?php esc_html_e( $basedir ) ?></label>
                                    <input type="text" id="import-upload-folder" class="import-upload-folder"
                                           name="import_upload_folder"
                                           value="<?php esc_attr_e( $this->settings->get_params( 'import_upload_folder' ) ) ?>">
                                </div>

                                <p class="description"><?php esc_html_e( 'A Sub folder will be created in your root upload folder to store photos of reviews imported from AliExpress or imported with CSV', 'woocommerce-photo-reviews' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="vi-ui positive message">
                                    <ul class="list">
                                        <li><?php _e( '<strong>{product_id}</strong> can be used in folder name to refer to ID of product that a review belongs to', 'woocommerce-photo-reviews' ); ?></li>
                                        <li><?php _e( 'If you want to create sub folders inside an other same sub folder, use it like <strong>woocommerce-photo-review/users-upload</strong> for Customer review photos folder and <strong>woocommerce-photo-review/import</strong> for Import review photos folder', 'woocommerce-photo-reviews' ); ?></li>
                                        <li><?php printf( __( 'If "Organize my uploads into month- and year-based folders" option in <a target="_blank" href="%s">Media Settings</a> is checked, month- and year-based folders will be created inside your upload folders', 'woocommerce-photo-reviews' ), admin_url( 'options-media.php' ) ); ?></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label for="user-upload-prefix"><?php esc_html_e( 'Customer review photos prefix', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <input type="text" id="user-upload-prefix" class="user-upload-prefix"
                                       name="user_upload_prefix"
                                       value="<?php esc_attr_e( $this->settings->get_params( 'user_upload_prefix' ) ) ?>">
                                <p class="description"><?php esc_html_e( 'Prefix for photos of reviews uploaded by customers', 'woocommerce-photo-reviews' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="import-upload-prefix"><?php esc_html_e( 'Import review photos prefix', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <input type="text" id="import-upload-prefix" class="import-upload-prefix"
                                       name="import_upload_prefix"
                                       value="<?php esc_attr_e( $this->settings->get_params( 'import_upload_prefix' ) ) ?>">
                                <p class="description"><?php esc_html_e( 'Prefix for photos of reviews imported from AliExpress or CSV', 'woocommerce-photo-reviews' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <p class="description"><?php _e( 'In photos prefix, you can use <strong>{comment_id}</strong> for ID of review that a photo belongs to and <strong>{product_id}</strong> for ID of product that a review belongs to', 'woocommerce-photo-reviews' ); ?></p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="vi-ui bottom tab segment" data-tab="photo">
                    <div class="vi-ui segment">
                        <table class="form-table">
                            <tr>
                                <th>
                                    <label for="photo_reviews_options"><?php esc_html_e( 'Include photos', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox" id="photo_reviews_options"
                                               name="photo_reviews_options" <?php checked( $this->settings->get_params( 'photo', 'enable' ), 'on' ) ?>><label
                                                for="photo_reviews_options"><?php esc_html_e( 'Allow customers to attach photos in their review.', 'woocommerce-photo-reviews' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="restrict_number_of_reviews"><?php esc_html_e( 'Restrict number of reviews', 'woocommerce-photo-reviews' ) ?></label>
                                </th>

                                <td>
									<?php
									$restrict_number_of_reviews = $this->settings->get_params( 'restrict_number_of_reviews' );
									?>
                                    <select class="vi-ui fluid dropdown" id="restrict_number_of_reviews"
                                            name="restrict_number_of_reviews">
                                        <option><?php esc_html_e( 'None', 'woocommerce-photo-reviews' ) ?></option>
                                        <option value="one" <?php selected( $restrict_number_of_reviews, 'one' ) ?>><?php esc_html_e( 'One review no matter the customer bought that product or not', 'woocommerce-photo-reviews' ) ?></option>
                                        <option value="one_verified" <?php selected( $restrict_number_of_reviews, 'one_verified' ) ?>><?php esc_html_e( 'One review and verified owner required(the customer must have bought that product)', 'woocommerce-photo-reviews' ) ?></option>
                                        <option value="orders_count" <?php selected( $restrict_number_of_reviews, 'orders_count' ) ?>><?php esc_html_e( 'By number of times the customer bought that product', 'woocommerce-photo-reviews' ) ?></option>
                                    </select>
                                    <p><?php _e( 'To use this feature, you have to uncheck option \'Reviews can only be left by "verified owners"\' in <a target="_blank" href="admin.php?page=wc-settings&tab=products#woocommerce_review_rating_verification_required">WooCommerce Settings</a>. Verified owner validation will be checked when customers submit reviews', 'woocommerce-photo-reviews' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="allow_empty_comment"><?php esc_html_e( 'Allow empty comment', 'woocommerce-photo-reviews' ) ?></label>
                                </th>

                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox" id="allow_empty_comment" value="1"
                                               name="allow_empty_comment" <?php checked( $this->settings->get_params( 'allow_empty_comment' ), '1' ) ?>><label
                                                for="allow_empty_comment"><?php esc_html_e( 'Allow customers to post reviews with empty content, only rating is required', 'woocommerce-photo-reviews' ) ?></label>
                                    </div>
                                </td>
                            </tr>
							<?php
							$upload_max_filesize = wc_let_to_num( ini_get( 'upload_max_filesize' ) ) / 1024;
							?>
                            <tr>
                                <th>
                                    <label for="image_maxsize"><?php esc_html_e( 'Maximum photo size', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui right labeled input">
                                        <input id="image_maxsize" class="kt-photo-reviews-setting" type="number"
                                               name="image_maxsize" min="0"
                                               max="<?php echo esc_attr( $upload_max_filesize ); ?>"
                                               step="1"
                                               value="<?php echo $this->settings->get_params( 'photo', 'maxsize' ); ?>">
                                        <label class="vi-ui label"><?php printf( esc_html__( 'KB (Max %s KB).', 'woocommerce-photo-reviews' ), $upload_max_filesize ); ?></label>
                                    </div>
                                    <p><?php esc_html_e( 'The maximum size of a single picture can be uploaded.', 'woocommerce-photo-reviews' ) ?></p>

                                </td>

                            </tr>
                            <tr>
                                <th>
                                    <label for="max_file_uploads"><?php esc_html_e( 'Maximum photo quantity', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui right labeled input">
                                        <input id="max_file_uploads" class="kt-photo-reviews-setting" type="number"
                                               name="max_file_uploads" min="1"
                                               max="<?php echo absint( ini_get( 'max_file_uploads' ) ); ?>" step="1"
                                               value="<?php echo $this->settings->get_params( 'photo', 'maxfiles' ); ?>">
                                        <label class="vi-ui label"><?php esc_html_e( 'Maximum value: ' . absint( ini_get( 'max_file_uploads' ) ) . '.', 'woocommerce-photo-reviews' ); ?></label>
                                    </div>
                                    <p><?php esc_html_e( 'The maximum quantity of photos can be uploaded with a review.', 'woocommerce-photo-reviews' ) ?></p>

                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="upload_images_requirement"><?php esc_html_e( 'Upload images requirement', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
									<?php
									$this->print_default_country_flag();
									?>
                                    <input id="upload_images_requirement" type="text" name="upload_images_requirement"
                                           value="<?php echo $this->settings->get_params( 'photo', 'upload_images_requirement' ); ?>">
                                    <p>
										<?php esc_html_e( '{max_files} - Maximum photo quantity', 'woocommerce-photo-reviews' ) ?>
                                    </p>
                                    <p>
										<?php esc_html_e( '{max_size} - Maximum photo size including unit KB', 'woocommerce-photo-reviews' ) ?>
                                    </p>
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$this->print_other_country_flag( 'upload_images_requirement', $value );
											?>
                                            <input id="<?php echo 'upload_images_requirement_' . $value ?>" type="text"
                                                   name="<?php echo 'upload_images_requirement_' . $value ?>"
                                                   value="<?php echo stripslashes( $this->settings->get_params( 'photo', 'upload_images_requirement', $value ) ); ?>">
											<?php
										}
									}
									?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="review_title_enable"><?php esc_html_e( 'Enable review title', 'woocommerce-photo-reviews' ) ?></label>
                                </th>

                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox" id="review_title_enable" value="1"
                                               name="review_title_enable" <?php checked( $this->settings->get_params( 'review_title_enable' ), '1' ) ?>><label
                                                for="review_title_enable"></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="review_title_placeholder"><?php esc_html_e( 'Review title Placeholder', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
									<?php
									$this->print_default_country_flag();
									?>
                                    <input id="review_title_placeholder" type="text" name="review_title_placeholder"
                                           value="<?php echo $this->settings->get_params( 'review_title_placeholder' ); ?>">
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$this->print_other_country_flag( 'review_title_placeholder', $value );
											?>
                                            <input id="<?php echo 'review_title_placeholder_' . $value ?>" type="text"
                                                   name="<?php echo 'review_title_placeholder_' . $value ?>"
                                                   value="<?php echo stripslashes( $this->settings->get_params( 'review_title_placeholder', '', $value ) ); ?>">
											<?php
										}
									}
									?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="thank_you_message"><?php esc_html_e( 'Thank you message', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
									<?php
									$this->print_default_country_flag();
									?>
                                    <input id="thank_you_message" type="text" name="thank_you_message"
                                           value="<?php echo $this->settings->get_params( 'thank_you_message' ); ?>">
                                    <p>
										<?php esc_html_e( 'Show this message after a customer leaves a review', 'woocommerce-photo-reviews' ) ?>
                                    </p>
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$this->print_other_country_flag( 'thank_you_message', $value );
											?>
                                            <input id="<?php echo 'thank_you_message_' . $value ?>" type="text"
                                                   name="<?php echo 'thank_you_message_' . $value ?>"
                                                   value="<?php echo stripslashes( $this->settings->get_params( 'thank_you_message', '', $value ) ); ?>">
											<?php
										}
									}
									?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="thank_you_message_coupon"><?php esc_html_e( 'Thank you message if Coupon', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
									<?php
									$this->print_default_country_flag();
									?>
                                    <input id="thank_you_message_coupon" type="text" name="thank_you_message_coupon"
                                           value="<?php echo $this->settings->get_params( 'thank_you_message_coupon' ); ?>">
                                    <p>
										<?php esc_html_e( 'Show this message after a customer leaves a review and receives a coupon', 'woocommerce-photo-reviews' ) ?>
                                    </p>
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$this->print_other_country_flag( 'thank_you_message_coupon', $value );
											?>
                                            <input id="<?php echo 'thank_you_message_coupon_' . $value ?>" type="text"
                                                   name="<?php echo 'thank_you_message_coupon_' . $value ?>"
                                                   value="<?php echo stripslashes( $this->settings->get_params( 'thank_you_message_coupon', '', $value ) ); ?>">
											<?php
										}
									}
									?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="photo_required"><?php esc_html_e( 'Photo required', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input class="kt-photo-reviews-setting" type="checkbox" id="photo_required"
                                               name="photo_reviews_required"
                                               value="on" <?php if ( 'on' == $this->settings->get_params( 'photo', 'required' ) ) {
											echo 'checked';
										}
										?>>
                                        <label for="photo_required"><?php esc_html_e( 'Reviews must include photo to be uploaded.', 'woocommerce-photo-reviews' ) ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Sort reviews by', 'woocommerce-photo-reviews' ) ?></th>
                                <td>
                                    <div class="grouped fields">


                                        <div class="field">
                                            <div class="vi-ui toggle checkbox">
                                                <input class="kt-photo-reviews-setting" type="radio"
                                                       name="reviews_sort_time" value="1"
                                                       id="reviews_sort_time_new" <?php if ( 1 == $this->settings->get_params( 'photo', 'sort' )['time'] ) {
													echo 'checked';
												}
												?>><label
                                                        for="reviews_sort_time_new"><?php esc_html_e( ' Newest first', 'woocommerce-photo-reviews' ) ?></label>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui toggle checkbox">
                                                <input class="kt-photo-reviews-setting" type="radio"
                                                       name="reviews_sort_time" value="2"
                                                       id="reviews_sort_time_old" <?php if ( 2 == $this->settings->get_params( 'photo', 'sort' )['time'] ) {
													echo 'checked';
												}
												?>><label
                                                        for="reviews_sort_time_old"><?php esc_html_e( ' Oldest first', 'woocommerce-photo-reviews' ) ?></label>
                                            </div>
                                        </div>

                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="review_tab_first"><?php esc_html_e( 'Show review tab first', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input class="review_tab_first" type="checkbox" id="review_tab_first"
                                               name="review_tab_first"
                                               value="on" <?php checked( $this->settings->get_params( 'photo', 'review_tab_first' ), 'on' ) ?>>
                                        <label></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="gdpr_policy"><?php esc_html_e( 'GDPR checkbox', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input class="gdpr_policy" type="checkbox" id="gdpr_policy"
                                               name="gdpr_policy"
                                               value="on" <?php checked( $this->settings->get_params( 'photo', 'gdpr' ), 'on' ) ?>>
                                        <label></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="gdpr_message"><?php esc_html_e( 'GDPR message', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
									<?php
									$this->print_default_country_flag();
									wp_editor( stripslashes( $this->settings->get_params( 'photo', 'gdpr_message' ) ), 'gdpr_message', array(
										'editor_height' => 300,
										'media_buttons' => true
									) );
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$this->print_other_country_flag( 'gdpr_message', $value );
											wp_editor( stripslashes( $this->settings->get_params( 'photo', 'gdpr_message', $value ) ), 'gdpr_message_' . $value, array(
												'editor_height' => 300,
												'media_buttons' => true
											) );
										}
									}
									?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="masonry_star_color"><?php esc_html_e( 'Rating stars color', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" class="color-picker" id="masonry_star_color"
                                           name="masonry_star_color"
                                           value="<?php echo $this->settings->get_params( 'photo', 'star_color' ); ?>"
                                           style="background-color: <?php echo $this->settings->get_params( 'photo', 'star_color' ); ?>;">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="wcpr_hide_name"><?php esc_html_e( 'Hide name', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox" name="wcpr_hide_name"
                                               id="wcpr_hide_name"
                                               value="on" <?php checked( $this->settings->get_params( 'photo', 'hide_name' ), 'on' ) ?>>
                                        <label><?php esc_html_e( 'Only display first letter of each word in author\'name. Eg: "Woo Photo Reviews" becomes "W** P**** R******"', 'woocommerce-photo-reviews' ); ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="verified-type"><?php esc_html_e( 'Verified owner', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <div class="equal width fields">
                                        <div class="field verified-type-wrap">
                                            <div>
                                                <select name="verified_type" id="verified-type"
                                                        class="vi-ui fluid dropdown">
                                                    <option value="default" <?php selected( $this->settings->get_params( 'photo', 'verified' ), 'default' ) ?>><?php esc_html_e( 'Default', 'woocommerce-photo-reviews' ); ?></option>
                                                    <option value="text" <?php selected( $this->settings->get_params( 'photo', 'verified' ), 'text' ) ?>><?php esc_html_e( 'Text', 'woocommerce-photo-reviews' ); ?></option>
                                                    <option value="badge" <?php selected( $this->settings->get_params( 'photo', 'verified' ), 'badge' ) ?>><?php esc_html_e( 'Badge', 'woocommerce-photo-reviews' ); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <input type="text" name="verified_text" class="verified-text"
                                                   placeholder="<?php esc_html_e( 'Enter text here', 'woocommerce-photo-reviews' ) ?>"
                                                   value="<?php echo $this->settings->get_params( 'photo', 'verified_text' ); ?>"
                                                   style="<?php if ( $this->settings->get_params( 'photo', 'verified' ) !== "text" ) {
												       echo 'display:none';
											       } ?>">
											<?php
											$badges = [
												'woocommerce-photo-reviews-badge-tick-5',
												'woocommerce-photo-reviews-badge-check-box',
												'woocommerce-photo-reviews-badge-check-1',
												'woocommerce-photo-reviews-badge-check',
												'woocommerce-photo-reviews-badge-checked-1',
												'woocommerce-photo-reviews-badge-check-square',
												'woocommerce-photo-reviews-badge-tick-3',
												'woocommerce-photo-reviews-badge-check-mark',
												'woocommerce-photo-reviews-badge-tick-2',
												'woocommerce-photo-reviews-badge-check-2',
												'woocommerce-photo-reviews-badge-tick',
												'woocommerce-photo-reviews-badge-success',
												'woocommerce-photo-reviews-badge-round-done-button',
												'woocommerce-photo-reviews-badge-checked-2',
												'woocommerce-photo-reviews-badge-tick-inside-circle',
												'woocommerce-photo-reviews-badge-tick-4',
												'woocommerce-photo-reviews-badge-tick-1',
												'woocommerce-photo-reviews-badge-checked',
												'woocommerce-photo-reviews-badge-check-4',
												'woocommerce-photo-reviews-badge-check-3',
											];
											?>

                                            <div class="wcpr-verified-badge-wrap-wrap"
                                                 style=" <?php if ( $this->settings->get_params( 'photo', 'verified' ) !== "badge" ) {
												     echo 'display:none';
											     } ?>">
                                                <input type="hidden" name="verified_badge"
                                                       value="<?php echo $this->settings->get_params( 'photo', 'verified_badge' ); ?>">
												<?php
												foreach ( $badges as $badge ) {
													?>
                                                    <span class="wcpr-verified-badge-wrap <?php echo $badge;
													if ( $this->settings->get_params( 'photo', 'verified_badge' ) === $badge ) {
														echo ' wcpr-verified-active-badge';
													} ?>" data-class_name="<?php echo $badge; ?>"></span>
													<?php
												}
												?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <input type="text" class="color-picker verified-color" name="verified_color"
                                               value="<?php echo $this->settings->get_params( 'photo', 'verified_color' ); ?>"
                                               placeholder="<?php esc_html_e( 'Verified owner color', 'woocommerce-photo-reviews' ) ?>"
                                               style="background-color: <?php echo $this->settings->get_params( 'photo', 'verified_color' ); ?>;">
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="vi-ui segment">
                        <table class="form-table">
                            <!--image caption options-->
                            <tr>
                                <th>
                                    <label for="image_caption_enable"><?php esc_html_e( 'Show image caption', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox" name="image_caption_enable" id="image_caption_enable"
                                               value="1" <?php checked( $this->settings->get_params( 'image_caption_enable' ), '1' ) ?>>
                                        <label><?php esc_html_e( 'Let your customer add caption for their review images and show it in reviews', 'woocommerce-photo-reviews' ); ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="image_caption_color"><?php esc_html_e( 'Image caption text color', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" class="color-picker" id="image_caption_color"
                                           name="image_caption_color"
                                           value="<?php echo $this->settings->get_params( 'image_caption_color' ); ?>"
                                           style="background-color: <?php echo $this->settings->get_params( 'image_caption_color' ); ?>;">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="image_caption_bg_color"><?php esc_html_e( 'Image caption background color', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <input data-alpha="true" type="text" class="color-picker"
                                           id="image_caption_bg_color"
                                           name="image_caption_bg_color"
                                           value="<?php echo $this->settings->get_params( 'image_caption_bg_color' ); ?>"
                                           style="background-color: <?php echo $this->settings->get_params( 'image_caption_bg_color' ); ?>;">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="image_caption_font_size"><?php esc_html_e( 'Image caption font size(px)', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <input type="number" name="image_caption_font_size" id="image_caption_font_size"
                                           min="0"
                                           value="<?php echo $this->settings->get_params( 'image_caption_font_size' ); ?>">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="vi-ui segment">
                        <table class="form-table">
                            <!--Helpful button-->
                            <tr>
                                <th>
                                    <label for="helpful_button_enable"><?php esc_html_e( 'Helpful buttons', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox" name="helpful_button_enable" id="helpful_button_enable"
                                               value="1" <?php checked( $this->settings->get_params( 'photo', 'helpful_button_enable' ), '1' ) ?>>
                                        <label><?php esc_html_e( 'Show up-vote/down-vote buttons in customer reviews', 'woocommerce-photo-reviews' ); ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="helpful_button_title"><?php esc_html_e( 'Helpful buttons title', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="helpful_button_title" id="helpful_button_title"
                                           value="<?php esc_attr_e( stripslashes( $this->settings->get_params( 'photo', 'helpful_button_title' ) ) ) ?>">
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$this->print_other_country_flag( 'helpful_button_title', $value );
											?>
                                            <input id="<?php esc_attr_e( 'helpful_button_title_' . $value ) ?>"
                                                   type="text"
                                                   name="<?php esc_attr_e( 'helpful_button_title_' . $value ) ?>"
                                                   value="<?php esc_attr_e( stripslashes( $this->settings->get_params( 'photo', 'helpful_button_title', $value ) ) ); ?>">
											<?php
										}
									}
									?>
                                </td>
                            </tr>
                        </table>
                    </div>
					<?php
					$frontend_style        = $this->settings->get_params( 'photo', 'display' );
					$masonry_options_class = 'masonry-options';
					$default_options_class = 'default-options';
					if ( $frontend_style == 2 ) {
						$masonry_options_class .= ' wcpr-hidden-items';
					} else {
						$default_options_class .= ' wcpr-hidden-items';
					}
					?>
                    <div class="vi-ui segment">
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Front-end style', 'woocommerce-photo-reviews' ) ?></th>
                                <td>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <div class="vi-ui toggle checkbox">
                                                <input class="kt-photo-reviews-setting" type="radio"
                                                       name="reviews_display" value="1"
                                                       id="reviews_display1" <?php checked( $frontend_style, '1' ) ?>><label
                                                        for="reviews_display1"><?php esc_html_e( ' Grid(masonry).', 'woocommerce-photo-reviews' ) ?></label>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui toggle checkbox">
                                                <input class="kt-photo-reviews-setting" type="radio"
                                                       name="reviews_display" value="2"
                                                       id="reviews_display2" <?php checked( $frontend_style, '2' ) ?>><label
                                                        for="reviews_display2"><?php esc_html_e( ' Normal.', 'woocommerce-photo-reviews' ) ?></label>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
							<?php
							$masonry_popup = $this->settings->get_params( 'photo', 'masonry_popup' );
							?>
                            <tr class="<?php esc_attr_e( $masonry_options_class ); ?>">
                                <th>
                                    <label for="masonry_popup"><?php esc_html_e( 'Popup type', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <select name="masonry_popup" id="masonry_popup"
                                            class="vi-ui fluid dropdown">
                                        <option value="review" <?php selected( $masonry_popup, 'review' ) ?>><?php esc_html_e( 'Whole review', 'woocommerce-photo-reviews' ); ?></option>
                                        <option value="image" <?php selected( $masonry_popup, 'image' ) ?>><?php esc_html_e( 'Only image', 'woocommerce-photo-reviews' ); ?></option>
                                        <option value="off" <?php selected( $masonry_popup, 'off' ) ?>><?php esc_html_e( 'Off', 'woocommerce-photo-reviews' ); ?></option>
                                    </select>
                                </td>
                            </tr>
							<?php
							$image_popup = $this->settings->get_params( 'photo', 'image_popup' );
							?>
                            <tr class="<?php esc_attr_e( $default_options_class ); ?>">
                                <th>
                                    <label for="image_popup"><?php esc_html_e( 'Image popup', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <select name="image_popup" id="image_popup"
                                            class="vi-ui fluid dropdown">
                                        <option value="below_thumb" <?php selected( $image_popup, 'review' ) ?>><?php esc_html_e( 'Below thumbnails', 'woocommerce-photo-reviews' ); ?></option>
                                        <option value="lightbox" <?php selected( $image_popup, 'image' ) ?>><?php esc_html_e( 'Lightbox', 'woocommerce-photo-reviews' ); ?></option>
                                    </select>
                                </td>
                            </tr>
							<?php
							$pagination_ajax = $this->settings->get_params( 'pagination_ajax' );
							$loadmore_button = $this->settings->get_params( 'loadmore_button' );
							?>
                            <tr>
                                <th>
                                    <label for="wcpr-pagination-ajax"><?php esc_html_e( 'Ajax Pagination & Filter', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox" name="wcpr_pagination_ajax" id="wcpr-pagination-ajax"
                                               value="1" <?php checked( $pagination_ajax, '1' ) ?>><label></label>
                                    </div>
                                    <p><?php esc_html_e( 'Do not reload page when customers select other reviews page or select a filter', 'woocommerce-photo-reviews' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="wcpr-loadmore_button"><?php esc_html_e( 'Enable load-more button', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox" name="wcpr_loadmore_button" id="wcpr-loadmore_button"
                                               value="1" <?php checked( $loadmore_button, '1' ) ?>><label></label>
                                    </div>
                                </td>
                            </tr>
                            <tr class="<?php esc_attr_e( $masonry_options_class ); ?>">
                                <th>
                                    <label for="max_content_length"><?php esc_html_e( 'Max content length', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <input class="max_content_length" type="number" min="0"
                                           id="max_content_length"
                                           value="<?php echo esc_attr( $this->settings->get_params( 'photo', 'max_content_length' ) ) ?>"
                                           name="max_content_length">
                                    <p><?php esc_html_e( 'Button "More" will show if a review content length is greater than this value so that customers can click the button to load full review content', 'woocommerce-photo-reviews' ); ?></p>
                                </td>
                            </tr>
                            <tr class="<?php esc_attr_e( $masonry_options_class ); ?>">
                                <th>
                                    <label for="full_screen_mobile"><?php esc_html_e( 'Display popup in fullscreen on mobile', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input class="full_screen_mobile" type="checkbox"
                                               id="full_screen_mobile" value="1"
                                               name="full_screen_mobile" <?php checked( $this->settings->get_params( 'photo', 'full_screen_mobile' ), '1' ) ?>>
                                        <label></label>
                                    </div>
                                </td>
                            </tr>
                            <tr class="<?php esc_attr_e( $masonry_options_class ); ?>">
                                <th>
                                    <label for="single_product_summary"><?php esc_html_e( 'Display product summary on masonry popup', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input class="single_product_summary" type="checkbox"
                                               id="single_product_summary"
                                               name="single_product_summary"
                                               value="on" <?php checked( $this->settings->get_params( 'photo', 'single_product_summary' ), 'on' ) ?>>
                                        <label></label>
                                    </div>
                                </td>
                            </tr>
                            <tr class="<?php esc_attr_e( $masonry_options_class ); ?>">
                                <th>
                                    <label for="masonry_col_num"><?php esc_html_e( 'Number of columns', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <div class="vi-ui right labeled input">
                                                <input type="number" min="2" max="5" id="masonry_col_num"
                                                       name="masonry_col_num"
                                                       value="<?php echo $this->settings->get_params( 'photo', 'col_num' ); ?>">
                                                <label class="vi-ui label"><?php esc_html_e( 'Desktop', 'woocommerce-photo-reviews' ); ?></label>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <div class="vi-ui right labeled input">
                                                <input type="number" min="1" max="3" id="masonry_col_num_mobile"
                                                       name="masonry_col_num_mobile"
                                                       value="<?php echo $this->settings->get_params( 'photo', 'col_num_mobile' ); ?>">
                                                <label class="vi-ui label"><?php esc_html_e( 'Mobile', 'woocommerce-photo-reviews' ); ?></label>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="<?php esc_attr_e( $masonry_options_class ); ?>">
                                <th>
                                    <label for="masonry_grid_bg"><?php esc_html_e( 'Grid background color', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" class="color-picker" id="masonry_grid_bg"
                                           name="masonry_grid_bg"
                                           value="<?php echo $this->settings->get_params( 'photo', 'grid_bg' ); ?>"
                                           style="background-color: <?php echo $this->settings->get_params( 'photo', 'grid_bg' ); ?>;">
                                </td>
                            </tr>
                            <tr class="<?php esc_attr_e( $masonry_options_class ); ?>">
                                <th>
                                    <label for="masonry_grid_item_bg"><?php esc_html_e( 'Grid item background color', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" class="color-picker" id="masonry_grid_item_bg"
                                           name="masonry_grid_item_bg"
                                           value="<?php echo $this->settings->get_params( 'photo', 'grid_item_bg' ); ?>"
                                           style="background-color: <?php echo $this->settings->get_params( 'photo', 'grid_item_bg' ); ?>;">
                                </td>
                            </tr>
                            <tr class="<?php esc_attr_e( $masonry_options_class ); ?>">
                                <th>
                                    <label for="masonry_grid_item_border_color"><?php esc_html_e( 'Grid item border color', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" class="color-picker" id="masonry_grid_item_border_color"
                                           name="masonry_grid_item_border_color"
                                           value="<?php echo $this->settings->get_params( 'photo', 'grid_item_border_color' ); ?>"
                                           style="background-color: <?php echo $this->settings->get_params( 'photo', 'grid_item_border_color' ); ?>;">
                                </td>
                            </tr>
                            <tr class="<?php esc_attr_e( $masonry_options_class ); ?>">
                                <th>
                                    <label for="masonry_comment_text_color"><?php esc_html_e( 'Review text color', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" class="color-picker" id="masonry_comment_text_color"
                                           name="masonry_comment_text_color"
                                           value="<?php echo $this->settings->get_params( 'photo', 'comment_text_color' ); ?>"
                                           style="background-color: <?php echo $this->settings->get_params( 'photo', 'comment_text_color' ); ?>;">
                                </td>
                            </tr>
                            <tr class="<?php esc_attr_e( $masonry_options_class ); ?>">
                                <th>
                                    <label for="show_review_date"><?php esc_html_e( 'Show review date', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox" name="show_review_date" id="show_review_date"
                                               value="1" <?php checked( $this->settings->get_params( 'photo', 'show_review_date' ), '1' ) ?>><label></label>
                                    </div>
                                </td>
                            </tr>
                            <tr class="<?php esc_attr_e( $masonry_options_class ); ?>">
                                <th>
                                    <label for="custom_review_date_format"><?php esc_html_e( 'Custom review date format', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" class="" id="custom_review_date_format"
                                           name="custom_review_date_format"
                                           value="<?php echo $this->settings->get_params( 'photo', 'custom_review_date_format' ); ?>">
                                    <p><?php esc_html_e( 'The review use the same date format of your WP settings. If you want to use a different format for it, enter it here.', 'woocommerce-photo-reviews' ); ?></p>
                                    <p><a href="https://wordpress.org/support/article/formatting-date-and-time/"
                                          target="_blank"><?php esc_html_e( 'Documentation on date and time formatting.', 'woocommerce-photo-reviews' ); ?></a>
                                    </p>
                                </td>
                            </tr>

                            <tr class="<?php esc_attr_e( $masonry_options_class ); ?>">
                                <th>
                                    <label for="enable_box_shadow"><?php esc_html_e( 'Enable box shadow', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="checkbox" name="enable_box_shadow" id="enable_box_shadow"
                                               value="1" <?php checked( $this->settings->get_params( 'photo', 'enable_box_shadow' ), '1' ) ?>><label></label>
                                    </div>
                                </td>
                            </tr>
                            <tr class="<?php esc_attr_e( $default_options_class ); ?>">
                                <th>
                                    <label for="wcpr-reviews-container"><?php esc_html_e( 'Reviews container', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="wcpr_reviews_container" id="wcpr-reviews-container"
                                           value="<?php echo $this->settings->get_params( 'reviews_container' ) ?>">
                                    <p><?php esc_html_e( 'Leave this field blank if ajax load more reviews works properly.', 'woocommerce-photo-reviews' ); ?></p>
                                    <p><?php esc_html_e( 'If nothing happens when you click on button load more, product review template have been customized on your site. Just find the specific class or id of the closest container-the closest element(usually an "ul" or "ol" element) that wraps all reviews. Use dot(.) if it\'s a class name and use hash(#) if it\'s an id.', 'woocommerce-photo-reviews' ); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="vi-ui segment">
                        <table class="form-table">
                            <tr>
                                <th>
                                    <label for="wcpr-reviews-anchor-link"><?php esc_html_e( 'Reviews anchor link', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="wcpr_reviews_anchor_link" id="wcpr-reviews-anchor-link"
                                           value="<?php echo htmlentities( $this->settings->get_params( 'reviews_anchor_link' ) ) ?>">
                                    <p><?php esc_html_e( 'This is the anchor link to your reviews form. Enter without a hash(#). This will be linked after product links in reviews reminder or when customers click on a filter on frontend.', 'woocommerce-photo-reviews' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="photo-reviews-css"><?php esc_html_e( 'Custom CSS', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
                                <textarea name="photo-reviews-css"
                                          id="photo-reviews-css"><?php echo $this->settings->get_params( 'photo', 'custom_css' ); ?></textarea>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="vi-ui bottom tab segment" data-tab="rating_filter">
                    <table class="form-table">
                        <tr>
                            <th>
                                <label for="ratings_count"><?php esc_html_e( 'Ratings count', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input class="kt-photo-reviews-setting" type="checkbox" id="ratings_count"
                                           name="ratings_count"
                                           value="on" <?php checked( $this->settings->get_params( 'photo', 'rating_count' ), 'on' ) ?>><label></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="hide_rating_count_if_empty"><?php esc_html_e( 'Hide if empty', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input class="kt-photo-reviews-setting" type="checkbox"
                                           id="hide_rating_count_if_empty"
                                           name="hide_rating_count_if_empty"
                                           value="1" <?php checked( $this->settings->get_params( 'photo', 'hide_rating_count_if_empty' ), '1' ) ?>><label><?php esc_html_e( 'Do not show Rating count & Overall rating if a product does not have any reviews', 'woocommerce-photo-reviews' ) ?></label>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label for="overall_rating"><?php esc_html_e( 'Overall rating', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input class="kt-photo-reviews-setting" type="checkbox" id="overall_rating"
                                           name="overall_rating"
                                           value="on" <?php checked( $this->settings->get_params( 'photo', 'overall_rating' ), 'on' ) ?>><label></label>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label for="rating-count-bar-color"><?php esc_html_e( 'Ratings count bar color', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <input type="text" class="color-picker" id="rating-count-bar-color"
                                       name="rating-count-bar-color"
                                       value="<?php echo $this->settings->get_params( 'photo', 'rating_count_bar_color' ); ?>"
                                       style="background-color: <?php echo $this->settings->get_params( 'photo', 'rating_count_bar_color' ); ?>;">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="vi-ui positive message">
									<?php esc_html_e( 'To set default active filters, Ajax pagination must be enabled', 'woocommerce-photo-reviews' ) ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="filter-enable"><?php esc_html_e( 'Filters', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input class="kt-photo-reviews-setting" type="checkbox" id="filter-enable"
                                           name="filter-enable"
                                           value="on" <?php if ( 'on' == $this->settings->get_params( 'photo', 'filter' )['enable'] ) {
										echo 'checked';
									} ?>>
                                    <label></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="hide_filters_if_empty"><?php esc_html_e( 'Hide if empty', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input class="kt-photo-reviews-setting" type="checkbox" id="hide_filters_if_empty"
                                           name="hide_filters_if_empty"
                                           value="1" <?php checked( $this->settings->get_params( 'photo', 'hide_filters_if_empty' ), '1' ) ?>><label><?php esc_html_e( 'Do not show Filter if a product does not have any reviews', 'woocommerce-photo-reviews' ) ?></label>
                                </div>
                            </td>
                        </tr>
						<?php
						$default_filter_class = 'wcpr-default-filters';
						if ( ! $pagination_ajax ) {
							$default_filter_class .= ' wcpr-hidden-items';
						}
						?>
                        <tr class="<?php echo esc_attr( $default_filter_class ) ?>">
                            <th>
                                <label for="wcpr-filter-default-image"><?php esc_html_e( 'Select Image filter by default', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" name="filter_default_image"
                                           id="wcpr-filter-default-image"
                                           value="1" <?php checked( $this->settings->get_params( 'filter_default_image' ), '1' ) ?>><label><?php esc_html_e( 'Enable', 'woocommerce-photo-reviews' ); ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr class="<?php echo esc_attr( $default_filter_class ) ?>">
                            <th>
                                <label for="wcpr-filter-default-verified"><?php esc_html_e( 'Select Verified filter by default', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" name="filter_default_verified"
                                           id="wcpr-filter-default-verified"
                                           value="1" <?php checked( $this->settings->get_params( 'filter_default_verified' ), '1' ) ?>><label><?php esc_html_e( 'Enable', 'woocommerce-photo-reviews' ); ?></label>
                                </div>
                            </td>
                        </tr>
						<?php
						$filter_default_rating = $this->settings->get_params( 'filter_default_rating' );
						?>
                        <tr class="<?php echo esc_attr( $default_filter_class ) ?>">
                            <th>
                                <label for="wcpr-filter-default-rating"><?php esc_html_e( 'Select Rating by default', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <select name="filter_default_rating" id="wcpr-filter-default-rating"
                                        class="wcpr-filter-default-rating vi-ui dropdown">
                                    <option value="" <?php selected( $filter_default_rating, '' ) ?>><?php esc_html_e( 'All ratings', 'woocommerce-photo-reviews' ); ?></option>
                                    <option value="1" <?php selected( $filter_default_rating, '1' ) ?>><?php esc_html_e( '1 Star', 'woocommerce-photo-reviews' ); ?></option>
                                    <option value="2" <?php selected( $filter_default_rating, '2' ) ?>><?php esc_html_e( '2 Star', 'woocommerce-photo-reviews' ); ?></option>
                                    <option value="3" <?php selected( $filter_default_rating, '3' ) ?>><?php esc_html_e( '3 Star', 'woocommerce-photo-reviews' ); ?></option>
                                    <option value="4" <?php selected( $filter_default_rating, '4' ) ?>><?php esc_html_e( '4 Star', 'woocommerce-photo-reviews' ); ?></option>
                                    <option value="5" <?php selected( $filter_default_rating, '5' ) ?>><?php esc_html_e( '5 Star', 'woocommerce-photo-reviews' ); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="filter-area-border-color"><?php esc_html_e( 'Filter area border color', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td colspan="4">
                                <input type="text" class="color-picker" id="filter-area-border-color"
                                       name="filter-area-border-color"
                                       value="<?php echo isset( $this->settings->get_params( 'photo', 'filter' )['area_border_color'] ) ? $this->settings->get_params( 'photo', 'filter' )['area_border_color'] : '' ?>"
                                       style="background-color: <?php echo isset( $this->settings->get_params( 'photo', 'filter' )['area_border_color'] ) ? $this->settings->get_params( 'photo', 'filter' )['area_border_color'] : '' ?>;">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="filter-area-bg-color"><?php esc_html_e( 'Filter area backgroud color', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td colspan="4">
                                <input name="filter-area-bg-color" id="filter-area-bg-color" type="text"
                                       class="color-picker"
                                       value="<?php echo isset( $this->settings->get_params( 'photo', 'filter' )['area_bg_color'] ) ? $this->settings->get_params( 'photo', 'filter' )['area_bg_color'] : '' ?>"
                                       style="background-color: <?php echo isset( $this->settings->get_params( 'photo', 'filter' )['area_bg_color'] ) ? $this->settings->get_params( 'photo', 'filter' )['area_bg_color'] : '' ?>;"/>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label for="filter-button-border-color"><?php esc_html_e( 'Filter buttons border color', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td colspan="4">
                                <input type="text" class="color-picker" id="filter-button-border-color"
                                       name="filter-button-border-color"
                                       value="<?php echo isset( $this->settings->get_params( 'photo', 'filter' )['button_border_color'] ) ? $this->settings->get_params( 'photo', 'filter' )['button_border_color'] : '' ?>"
                                       style="background-color: <?php echo isset( $this->settings->get_params( 'photo', 'filter' )['button_border_color'] ) ? $this->settings->get_params( 'photo', 'filter' )['button_border_color'] : '' ?>;">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="filter-button-color"><?php esc_html_e( 'Filter button color', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td colspan="4">
                                <input name="filter-button-color" id="filter-button-color" type="text"
                                       class="color-picker"
                                       value="<?php echo isset( $this->settings->get_params( 'photo', 'filter' )['button_color'] ) ? $this->settings->get_params( 'photo', 'filter' )['button_color'] : '' ?>"
                                       style="background-color: <?php echo isset( $this->settings->get_params( 'photo', 'filter' )['button_color'] ) ? $this->settings->get_params( 'photo', 'filter' )['button_color'] : '' ?>;"/>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="filter-button-bg-color"><?php esc_html_e( 'Filter button background color', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td colspan="4">
                                <input name="filter-button-bg-color" id="filter-button-bg-color" type="text"
                                       class="color-picker"
                                       value="<?php echo isset( $this->settings->get_params( 'photo', 'filter' )['button_bg_color'] ) ? $this->settings->get_params( 'photo', 'filter' )['button_bg_color'] : '' ?>"
                                       style="background-color: <?php echo isset( $this->settings->get_params( 'photo', 'filter' )['button_bg_color'] ) ? $this->settings->get_params( 'photo', 'filter' )['button_bg_color'] : '' ?>;"/>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="vi-ui bottom tab segment" data-tab="coupon">

                    <table class="form-table">
                        <tr>
                            <th>
                                <label for="kt_coupons_enable"><?php esc_html_e( 'Coupon for reviews', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" id="kt_coupons_enable" name="kt_coupons_enable"
                                           value="on"<?php if ( 'on' == $this->settings->get_params( 'coupon', 'enable' ) ) {
										echo 'checked';
									}
									?>><label
                                            for="kt_coupons_enable"><?php esc_html_e( 'Send coupon to customers when their reviews are approved', 'woocommerce-photo-reviews' ) ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="kt_coupons_if_register"><?php esc_html_e( 'Registered-account email is required', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
									<?php
									$coupons_require = $this->settings->get_params( 'coupon', 'require' );
									?>
                                    <input id="kt_coupons_if_register" type="checkbox"
                                           name="kt_coupons_if_register"
                                           value="on"<?php checked( isset( $coupons_require['register'] ) ? $coupons_require['register'] : '', 'on' ) ?>>
                                    <label for="kt_coupons_if_register"><?php esc_html_e( 'Only send coupons if author\'s email is registered an account', 'woocommerce-photo-reviews' ) ?></label>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                <label for="kt_coupons_if_photo"><?php esc_html_e( 'Photo is required', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="kt_coupons_if_photo" type="checkbox"
                                           name="kt_coupons_if_photo"
                                           value="on"<?php checked( $coupons_require['photo'], 'on' ) ?>>
                                    <label for="kt_coupons_if_photo"><?php esc_html_e( 'Only send coupons for reviews including photos', 'woocommerce-photo-reviews' ) ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="kt_coupons_if_min_rating"><?php esc_html_e( 'Minimum required rating', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui right labeled input">
                                    <input id="kt_coupons_if_min_rating" type="number" name="kt_coupons_if_min_rating"
                                           placeholder="0" min="0"
                                           max="5" step="1"
                                           value="<?php echo isset( $coupons_require['min_rating'] ) ? $coupons_require['min_rating'] : '' ?>">
                                    <label for="kt_coupons_if_min_rating"
                                           class="vi-ui label"><?php esc_html_e( 'Star(s)', 'woocommerce-photo-reviews' ) ?></label>
                                </div>
                                <p><?php esc_html_e( 'Only send coupons for reviews if rating is equal or greater than this value', 'woocommerce-photo-reviews' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="kt_coupons_if_verified"><?php esc_html_e( 'Verified owner is required', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="kt_coupons_if_verified" type="checkbox"
                                           name="kt_coupons_if_verified"
                                           value="on"<?php checked( $coupons_require['owner'], 'on' ) ?>><label
                                            for="kt_coupons_if_verified"><?php esc_html_e( 'Only send coupon for reviews from purchased customers.', 'woocommerce-photo-reviews' ) ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="review_form_description"><?php esc_html_e( 'Review form description', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
								<?php
								$this->print_default_country_flag();
								?>
                                <input id="review_form_description" type="text" name="review_form_description"
                                       value="<?php echo $this->settings->get_params( 'coupon', 'form_title' ); ?>">
								<?php
								if ( count( $this->languages ) ) {
									foreach ( $this->languages as $key => $value ) {
										$this->print_other_country_flag( 'review_form_description', $value );
										?>
                                        <input id="<?php echo 'review_form_description_' . $value ?>" type="text"
                                               name="<?php echo 'review_form_description_' . $value ?>"
                                               value="<?php echo stripslashes( $this->settings->get_params( 'coupon', 'form_title', $value ) ); ?>">
										<?php
									}
								}
								?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="kt_products_gen_coupon"><?php esc_html_e( 'Required products', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <select id="kt_products_gen_coupon"
                                        data-placeholder="<?php esc_html_e( 'Please Fill In Your Product Title', 'woocommerce-photo-reviews' ) ?>"
                                        name="kt_products_gen_coupon[]" multiple="multiple"
                                        class="product-search select2-selection--multiple">
									<?php
									$products_gen_coupon = $this->settings->get_params( 'coupon', 'products_gene' );
									if ( is_array( $products_gen_coupon ) && count( $products_gen_coupon ) ) {
										foreach ( $products_gen_coupon as $pgc ) {
											$product = wc_get_product( $pgc );
											if ( $product ) {
												?>
                                                <option selected
                                                        value="<?php echo $pgc ?>"><?php echo $product->get_title() ?></option>
												<?php
											}
										}
									}
									?>
                                </select>
                                <span class="wcpr-select-all-product vi-ui button"><?php esc_html_e( 'Select all', 'woocommerce-photo-reviews' ) ?></span>
                                <span class="wcpr-clear-all-product vi-ui negative button"><?php esc_html_e( 'Clear all', 'woocommerce-photo-reviews' ) ?></span>
                                <p><?php esc_html_e( 'Only reviews on selected products can receive coupons. Leave blank to apply for all products', 'woocommerce-photo-reviews' ) ?></p>

                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="kt_excluded_products_gen_coupon"><?php esc_html_e( 'Exclude products to give coupon', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <select id="kt_excluded_products_gen_coupon"
                                        data-placeholder="<?php esc_html_e( 'Please Fill In Your Product Title', 'woocommerce-photo-reviews' ) ?>"
                                        name="kt_excluded_products_gen_coupon[]" multiple="multiple"
                                        class="product-search select2-selection--multiple">
									<?php
									$excluded_products_gen_coupon = $this->settings->get_params( 'coupon', 'excluded_products_gene' );
									if ( is_array( $excluded_products_gen_coupon ) && count( $excluded_products_gen_coupon ) ) {
										foreach ( $excluded_products_gen_coupon as $pgc ) {
											$product = wc_get_product( $pgc );
											if ( $product ) {
												?>
                                                <option selected
                                                        value="<?php echo $pgc ?>"><?php echo $product->get_title() ?></option>
												<?php
											}
										}
									}
									?>
                                </select>
                                <span class="wcpr-select-all-product vi-ui button"><?php esc_html_e( 'Select all', 'woocommerce-photo-reviews' ) ?></span>
                                <span class="wcpr-clear-all-product vi-ui negative button"><?php esc_html_e( 'Clear all', 'woocommerce-photo-reviews' ) ?></span>
                                <p><?php esc_html_e( 'Reviews on these products will not receive coupon', 'woocommerce-photo-reviews' ) ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="kt_categories_gen_coupon"><?php esc_html_e( 'Required categories', 'woocommerce-photo-reviews' ) ?></label>

                            </th>
                            <td>
                                <select id="kt_categories_gen_coupon"
                                        data-placeholder="<?php esc_html_e( 'Please enter category title', 'woocommerce-photo-reviews' ) ?>"
                                        name="kt_categories_gen_coupon[]" multiple="multiple"
                                        class="category-search select2-selection--multiple">
									<?php
									$categories_gen_coupon = $this->settings->get_params( 'coupon', 'categories_gene' );
									if ( is_array( $categories_gen_coupon ) && count( $categories_gen_coupon ) ) {
										foreach ( $categories_gen_coupon as $category_id ) {
											$category = get_term( $category_id );
											if ( $category ) {
												?>
                                                <option value="<?php echo $category_id ?>"
                                                        selected><?php echo $category->name; ?></option>
												<?php
											}

										}
									}
									?>
                                </select><?php esc_html_e( 'Only reviews on products in these categories can receive coupon', 'woocommerce-photo-reviews' ) ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="kt_excluded_categories_gen_coupon"><?php esc_html_e( 'Exclude categories to give coupon', 'woocommerce-photo-reviews' ) ?></label>

                            </th>
                            <td>
                                <select id="kt_excluded_categories_gen_coupon"
                                        data-placeholder="<?php esc_html_e( 'Please enter category title', 'woocommerce-photo-reviews' ) ?>"
                                        name="kt_excluded_categories_gen_coupon[]" multiple="multiple"
                                        class="category-search select2-selection--multiple">
									<?php
									$excluded_categories_gen_coupon = $this->settings->get_params( 'coupon', 'excluded_categories_gene' );
									if ( is_array( $excluded_categories_gen_coupon ) && count( $excluded_categories_gen_coupon ) ) {
										foreach ( $excluded_categories_gen_coupon as $category_id ) {
											$category = get_term( $category_id );
											if ( $category ) {
												?>
                                                <option value="<?php echo $category_id ?>"
                                                        selected><?php echo $category->name; ?></option>
												<?php
											}

										}
									}
									?>
                                </select><?php esc_html_e( 'Reviews on products in these categories will not receive coupon', 'woocommerce-photo-reviews' ) ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="from_address"><?php esc_html_e( 'Custom "from" address', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <input id="from_address" type="text" name="from_address"
                                       value="<?php echo isset( $this->settings->get_params( 'coupon', 'email' )['from_address'] ) ? stripslashes( $this->settings->get_params( 'coupon', 'email' )['from_address'] ) : ''; ?>">
                                <p><?php esc_html_e( 'If blank, "From" address of WooCommerce will be used', 'woocommerce-photo-reviews' ) ?></p>
                            </td>
                        </tr>
						<?php
						$email_template = $this->settings->get_params( 'email_template' );
						?>
                        <tr>
                            <th>
                                <label for="email_template"><?php esc_html_e( 'Email template', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
								<?php
								$this->print_default_country_flag();
								$email_templates = self::get_email_templates();
								?>
                                <select class="vi-ui dropdown fluid" id="email_template" type="text"
                                        name="email_template">
                                    <option value=""><?php esc_html_e( 'None', 'woocommerce-photo-reviews' ) ?></option>
									<?php
									if ( count( $email_templates ) ) {
										foreach ( $email_templates as $email_template_k => $email_template_v ) {
											?>
                                            <option value="<?php echo esc_attr( $email_template_v->ID ); ?>" <?php selected( $email_template_v->ID, $email_template ); ?>><?php echo esc_html( "(#{$email_template_v->ID}){$email_template_v->post_title}" ); ?></option>
											<?php
										}
									}
									?>
                                </select>
								<?php
								if ( count( $this->languages ) ) {
									foreach ( $this->languages as $key => $value ) {
										$email_template_lang = $this->settings->get_params( 'email_template', '', $value );
										$this->print_other_country_flag( 'email_template', $value );
										?>
                                        <select class="vi-ui dropdown fluid"
                                                id="<?php echo esc_attr( "email_template_{$value}" ) ?>" type="text"
                                                name="<?php echo esc_attr( "email_template_{$value}" ) ?>">
                                            <option value=""><?php esc_html_e( 'None', 'woocommerce-photo-reviews' ) ?></option>
											<?php
											if ( count( $email_templates ) ) {
												foreach ( $email_templates as $email_template_k => $email_template_v ) {
													?>
                                                    <option value="<?php echo esc_attr( $email_template_v->ID ); ?>" <?php selected( $email_template_v->ID, $email_template_lang ); ?>><?php echo esc_html( "(#{$email_template_v->ID}){$email_template_v->post_title}" ); ?></option>
													<?php
												}
											}
											?>
                                        </select>
										<?php
									}
								}
								?>
                                <p><?php _e( 'You can use <a href="https://1.envato.market/BZZv1" target="_blank">WooCommerce Email Template Customizer</a> or <a href="http://bit.ly/woo-email-template-customizer" target="_blank">Email Template Customizer for WooCommerce</a> to create and customize your own email template. If no email template is selected, below email will be used.', 'woocommerce-photo-reviews' ) ?></p>
								<?php
								if ( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::is_email_template_customizer_active() ) {
									?>
                                    <p>
                                        <a href="edit.php?post_type=viwec_template"
                                           target="_blank"><?php esc_html_e( 'View all Email templates', 'woocommerce-photo-reviews' ) ?></a>
										<?php esc_html_e( 'or', 'woocommerce-photo-reviews' ) ?>
                                        <a href="post-new.php?post_type=viwec_template&sample=wcpr_coupon_email&style=basic"
                                           target="_blank"><?php esc_html_e( 'Create a new email template', 'woocommerce-photo-reviews' ) ?></a>
                                    </p>
									<?php
								}
								?>
                            </td>
                        </tr>
                    </table>
                    <div class="vi-ui segment <?php VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::set( 'default-coupon-email' ) ?>">
                        <div class="vi-ui message"><?php esc_html_e( 'This email is used when no Email template is selected', 'woocommerce-photo-reviews' ) ?></div>
                        <table class="form-table">
                            <tr>
                                <th>
                                    <label for="subject"><?php esc_html_e( 'Email subject', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
									<?php
									$this->print_default_country_flag();
									?>
                                    <input id="subject" type="text" name="subject"
                                           value="<?php echo stripslashes( $this->settings->get_params( 'coupon', 'email' )['subject'] ); ?>">
                                    <p><?php esc_html_e( 'The subject of emails sending to customers which include discount coupon code.', 'woocommerce-photo-reviews' ) ?></p>
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$this->print_other_country_flag( 'subject', $value );
											?>
                                            <input id="<?php echo 'subject_' . $value ?>" type="text"
                                                   name="<?php echo 'subject_' . $value ?>"
                                                   value="<?php echo stripslashes( $this->settings->get_params( 'coupon', 'email', $value )['subject'] ); ?>">
											<?php
										}
									}
									?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="heading"><?php esc_html_e( 'Email heading', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
									<?php
									$this->print_default_country_flag();
									?>
                                    <input id="heading" type="text" name="heading"
                                           value="<?php echo isset( $this->settings->get_params( 'coupon', 'email' )['heading'] ) ? stripslashes( $this->settings->get_params( 'coupon', 'email' )['heading'] ) : 'Thank You For Your Review!'; ?>">
                                    <p><?php esc_html_e( 'The heading of emails sending to customers which include discount coupon code.', 'woocommerce-photo-reviews' ) ?></p>
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$this->print_other_country_flag( 'heading', $value );
											?>
                                            <input id="<?php echo 'heading_' . $value ?>" type="text"
                                                   name="<?php echo 'heading_' . $value ?>"
                                                   value="<?php echo stripslashes( $this->settings->get_params( 'coupon', 'email', $value )['heading'] ); ?>">
											<?php
										}
									}
									?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="content"><?php esc_html_e( 'Email content', 'woocommerce-photo-reviews' ) ?></label>
                                    <p><?php esc_html_e( 'The content of email sending to customers to inform them the coupon code they get for leaving reviews.', 'woocommerce-photo-reviews' ) ?></p>
                                </th>
                                <td>
									<?php
									$this->print_default_country_flag();
									wp_editor( stripslashes( $this->settings->get_params( 'coupon', 'email' )['content'] ), 'content', array(
										'editor_height' => 300,
										'media_buttons' => true
									) );
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$this->print_other_country_flag( 'content', $value );
											wp_editor( stripslashes( $this->settings->get_params( 'coupon', 'email', $value )['content'] ), 'content_' . $value, array(
												'editor_height' => 300,
												'media_buttons' => true
											) );
										}
									}
									?>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <ul>
                                        <li><?php esc_html_e( '{customer_name} - Customer\'s name.', 'woocommerce-photo-reviews' ) ?></li>
                                        <li><?php esc_html_e( '{coupon_code} - Discount coupon code will be sent to customer.', 'woocommerce-photo-reviews' ) ?></li>
                                        <li><?php esc_html_e( '{date_expires} - Expiry date of the coupon.', 'woocommerce-photo-reviews' ) ?></li>
                                    </ul>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <table class="form-table">
                        <tr>
                            <th>
                                <label for="set-email-restriction"><?php esc_html_e( 'Set email restriction', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="set-email-restriction" type="checkbox"
                                           name="set_email_restriction"
                                           value="1" <?php checked( $this->settings->get_params( 'set_email_restriction' ), '1' ) ?>><label
                                            for="set-email-restriction"><?php esc_html_e( 'If enabled, coupon will be used for received emails only.', 'woocommerce-photo-reviews' ) ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th style="border-bottom: 1px solid #F5F5F5;">
                                <label for="kt_coupons_select"><?php esc_html_e( 'Select coupon kind', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td style="border-bottom: 1px solid #F5F5F5;">
                                <select id="kt_coupons_select" name="kt_coupons_select" class="vi-ui fluid dropdown">
                                    <option value="kt_generate_coupon"<?php selected( $this->settings->get_params( 'coupon', 'coupon_select' ), 'kt_generate_coupon' ) ?>><?php esc_html_e( 'Generate unique coupon', 'woocommerce-photo-reviews' ) ?>
                                    </option>
                                    <option value="kt_existing_coupon"<?php selected( $this->settings->get_params( 'coupon', 'coupon_select' ), 'kt_existing_coupon' ) ?>><?php esc_html_e( 'Select an existing coupon', 'woocommerce-photo-reviews' ) ?>
                                    </option>
                                </select>
                                <p><?php esc_html_e( 'Choose to send an existing coupon or generate unique coupons.', 'woocommerce-photo-reviews' ) ?></p>
                            </td>
                        </tr>
                        <tr class="kt-existing-coupons">
                            <th>
                                <label for="kt_existing_coupons"><?php esc_html_e( 'Select an existing coupon', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <select id="kt_existing_coupons" name="kt_existing_coupons"
                                        class="coupon-search select2-selection--single"
                                        data-placeholder="<?php esc_html_e( 'Please Fill In Your Coupon Code', 'woocommerce-photo-reviews' ) ?>">
									<?php
									if ( '' !== $this->settings->get_params( 'coupon', 'existing_coupon' ) ) {
										echo '<option value="' . $this->settings->get_params( 'coupon', 'existing_coupon' ) . '" selected>' . get_post( $this->settings->get_params( 'coupon', 'existing_coupon' ) )->post_title . '</option>';
									}
									?>
                                </select>
                            </td>
                        </tr>
                        <tr class="kt-custom-coupon">
                            <th><?php esc_html_e( 'Settings For Unique Coupon', 'woocommerce-photo-reviews' ) ?></th>
                            <td></td>
                        </tr>
                        <tr class="kt-custom-coupon">
                            <th>
                                <label for="kt_discount_type"><?php esc_html_e( 'Discount Type', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div>
                                    <select id="kt_discount_type" name="kt_discount_type" class="vi-ui fluid dropdown">
                                        <option value="percent" <?php selected( $coupon_generate['discount_type'], 'percent' ) ?>><?php esc_html_e( 'Percentage discount', 'woocommerce-photo-reviews' ) ?>
                                        </option>
                                        <option value="fixed_cart" <?php selected( $coupon_generate['discount_type'], 'fixed_cart' ) ?>><?php esc_html_e( 'Fixed cart discount', 'woocommerce-photo-reviews' ) ?>
                                        </option>
                                        <option value="fixed_product" <?php selected( $coupon_generate['discount_type'], 'fixed_product' ) ?>><?php esc_html_e( 'Fixed product discount', 'woocommerce-photo-reviews' ) ?>
                                        </option>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr class="kt-custom-coupon">
                            <th>
                                <label for="kt_coupon_amount"><?php esc_html_e( 'Coupon Amount', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <input type="number" min="0" class="short wc_input_price" step="0.01"
                                       name="kt_coupon_amount" id="kt_coupon_amount"
                                       value="<?php echo( $coupon_generate['coupon_amount'] ); ?>" placeholder="0">
								<?php esc_html_e( 'Value of the coupon.', 'woocommerce-photo-reviews' ) ?>
                            </td>
                        </tr>
                        <tr class="kt-custom-coupon">
                            <th><?php esc_html_e( 'Allow Free Shipping', 'woocommerce-photo-reviews' ) ?></th>
                            <td>
                                <input type="checkbox"
                                       class="checkbox" <?php if ( $coupon_generate['allow_free_shipping'] == 'yes' ) {
									echo 'checked';
								} ?> name="kt_free_shipping" id="kt_free_shipping" value="yes">
                                <label for="kt_free_shipping"><?php esc_html_e( 'Check this box if the coupon grants free shipping. A ', 'woocommerce-photo-reviews' ) ?>
                                    <a href="https://docs.woocommerce.com/document/free-shipping/"
                                       target="_blank"><?php esc_html_e( 'free shipping method', 'woocommerce-photo-reviews' ); ?></a><?php esc_html_e( ' must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'woocommerce-photo-reviews' ); ?>
                                </label>
                            </td>
                        </tr>
                        <tr class="kt-custom-coupon">
                            <th>
                                <label for="kt_expiry_date"><?php esc_html_e( 'Time To Live', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui right labeled input">
                                    <input type="number" min="0" name="kt_expiry_date" id="kt_expiry_date"
                                           value="<?php echo $coupon_generate['expiry_date']; ?>">
                                    <label for="kt_expiry_date"
                                           class="vi-ui label"><?php esc_html_e( 'Day(s)', 'woocommerce-photo-reviews' ) ?></label>
                                </div>
                                <p><?php esc_html_e( 'Coupon will expire after x day(s) since it\'s generated and sent. Set 0 or blank to make it never expire.', 'woocommerce-photo-reviews' ) ?></p>
                            </td>
                        </tr>
                        <tr class="kt-custom-coupon">
                            <th>
                                <label for="kt_min_spend"><?php esc_html_e( 'Minimum Spend', 'woocommerce-photo-reviews' ) ?></label>

                            </th>
                            <td>
                                <input type="text" class="short wc_input_price" name="kt_min_spend"
                                       id="kt_min_spend" value="<?php echo $coupon_generate['min_spend']; ?>"
                                       placeholder="<?php esc_html_e( 'No minimum', 'woocommerce-photo-reviews' ) ?>">
								<?php esc_html_e( 'The minimum spend to use the coupon.', 'woocommerce-photo-reviews' ) ?>
                            </td>
                        </tr>
                        <tr class="kt-custom-coupon">
                            <th>
                                <label for="kt_max_spend"><?php esc_html_e( 'Maximum Spend', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <input type="text" class="short wc_input_price" name="kt_max_spend"
                                       id="kt_max_spend" value="<?php echo $coupon_generate['max_spend']; ?>"
                                       placeholder="<?php esc_html_e( 'No maximum', 'woocommerce-photo-reviews' ) ?>">
								<?php esc_html_e( 'The maximum spend to use the coupon.', 'woocommerce-photo-reviews' ) ?>
                            </td>
                        </tr>
                        <tr class="kt-custom-coupon">
                            <th><?php esc_html_e( 'Individual Use Only', 'woocommerce-photo-reviews' ) ?></th>
                            <td>
                                <input type="checkbox" <?php if ( $coupon_generate['individual_use'] == 'yes' ) {
									echo 'checked';
								} ?> class="checkbox" name="kt_individual_use" id="kt_individual_use"
                                       value="yes"><label
                                        for="kt_individual_use"><?php esc_html_e( 'Check this box if the coupon cannot be used in conjunction with other coupons.', 'woocommerce-photo-reviews' ) ?></label>
                            </td>
                        </tr>
                        <tr class="kt-custom-coupon">
                            <th><?php esc_html_e( 'Exclude Sale Items', 'woocommerce-photo-reviews' ) ?></th>
                            <td>
                                <input type="checkbox" <?php if ( $coupon_generate['exclude_sale_items'] == 'yes' ) {
									echo 'checked';
								} ?> class="checkbox" name="kt_exclude_sale_items" id="kt_exclude_sale_items"
                                       value="yes"><label
                                        for="kt_exclude_sale_items"><?php esc_html_e( 'Check this box if the coupon should not apply to items on sale. Per-item coupons will only work if the item is not on sale. Per-cart coupons will only work if there are items in the cart that are not on sale.', 'woocommerce-photo-reviews' ) ?></label>
                            </td>
                        </tr>
                        <tr class="kt-custom-coupon">
                            <th>
                                <label for="kt_product_ids"><?php esc_html_e( 'Include products', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div>
                                    <select id="kt_product_ids" name="kt_product_ids[]" multiple="multiple"
                                            class="product-search"
                                            data-placeholder="<?php esc_html_e( 'Please Fill In Your Product Title', 'woocommerce-photo-reviews' ) ?>">
										<?php
										$product_ids = $coupon_generate['product_ids'];
										if ( count( $product_ids ) ) {
											foreach ( $product_ids as $ps ) {
												$product = wc_get_product( $ps );
												if ( $product ) {
													?>
                                                    <option selected
                                                            value="<?php echo $ps ?>"><?php echo $product->get_title() ?></option>
													<?php
												}
											}
										}
										?>
                                    </select>
                                    <span class="wcpr-select-all-product vi-ui button"><?php esc_html_e( 'Select all', 'woocommerce-photo-reviews' ) ?></span>
                                    <span class="wcpr-clear-all-product vi-ui negative button"><?php esc_html_e( 'Clear all', 'woocommerce-photo-reviews' ) ?></span>
                                </div>
								<?php esc_html_e( 'Products that the coupon will be applied to, or that need to be in the cart in order for the "Fixed cart discount" to be applied', 'woocommerce-photo-reviews' ) ?>
                            </td>
                        </tr>
                        <tr class="kt-custom-coupon">
                            <th>
                                <label for="kt_excluded_product_ids"><?php esc_html_e( 'Exclude products', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <select id="kt_excluded_product_ids" name="kt_excluded_product_ids[]"
                                        multiple="multiple"
                                        class="product-search"
                                        data-placeholder="<?php esc_html_e( 'Please Fill In Your Product Title', 'woocommerce-photo-reviews' ) ?>">
									<?php
									$excluded_product_ids = isset( $coupon_generate['excluded_product_ids'] ) ? $coupon_generate['excluded_product_ids'] : array();
									if ( count( $excluded_product_ids ) ) {
										foreach ( $excluded_product_ids as $ps ) {
											$product = wc_get_product( $ps );
											if ( $product ) {
												?>
                                                <option selected
                                                        value="<?php echo $ps ?>"><?php echo $product->get_title() ?></option>
												<?php
											}
										}
									}
									?>
                                </select>
                                <span class="wcpr-select-all-product vi-ui button"><?php esc_html_e( 'Select all', 'woocommerce-photo-reviews' ) ?></span>
                                <span class="wcpr-clear-all-product vi-ui negative button"><?php esc_html_e( 'Clear all', 'woocommerce-photo-reviews' ) ?></span>
                                <p><?php esc_html_e( 'Products that the coupon will not be applied to, or that cannot be in the cart in order for the "Fixed cart discount" to be applied.', 'woocommerce-photo-reviews' ) ?></p>
                            </td>
                        </tr>

                        <tr class="kt-custom-coupon">
                            <th>
                                <label for="kt_product_categories"><?php esc_html_e( 'Included categories', 'woocommerce-photo-reviews' ) ?></label>

                            </th>
                            <td>
                                <select id="kt_product_categories"
                                        data-placeholder="<?php esc_html_e( 'Please enter category title', 'woocommerce-photo-reviews' ) ?>"
                                        name="kt_product_categories[]" multiple="multiple"
                                        class="category-search select2-selection--multiple">
									<?php
									$product_categories = isset( $coupon_generate['product_categories'] ) ? $coupon_generate['product_categories'] : array();
									if ( count( $product_categories ) ) {
										foreach ( $product_categories as $category_id ) {
											$category = get_term( $category_id );
											?>
                                            <option value="<?php echo $category_id ?>"
                                                    selected><?php echo $category->name; ?></option>
											<?php
										}
									}
									?>
                                </select><?php esc_html_e( 'Product categories that the coupon will be applied to, or that need to be in the cart in order for the "Fixed cart discount" to be applied.', 'woocommerce-photo-reviews' ) ?>
                            </td>
                        </tr>

                        <tr class="kt-custom-coupon">
                            <th>
                                <label for="kt_excluded_product_categories"><?php esc_html_e( 'Exclude categories', 'woocommerce-photo-reviews' ) ?></label>

                            </th>
                            <td>
                                <select id="kt_excluded_product_categories"
                                        data-placeholder="<?php esc_html_e( 'Please enter category title', 'woocommerce-photo-reviews' ) ?>"
                                        name="kt_excluded_product_categories[]" multiple="multiple"
                                        class="category-search select2-selection--multiple">
									<?php
									$excluded_categories = isset( $coupon_generate['excluded_product_categories'] ) ? $coupon_generate['excluded_product_categories'] : array();
									if ( count( $excluded_categories ) ) {
										foreach ( $excluded_categories as $category_id ) {
											$category = get_term( $category_id );
											?>
                                            <option value="<?php echo $category_id ?>"
                                                    selected><?php echo $category->name; ?></option>
											<?php
										}
									}
									?>
                                </select><?php esc_html_e( 'Product categories that the coupon will not be applied to, or that cannot be in the cart in order for the "Fixed cart discount" to be applied.', 'woocommerce-photo-reviews' ) ?>
                            </td>
                        </tr>

                        <tr class="kt-custom-coupon">
                            <th>
                                <label for="kt_limit_per_coupon"><?php esc_html_e( 'Usage Limit Per Coupon', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <input type="number" class="short" name="kt_limit_per_coupon"
                                       id="kt_limit_per_coupon"
                                       value="<?php if ( $coupon_generate['limit_per_coupon'] > 0 ) {
									       echo $coupon_generate['limit_per_coupon'];
								       } ?>" placeholder="Unlimited usage" step="1" min="0">
								<?php esc_html_e( 'How many times this coupon can be used before it is void.', 'woocommerce-photo-reviews' ) ?>
                            </td>
                        </tr>
                        <tr class="kt-custom-coupon">
                            <th>
                                <label for="kt_limit_to_x_items"><?php esc_html_e( 'Limit Usage To X Items', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <input type="number" class="short" name="kt_limit_to_x_items"
                                       id="kt_limit_to_x_items"
                                       value="<?php if ( $coupon_generate['limit_to_x_items'] > 0 ) {
									       echo $coupon_generate['limit_to_x_items'];
								       } ?>"
                                       placeholder="<?php esc_html_e( 'Apply To All Qualifying Items In Cart', 'woocommerce-photo-reviews' ) ?>"
                                       step="1" min="0">
								<?php esc_html_e( 'The maximum number of individual items this coupon can apply to when using product discount.', 'woocommerce-photo-reviews' ) ?>
                            </td>
                        </tr>
                        <tr class="kt-custom-coupon">
                            <th>
                                <label for="kt_limit_per_user"><?php esc_html_e( 'Usage Limit Per User', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <input type="number" class="short" name="kt_limit_per_user"
                                       id="kt_limit_per_user"
                                       value="<?php if ( $coupon_generate['limit_per_user'] > 0 ) {
									       echo $coupon_generate['limit_per_user'];
								       } ?>"
                                       placeholder="<?php esc_html_e( 'Unlimited Usage', 'woocommerce-photo-reviews' ) ?>"
                                       step="1" min="0">
								<?php esc_html_e( 'How many times this coupon can be used by an individual user.', 'woocommerce-photo-reviews' ) ?>
                            </td>
                        </tr>
                        <tr class="kt-custom-coupon">
                            <th>
                                <label for="kt_coupon_code_prefix"><?php esc_html_e( 'Coupon Code Prefix', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <input id="kt_coupon_code_prefix" type="text" name="kt_coupon_code_prefix"
                                       value="<?php if ( $coupon_generate['coupon_code_prefix'] ) {
									       esc_html_e( $coupon_generate['coupon_code_prefix'], 'woocommerce-photo-reviews' );
								       } ?>"></td>
                        </tr>
                    </table>

                </div>
                <div class="vi-ui bottom tab segment" data-tab="email">

                    <table class="form-table">
                        <tr>
                            <th>
                                <label for="follow_up_email_enable"><?php esc_html_e( 'Review reminder', 'woocommerce-photo-reviews' ) ?></label>
                            </th>

                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" id="follow_up_email_enable"
                                           name="follow_up_email_enable"
                                           value="on" <?php checked( $this->settings->get_params( 'followup_email', 'enable' ), 'on' ) ?>>
                                    <label for="follow_up_email_enable"><?php esc_html_e( 'If checked, an email will be automatically sent when a customer completes an order to request for a review.', 'woocommerce-photo-reviews' ) ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="<?php echo esc_attr( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::set( 'order_statuses' ) ) ?>"><?php esc_html_e( 'Order statuses trigger', 'woocommerce-photo-reviews' ) ?></label>

                            </th>
                            <td>
                                <select name="<?php echo esc_attr( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::set( 'order_statuses', true ) ) ?>[]"
                                        multiple="multiple"
                                        id="<?php echo esc_attr( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::set( 'order_statuses' ) ) ?>"
                                        class="<?php echo esc_attr( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::set( 'order_statuses' ) ) ?> vi-ui fluid dropdown">
									<?php
									$order_statuses    = $this->settings->get_params( 'followup_email', 'order_statuses' );
									$wc_order_statuses = wc_get_order_statuses();
									foreach ( $wc_order_statuses as $status_k => $status_v ) {
										?>
                                        <option value="<?php esc_attr_e( $status_k ) ?>" <?php if ( in_array( $status_k, $order_statuses ) ) {
											echo esc_attr( 'selected' );
										} ?>><?php esc_html_e( $status_v ) ?></option>
										<?php
									}
									?>
                                </select>
                                <p><?php esc_html_e( 'Send reminder when an order status changes to one of these values.', 'woocommerce-photo-reviews' ) ?></p>
                                <p><?php esc_html_e( 'Bulk send review reminder only applies to orders whose statuses are among these values.', 'woocommerce-photo-reviews' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="exclude_addresses"><?php esc_html_e( 'Exclude Email addresses', 'woocommerce-photo-reviews' ) ?></label>

                            </th>
                            <td>
                                <select name="exclude_addresses[]" id="exclude_addresses"
                                        class="exclude-addresses"
                                        multiple="multiple">
									<?php
									$exclude_addresses = $this->settings->get_params( 'followup_email', 'exclude_addresses' );
									if ( is_array( $exclude_addresses ) && count( $exclude_addresses ) ) {
										foreach ( $exclude_addresses as $exclude_address_id ) {
											?>
                                            <option value="<?php echo $exclude_address_id ?>"
                                                    selected><?php echo $exclude_address_id; ?></option>
											<?php
										}
									}
									?>
                                </select>
                                <p><?php esc_html_e( 'Reminder will not be sent if email is among these', 'woocommerce-photo-reviews' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="follow-up-email-products-restriction"><?php esc_html_e( 'Exclude products', 'woocommerce-photo-reviews' ) ?></label>

                            </th>
                            <td>
                                <select id="follow-up-email-products-restriction"
                                        data-placeholder="<?php esc_html_e( 'Please Fill In Your Product Title', 'woocommerce-photo-reviews' ) ?>"
                                        name="follow-up-email-products-restriction[]" multiple="multiple"
                                        class="product-search select2-selection--multiple">
									<?php
									$products_restriction = $this->settings->get_params( 'followup_email', 'products_restriction' );
									if ( is_array( $products_restriction ) && count( $products_restriction ) ) {
										foreach ( $products_restriction as $prn ) {
											$product = wc_get_product( $prn );
											if ( $product ) {
												echo '<option selected value="' . $prn . '">' . $product->get_title() . '</option>';
											}
										}
									}
									?>
                                </select>
                                <span class="wcpr-select-all-product vi-ui button"><?php esc_html_e( 'Select all', 'woocommerce-photo-reviews' ) ?></span>
                                <span class="wcpr-clear-all-product vi-ui negative button"><?php esc_html_e( 'Clear all', 'woocommerce-photo-reviews' ) ?></span>
                                <p><?php esc_html_e( 'These products will not appear in review reminder email.', 'woocommerce-photo-reviews' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="follow-up-email-excluded-categories"><?php esc_html_e( 'Exclude categories', 'woocommerce-photo-reviews' ) ?></label>

                            </th>
                            <td>
                                <select id="follow-up-email-excluded-categories"
                                        data-placeholder="<?php esc_html_e( 'Please enter category title', 'woocommerce-photo-reviews' ) ?>"
                                        name="follow-up-email-excluded-categories[]" multiple="multiple"
                                        class="category-search select2-selection--multiple">
									<?php
									$excluded_categories = $this->settings->get_params( 'followup_email', 'excluded_categories' );
									if ( is_array( $excluded_categories ) && count( $excluded_categories ) ) {
										foreach ( $excluded_categories as $category_id ) {
											$category = get_term( $category_id );
											?>
                                            <option value="<?php echo $category_id ?>"
                                                    selected><?php echo $category->name; ?></option>
											<?php
										}
									}
									?>
                                </select><?php esc_html_e( 'Products in these categories will not appear in review reminder email.', 'woocommerce-photo-reviews' ) ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="exclude_non_coupon_products"><?php esc_html_e( 'Exclude non-coupon given products', 'woocommerce-photo-reviews' ) ?></label>
                            </th>

                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" id="exclude_non_coupon_products"
                                           name="exclude_non_coupon_products"
                                           value="on" <?php checked( $this->settings->get_params( 'followup_email', 'exclude_non_coupon_products' ), 'on' ) ?>>
                                    <label for="exclude_non_coupon_products"><?php esc_html_e( 'Enable this if you mean to offer coupon for reviews in review reminder.', 'woocommerce-photo-reviews' ) ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr class="follow-up-email">
                            <th>
                                <label for="email_time_amount"><?php esc_html_e( 'Schedule time', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div class="equal width fields">
                                    <div class="field">
                                        <input class="email-time" id="email_time_amount"
                                               name="email_time_amount" type="number" min="1"
                                               value="<?php _e( $this->settings->get_params( 'followup_email', 'amount' ), 'woocommerce-photo-reviews' ); ?>">
                                    </div>
                                    <div class="field">
										<?php
										$reminder_email_unit = $this->settings->get_params( 'followup_email', 'unit' );
										$units               = array(
											's' => esc_html__( 'Seconds', 'woocommerce-photo-reviews' ),
											'm' => esc_html__( 'Minutes', 'woocommerce-photo-reviews' ),
											'h' => esc_html__( 'Hours', 'woocommerce-photo-reviews' ),
											'd' => esc_html__( 'Days', 'woocommerce-photo-reviews' ),
										);
										?>
                                        <select class="email-time vi-ui dropdown" id="email_time_unit"
                                                name="email_time_unit">
											<?php
											foreach ( $units as $units_k => $units_v ) {
												?>
                                                <option value="<?php echo esc_attr( $units_k ) ?>" <?php selected( $units_k, $reminder_email_unit ) ?>><?php echo $units_v; ?></option>
												<?php
											}
											?>
                                        </select>
                                    </div>
                                </div>
                                <p><?php esc_html_e( 'Schedule a time to send request email order status changes to the one you selected.', 'woocommerce-photo-reviews' ) ?></p>
                            </td>
                        </tr>
                        <tr class="follow-up-email">
                            <th>
                                <label for="follow_up_email_from_address"><?php esc_html_e( 'Custom "from" address', 'woocommerce-photo-reviews' ) ?></label>

                            </th>
                            <td>
                                <input id="follow_up_email_from_address" type="text" name="follow_up_email_from_address"
                                       value="<?php echo stripslashes( $this->settings->get_params( 'followup_email', 'from_address' ) ); ?>">
                                <p><?php esc_html_e( 'If blank, "From" address of WooCommerce will be used', 'woocommerce-photo-reviews' ) ?></p>
                            </td>
                        </tr>
						<?php
						$reminder_email_template = $this->settings->get_params( 'reminder_email_template' );
						?>
                        <tr>
                            <th>
                                <label for="reminder_email_template"><?php esc_html_e( 'Email template', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
								<?php
								$this->print_default_country_flag();
								$reminder_email_templates = self::get_email_templates( 'wcpr_review_reminder' );
								?>
                                <select class="vi-ui dropdown fluid" id="reminder_email_template" type="text"
                                        name="reminder_email_template">
                                    <option value=""><?php esc_html_e( 'None', 'woocommerce-photo-reviews' ) ?></option>
									<?php
									if ( count( $reminder_email_templates ) ) {
										foreach ( $reminder_email_templates as $reminder_email_template_k => $reminder_email_template_v ) {
											?>
                                            <option value="<?php echo esc_attr( $reminder_email_template_v->ID ); ?>" <?php selected( $reminder_email_template_v->ID, $reminder_email_template ); ?>><?php echo esc_html( "(#{$reminder_email_template_v->ID}){$reminder_email_template_v->post_title}" ); ?></option>
											<?php
										}
									}
									?>
                                </select>
								<?php
								if ( count( $this->languages ) ) {
									foreach ( $this->languages as $key => $value ) {
										$reminder_email_template_lang = $this->settings->get_params( 'reminder_email_template', '', $value );
										$this->print_other_country_flag( 'reminder_email_template', $value );
										?>
                                        <select class="vi-ui dropdown fluid"
                                                id="<?php echo esc_attr( "reminder_email_template_{$value}" ) ?>"
                                                type="text"
                                                name="<?php echo esc_attr( "reminder_email_template_{$value}" ) ?>">
                                            <option value=""><?php esc_html_e( 'None', 'woocommerce-photo-reviews' ) ?></option>
											<?php
											if ( count( $reminder_email_templates ) ) {
												foreach ( $reminder_email_templates as $reminder_email_template_k => $reminder_email_template_v ) {
													?>
                                                    <option value="<?php echo esc_attr( $reminder_email_template_v->ID ); ?>" <?php selected( $reminder_email_template_v->ID, $reminder_email_template_lang ); ?>><?php echo esc_html( "(#{$reminder_email_template_v->ID}){$reminder_email_template_v->post_title}" ); ?></option>
													<?php
												}
											}
											?>
                                        </select>
										<?php
									}
								}
								?>
                                <p><?php _e( 'You can use <a href="https://1.envato.market/BZZv1" target="_blank">WooCommerce Email Template Customizer</a> or <a href="http://bit.ly/woo-email-template-customizer" target="_blank">Email Template Customizer for WooCommerce</a> to create and customize your own email template. If no email template is selected, below email will be used.', 'woocommerce-photo-reviews' ) ?></p>
								<?php
								if ( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::is_email_template_customizer_active() ) {
									?>
                                    <p>
                                        <a href="edit.php?post_type=viwec_template"
                                           target="_blank"><?php esc_html_e( 'View all Email templates', 'woocommerce-photo-reviews' ) ?></a>
										<?php esc_html_e( 'or', 'woocommerce-photo-reviews' ) ?>
                                        <a href="post-new.php?post_type=viwec_template&sample=wcpr_review_reminder&style=basic"
                                           target="_blank"><?php esc_html_e( 'Create a new email template', 'woocommerce-photo-reviews' ) ?></a>
                                    </p>
									<?php
								}
								?>
                            </td>
                        </tr>
                    </table>
                    <div class="vi-ui segment <?php VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::set( 'default-review-reminder-email' ) ?>">
                        <div class="vi-ui message"><?php esc_html_e( 'This email is used when no Email template is selected', 'woocommerce-photo-reviews' ) ?></div>
                        <table class="form-table">
                            <tr class="follow-up-email">
                                <th>
                                    <label for="follow_up_email_subject"><?php esc_html_e( 'Email subject', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
									<?php
									$this->print_default_country_flag();
									?>
                                    <input id="follow_up_email_subject" type="text" name="follow_up_email_subject"
                                           value="<?php echo stripslashes( $this->settings->get_params( 'followup_email', 'subject' ) ); ?>">
                                    <p><?php esc_html_e( 'The subject of emails sending to customers to request for reviews.', 'woocommerce-photo-reviews' ) ?></p>
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$this->print_other_country_flag( 'follow_up_email_subject', $value );
											?>
                                            <input id="<?php echo 'follow_up_email_subject_' . $value ?>" type="text"
                                                   name="<?php echo 'follow_up_email_subject_' . $value ?>"
                                                   value="<?php echo stripslashes( $this->settings->get_params( 'followup_email', 'subject', $value ) ); ?>">
											<?php
										}
									}
									?>
                                </td>
                            </tr>
                            <tr class="follow-up-email">
                                <th>
                                    <label for="follow_up_email_heading"><?php esc_html_e( 'Email heading', 'woocommerce-photo-reviews' ) ?></label>
                                </th>
                                <td>
									<?php
									$this->print_default_country_flag();
									?>
                                    <input id="follow_up_email_heading" type="text" name="follow_up_email_heading"
                                           value="<?php echo stripslashes( $this->settings->get_params( 'followup_email', 'heading' ) ); ?>">
                                    <p><?php esc_html_e( 'The heading of emails sending to customers to request for reviews.', 'woocommerce-photo-reviews' ) ?></p>
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$this->print_other_country_flag( 'follow_up_email_heading', $value );
											?>
                                            <input id="<?php echo 'follow_up_email_heading_' . $value ?>" type="text"
                                                   name="<?php echo 'follow_up_email_heading_' . $value ?>"
                                                   value="<?php echo stripslashes( $this->settings->get_params( 'followup_email', 'heading', $value ) ); ?>">
											<?php
										}
									}
									?>
                                </td>
                            </tr>
                            <tr class="follow-up-email">
                                <th>
                                    <label for="follow_up_email_content"><?php esc_html_e( 'Email content', 'woocommerce-photo-reviews' ) ?></label>
                                    <p><?php esc_html_e( 'The content of email sending to customers to ask for reviews.', 'woocommerce-photo-reviews' ) ?></p>
                                </th>
                                <td>
									<?php
									$this->print_default_country_flag();
									wp_editor( stripslashes( $this->settings->get_params( 'followup_email', 'content' ) ), 'follow_up_email_content', array(
										'editor_height' => 300,
										'media_buttons' => true
									) );
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$this->print_other_country_flag( 'follow_up_email_content', $value );
											wp_editor( stripslashes( $this->settings->get_params( 'followup_email', 'content', $value ) ), 'follow_up_email_content_' . $value, array(
												'editor_height' => 300,
												'media_buttons' => true
											) );
										}
									}
									?>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <ul>
                                        <li><?php esc_html_e( '{customer_name} - Customer\'s name.', 'woocommerce-photo-reviews' ) ?></li>
                                        <li><?php esc_html_e( '{order_id} - Order id.', 'woocommerce-photo-reviews' ) ?></li>
                                        <li><?php esc_html_e( '{site_title} - Your site title.', 'woocommerce-photo-reviews' ) ?></li>
                                        <li><?php esc_html_e( '{date_create} - Order\'s created date.', 'woocommerce-photo-reviews' ) ?></li>
                                        <li><?php esc_html_e( '{date_complete} - Order\'s completed date.', 'woocommerce-photo-reviews' ) ?></li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="button-review-now"><?php esc_html_e( 'Button "Review now"', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
									<?php
									$this->print_default_country_flag();
									?>
                                    <input type="text" id="button-review-now"
                                           name="button-review-now"
                                           value="<?php echo $this->settings->get_params( 'followup_email', 'review_button' ); ?>">
									<?php
									if ( count( $this->languages ) ) {
										foreach ( $this->languages as $key => $value ) {
											$this->print_other_country_flag( 'button-review-now', $value );
											?>
                                            <input id="<?php echo 'button-review-now_' . $value ?>" type="text"
                                                   name="<?php echo 'button-review-now_' . $value ?>"
                                                   value="<?php echo stripslashes( $this->settings->get_params( 'followup_email', 'review_button', $value ) ); ?>">
											<?php
										}
									}
									?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="button-review-now-color"><?php esc_html_e( 'Button "Review now" text color', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" class="color-picker" id="button-review-now-color"
                                           name="button-review-now-color"
                                           value="<?php echo $this->settings->get_params( 'followup_email', 'review_button_color' ); ?>"
                                           style="background-color: <?php echo $this->settings->get_params( 'followup_email', 'review_button_color' ); ?>;">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="button-review-now-bg-color"><?php esc_html_e( 'Button "Review now" background color', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" class="color-picker" id="button-review-now-bg-color"
                                           name="button-review-now-bg-color"
                                           value="<?php echo $this->settings->get_params( 'followup_email', 'review_button_bg_color' ); ?>"
                                           style="background-color: <?php echo $this->settings->get_params( 'followup_email', 'review_button_bg_color' ); ?>;">
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="product-image-width"><?php esc_html_e( 'Product image width', 'woocommerce-photo-reviews' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui right labeled input">
                                        <input type="number" class="product-image-width" id="product-image-width"
                                               min="30"
                                               max="150"
                                               name="product_image_width"
                                               value="<?php echo $this->settings->get_params( 'followup_email', 'product_image_width' ); ?>">
                                        <label for="product-image-width"
                                               class="vi-ui label"><?php esc_html_e( 'px', 'woocommerce-photo-reviews' ) ?></label>
                                    </div>

                                </td>
                            </tr>
                        </table>
                    </div>
                    <table class="form-table">
                        <tr>
                            <th>
                                <label for="auto_login"><?php esc_html_e( 'Auto login', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" id="auto_login"
                                           name="auto_login"
                                           value="1" <?php checked( $this->settings->get_params( 'followup_email', 'auto_login' ), '1' ) ?>>
                                    <label for="auto_login"><?php esc_html_e( 'Automatically log customers into their accounts when clicking on button "Review Now" in their review reminder emails. The link is onetime-use and will expire after 30 days if not used.', 'woocommerce-photo-reviews' ) ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Disable auto login if users have roles:', 'woocommerce-photo-reviews' ); ?></th>
                            <td>
                                <select name="auto_login_exclude[]" class="vi-ui fluid dropdown" multiple>
									<?php
									$auto_login_exclude = $this->settings->get_params( 'followup_email', 'auto_login_exclude' );
									$wp_roles           = wp_roles()->roles;
									if ( is_array( $wp_roles ) && count( $wp_roles ) ) {
										foreach ( $wp_roles as $role_key => $role_value ) {
											?>
                                            <option value="<?php esc_attr_e( $role_key ) ?>"
												<?php if ( in_array( $role_key, $auto_login_exclude ) )
													echo esc_attr( 'selected' ) ?>><?php esc_html_e( $role_value['name'] ) ?></option>
											<?php
										}
									}
									?>
                                </select>
                                <p><?php esc_html_e( 'Do not use auto login function if users have roles in this group', 'woocommerce-photo-reviews' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Review page', 'woocommerce-photo-reviews' ); ?></th>
                            <td>
                                <select name="review_form_page" class="wcpr-page-search">
									<?php
									$review_form_page = $this->settings->get_params( 'followup_email', 'review_form_page' );
									if ( $review_form_page ) {
										$review_form_page_obj = get_post( $review_form_page );
										if ( $review_form_page_obj ) {
											?>
                                            <option value="<?php esc_attr_e( $review_form_page ) ?>"
                                                    selected><?php esc_html_e( $review_form_page_obj->post_title ) ?></option>
											<?php
										}
									}
									?>
                                </select>
                                <p><?php _e( 'Please select a page that you use shortcode <strong>[woocommerce_photo_reviews_form]</strong>. If empty, customers will be redirected to single product page when click on button "Review Now"', 'woocommerce-photo-reviews' ); ?></p>
                            </td>
                        </tr>
						<?php
						/*
						?>
						<tr>
							<th>
								<label for="empty_product_price"><?php esc_html_e( 'Empty product price', 'woocommerce-photo-reviews' ); ?></label>
							</th>
							<td>
						<?php
                                $this->print_default_country_flag();
                                ?>
								<input type="text" id="empty_product_price"
									   name="empty_product_price"
									   value="<?php echo $this->settings->get_params( 'followup_email', 'empty_product_price' ); ?>">
								<?php
								if ( count( $this->languages ) ) {
									foreach ( $this->languages as $key => $value ) {
						$this->print_other_country_flag('empty_product_price',$value);
										?>
										<input id="<?php echo 'empty_product_price_' . $value ?>" type="text"
											   name="<?php echo 'empty_product_price_' . $value ?>"
											   value="<?php echo stripslashes( $this->settings->get_params( 'followup_email', 'empty_product_price', $value ) ); ?>">
										<?php
									}
								}
								?>
							</td>
						</tr>
						<?php */
						?>
                        <tr>
                            <th>
                                <label for="<?php echo esc_attr( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::set( 'my_account_order_statuses' ) ) ?>"><?php esc_html_e( 'Button Review on My account/orders', 'woocommerce-photo-reviews' ) ?></label>

                            </th>
                            <td>
                                <select name="<?php echo esc_attr( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::set( 'my_account_order_statuses', true ) ) ?>[]"
                                        multiple="multiple"
                                        id="<?php echo esc_attr( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::set( 'my_account_order_statuses' ) ) ?>"
                                        class="<?php echo esc_attr( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::set( 'my_account_order_statuses' ) ) ?> vi-ui fluid dropdown">
									<?php
									$my_account_order_statuses = $this->settings->get_params( 'my_account_order_statuses' );
									$wc_order_statuses         = wc_get_order_statuses();
									foreach ( $wc_order_statuses as $status_k => $status_v ) {
										?>
                                        <option value="<?php esc_attr_e( $status_k ) ?>" <?php if ( in_array( $status_k, $my_account_order_statuses ) ) {
											echo esc_attr( 'selected' );
										} ?>><?php esc_html_e( $status_v ) ?></option>
										<?php
									}
									?>
                                </select>
                                <p><?php esc_html_e( 'Select order statuses that you want button Review to be shown on My account/orders', 'woocommerce-photo-reviews' ) ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="vi-ui bottom tab segment" data-tab="custom_fields">
                    <table class="form-table">
                        <tr>
                            <th>
                                <label for="wcpr-custom-fields-enable"><?php esc_html_e( 'Enable', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" name="wcpr_custom_fields_enable"
                                           id="wcpr-custom-fields-enable"
                                           value="1" <?php checked( '1', $this->settings->get_params( 'custom_fields_enable' ) ) ?>><label><?php esc_html_e( 'Add optional input fields in review form and display in customers reviews', 'woocommerce-photo-reviews' ); ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="wcpr-custom-fields-from-variations"><?php esc_html_e( 'Show optional fields from product variations', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" name="wcpr_custom_fields_from_variations"
                                           id="wcpr-custom-fields-from-variations"
                                           value="1" <?php checked( '1', $this->settings->get_params( 'custom_fields_from_variations' ) ) ?>><label><?php esc_html_e( 'Create optional fields from all variations of current products', 'woocommerce-photo-reviews' ); ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="vi-ui positive message">
                                    <ul class="list">
                                        <li>
											<?php esc_html_e( '"Name" can not be empty.', 'woocommerce-photo-reviews' ); ?>
                                        </li>
                                        <li>
											<?php esc_html_e( '"Label" is used for review form. If "Label" is empty, "Name" will be used instead.', 'woocommerce-photo-reviews' ); ?>
                                        </li>
                                        <li>
											<?php esc_html_e( '"Value set" and "Units": if you want customers to enter them by themselves, leave them empty; if you want your customers to select from a set of options, enter those options by "|" separating values.', 'woocommerce-photo-reviews' ); ?>
                                        </li>
                                    </ul>
                                </div>

                            </td>
                        </tr>
                    </table>
                    <table class="vi-ui table wcpr-add-custom-field-container">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'Name', 'woocommerce-photo-reviews' ) ?></th>
                            <th><?php esc_html_e( 'Label', 'woocommerce-photo-reviews' ) ?></th>
                            <th><?php esc_html_e( 'Placeholder', 'woocommerce-photo-reviews' ) ?></th>
                            <th><?php esc_html_e( 'Value set', 'woocommerce-photo-reviews' ) ?></th>
                            <th><?php esc_html_e( 'Units', 'woocommerce-photo-reviews' ) ?></th>
							<?php
							if ( count( $this->languages ) ) {
								?>
                                <th><?php esc_html_e( 'Language', 'woocommerce-photo-reviews' ) ?></th>
								<?php
							}
							?>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php
						$custom_fields = $this->settings->get_params( 'custom_fields' );
						if ( is_array( $custom_fields ) && count( $custom_fields ) ) {
							foreach ( $custom_fields as $custom_field ) {
								?>
                                <tr>
                                    <td>
                                        <input type="text" name="wcpr_custom_fields[name][]"
                                               value="<?php echo esc_attr( $custom_field['name'] ) ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="wcpr_custom_fields[label][]"
                                               value="<?php echo esc_attr( $custom_field['label'] ) ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="wcpr_custom_fields[placeholder][]"
                                               value="<?php echo esc_attr( isset( $custom_field['placeholder'] ) ? $custom_field['placeholder'] : '' ) ?>">
                                    </td>
                                    <td>
                                        <input type="text"
                                               name="wcpr_custom_fields[value][]"
                                               value="<?php echo esc_attr( implode( '|', $custom_field['value'] ) ) ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="wcpr_custom_fields[unit][]"
                                               value="<?php echo esc_attr( implode( '|', $custom_field['unit'] ) ) ?>">
                                    </td>
									<?php
									if ( count( $this->languages ) ) {
										$language_label = $this->default_language;
										if ( isset( $this->languages_data[ $this->default_language ]['translated_name'] ) ) {
											$language_label .= '(' . $this->languages_data[ $this->default_language ]['translated_name'] . ')';
										}
										$selected_language = isset( $custom_field['language'] ) ? $custom_field['language'] : '';
										?>
                                        <td>
                                            <select name="wcpr_custom_fields[language][]">
                                                <option value=""><?php echo $language_label ?></option>
												<?php
												foreach ( $this->languages as $key => $value ) {
													$language_label = $value;
													if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
														$language_label .= '(' . $this->languages_data[ $value ]['translated_name'] . ')';
													}
													?>
                                                    <option value="<?php echo esc_attr( $value ) ?>" <?php selected( $selected_language, $value ) ?>><?php echo esc_html( $language_label ) ?></option>
													<?php
												}
												?>
                                            </select>
                                        </td>
										<?php
									}
									?>
                                    <td>
                                        <span class="vi-ui button negative wcpr-remove-custom-field"><?php esc_html_e( 'Remove', 'woocommerce-photo-reviews' ) ?></span>
                                    </td>
                                </tr>
								<?php
							}
						}
						?>
                        </tbody>
                    </table>
                    <span class="button wcpr-add-custom-field"><?php esc_html_e( 'Add field', 'woocommerce-photo-reviews' ) ?></span>
                </div>
                <div class="vi-ui bottom tab segment" data-tab="aliexpress_reviews">
                    <div class="vi-ui phrases-filter">
                        <div class="vi-ui positive message">
                            <div><?php esc_html_e( 'Search and replace strings in review author and review content when importing reviews from AliExpress', 'woocommerce-photo-reviews' ); ?></div>
                            <div>
                                <strong><?php esc_html_e( '*This feature is also used for reviews imported from AliExpress, Amazon using chrome extension', 'woocommerce-photo-reviews' ); ?></strong>
                            </div>
                        </div>
                        <table class="vi-ui table">
                            <thead>
                            <tr>
                                <th><?php esc_html_e( 'Search', 'woocommerce-photo-reviews' ); ?></th>
                                <th><?php esc_html_e( 'Case Sensitive', 'woocommerce-photo-reviews' ); ?></th>
                                <th><?php esc_html_e( 'Replace', 'woocommerce-photo-reviews' ); ?></th>
                                <th style="width: 1%"><?php esc_html_e( 'Remove', 'woocommerce-photo-reviews' ); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							$phrases_filter       = $this->settings->get_params( 'phrases_filter' );
							$phrases_filter_count = 1;
							if ( ! empty( $phrases_filter['from_string'] ) && ! empty( $phrases_filter['to_string'] ) && is_array( $phrases_filter['from_string'] ) ) {
								$phrases_filter_count = count( $phrases_filter['from_string'] );
							}
							for ( $i = 0; $i < $phrases_filter_count; $i ++ ) {
								$checked = $case_sensitive = '';
								if ( ! empty( $phrases_filter['sensitive'][ $i ] ) ) {
									$checked        = 'checked';
									$case_sensitive = 1;
								}
								?>
                                <tr class="clone-source">
                                    <td>
                                        <input type="text"
                                               value="<?php echo esc_attr( isset( $phrases_filter['from_string'][ $i ] ) ? $phrases_filter['from_string'][ $i ] : '' ) ?>"
                                               name="phrases_filter[from_string][]">
                                    </td>
                                    <td>
                                        <div class="phrases-filter-sensitive-container">
                                            <input type="checkbox"
                                                   value="1" <?php echo esc_attr( $checked ) ?>
                                                   class="phrases-filter-sensitive">
                                            <input type="hidden"
                                                   class="phrases-filter-sensitive-value"
                                                   value="<?php echo esc_attr( $case_sensitive ) ?>"
                                                   name="phrases_filter[sensitive][]">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text"
                                               placeholder="<?php esc_html_e( 'Leave blank to delete matches', 'woocommerce-photo-reviews' ); ?>"
                                               value="<?php echo esc_attr( isset( $phrases_filter['to_string'][ $i ] ) ? $phrases_filter['to_string'][ $i ] : '' ) ?>"
                                               name="phrases_filter[to_string][]">
                                    </td>
                                    <td>
                                        <button type="button"
                                                class="vi-ui button negative delete-phrases-filter-rule">
                                            <i class="dashicons dashicons-trash"></i>
                                        </button>
                                    </td>
                                </tr>
								<?php
							}
							?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <th colspan="4">
                                    <button type="button"
                                            class="vi-ui button positive add-phrases-filter-rule">
										<?php esc_html_e( 'Add', 'woocommerce-photo-reviews' ); ?>
                                    </button>
                                </th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th>
                                <label for="show_review_country"><?php esc_html_e( 'Show review country', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" name="show_review_country"
                                           id="show_review_country"
                                           value="1" <?php checked( '1', $this->settings->get_params( 'show_review_country' ) ) ?>><label><?php esc_html_e( 'This option only works for reviews imported since version 1.1.4', 'woocommerce-photo-reviews' ); ?></label>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="vi-ui bottom tab segment" data-tab="chrome_extension">
                    <div class="vi-ui positive message">
						<?php _e( 'Below options are used for importing reviews using <a href="http://downloads.villatheme.com/?download=woo-photo-reviews-extension" target="_blank">chrome extension</a>', 'woocommerce-photo-reviews' ); ?>
                        <p class="wcpr-chrome-extension-container">
                            <iframe width="560" height="315" src="https://www.youtube.com/embed/GXNRdWZQ4E0"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                        </p>
                    </div>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th>
                                <label for="secret_key"><?php esc_html_e( 'Secret key', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td class="wcpr-secretkey-container">
                                <div class="vi-ui left labeled input fluid">
                                    <label class="vi-ui label">
                                        <span class="wcpr-buttons-group">
                                            <span class="wcpr-copy-secretkey"
                                                  title="<?php esc_attr_e( 'Copy Secret key', 'woocommerce-photo-reviews' ) ?>">
                                                <i class="dashicons dashicons-admin-page"></i>
                                            </span>
                                            <span class="wcpr-generate-secretkey"
                                                  title="<?php esc_attr_e( 'Generate new key', 'woocommerce-photo-reviews' ) ?>">
                                                <i class="dashicons dashicons-image-rotate"></i>
                                            </span>
                                        </span>
                                    </label>
                                    <input type="text" name="secret_key" class="wcpr-secret-key"
                                           id="secret_key"
                                           value="<?php echo esc_attr( $this->settings->get_params( 'secret_key' ) ) ?>">
                                </div>
                                <p><?php esc_html_e( 'Please enter this key in the chrome extension to import reviews', 'woocommerce-photo-reviews' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="search_product_by"><?php esc_html_e( 'Look up for product ID by', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="search_product_by"
                                       id="search_product_by"
                                       value="<?php echo esc_attr( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::search_product_by() ) ?>">
                                <p><?php esc_html_e( 'When you import reviews, this plugin looks up for AliExpress and Amazon product ID by SKU of WooCommerce products by default. If you want to use an other post meta field, please enter post meta name here', 'woocommerce-photo-reviews' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="import_reviews_to"><?php esc_html_e( 'Import reviews to', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <select id="import_reviews_to"
                                        data-placeholder="<?php esc_html_e( 'Please Fill In Your Product Title', 'woocommerce-photo-reviews' ) ?>"
                                        name="import_reviews_to[]" multiple="multiple"
                                        class="product-search select2-selection--multiple">
									<?php
									$import_reviews_to = $this->settings->get_params( 'import_reviews_to' );
									if ( is_array( $import_reviews_to ) && count( $import_reviews_to ) ) {
										foreach ( $import_reviews_to as $import_reviews_to_id ) {
											$product = wc_get_product( $import_reviews_to_id );
											if ( $product ) {
												echo '<option selected value="' . $import_reviews_to_id . '">' . $product->get_title() . '</option>';
											}
										}
									}
									?>
                                </select>
                                <p><?php esc_html_e( 'If set, reviews imported with chrome extension will be added only to selected product(s). The "Look up for product ID by" option above will not be used.', 'woocommerce-photo-reviews' ) ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="import_reviews_status"><?php esc_html_e( 'Review status', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <select class="vi-ui dropdown" id="import_reviews_status" name="import_reviews_status">
                                    <option value="0" <?php selected( 0, $this->settings->get_params( 'import_reviews_status' ) ) ?>><?php esc_html_e( 'Pending', 'woocommerce-photo-reviews' ); ?></option>
                                    <option value="1" <?php selected( 1, $this->settings->get_params( 'import_reviews_status' ) ) ?>><?php esc_html_e( 'Approved', 'woocommerce-photo-reviews' ); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="import_reviews_verified"><?php esc_html_e( 'Set review verified', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" name="import_reviews_verified"
                                           id="import_reviews_verified"
                                           value="1" <?php checked( '1', $this->settings->get_params( 'import_reviews_verified' ) ) ?>><label><?php esc_html_e( 'Set reviews imported with chrome extension to verified reviews', 'woocommerce-photo-reviews' ); ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="import_reviews_vote"><?php esc_html_e( 'Review vote', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" name="import_reviews_vote"
                                           id="import_reviews_vote"
                                           value="1" <?php checked( '1', $this->settings->get_params( 'import_reviews_vote' ) ) ?>><label><?php esc_html_e( 'Import vote up/down count of reviews', 'woocommerce-photo-reviews' ); ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="import_reviews_download_images"><?php esc_html_e( 'Upload review images', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" name="import_reviews_download_images"
                                           id="import_reviews_download_images"
                                           value="1" <?php checked( '1', $this->settings->get_params( 'import_reviews_download_images' ) ) ?>><label><?php esc_html_e( 'If a review has images, upload them to your server instead of using the original image url', 'woocommerce-photo-reviews' ); ?></label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="import_reviews_order_info"><?php esc_html_e( 'Import order info', 'woocommerce-photo-reviews' ); ?></label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input type="checkbox" name="import_reviews_order_info"
                                           id="import_reviews_order_info"
                                           value="1" <?php checked( '1', $this->settings->get_params( 'import_reviews_order_info' ) ) ?>><label><?php esc_html_e( 'Import reviewer\' order info(except for logistics info) such as Color, Size... to Optional fields', 'woocommerce-photo-reviews' ); ?></label>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="vi-ui bottom tab segment" data-tab="share_reviews">
                    <table class="vi-ui celled table wcpr-share-reviews-table">
                        <thead>
                        <tr>
                            <th width="1%"><?php esc_html_e( 'Group no', 'woocommerce-photo-reviews' ); ?></th>
                            <th><?php esc_html_e( 'Products', 'woocommerce-photo-reviews' ); ?></th>
                            <th width="1%"><?php esc_html_e( 'Actions', 'woocommerce-photo-reviews' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php
						$share_reviews = $this->settings->get_params( 'share_reviews' );
						if ( ! count( $share_reviews ) ) {
							$share_reviews = array( array() );
						}
						$share_review_no = 0;
						foreach ( $share_reviews as $share_review ) {
							$share_review_name = "share_reviews[$share_review_no][]";
							$share_review_no ++;
							?>
                            <tr class="wcpr-share-reviews-row">
                                <td>
                                    <span class="wcpr-share-reviews-row-no"><?php echo esc_html( $share_review_no ) ?></span>
                                </td>
                                <td>
                                    <select data-placeholder="<?php esc_html_e( 'Enter product title to search', 'woocommerce-photo-reviews' ) ?>"
                                            name="<?php echo esc_attr( $share_review_name ) ?>"
                                            multiple="multiple"
                                            class="product-search select2-selection--multiple wcpr-share-reviews-products">
										<?php
										if ( count( $share_review ) ) {
											foreach ( $share_review as $share_review_id ) {
												$product = wc_get_product( $share_review_id );
												if ( $product ) {
													echo '<option selected value="' . $share_review_id . '">' . $product->get_title() . '</option>';
												}
											}
										}
										?>
                                    </select>
                                </td>
                                <td>
                                    <div>
                                        <span class="vi-ui button negative icon mini wcpr-share-reviews-remove"><i
                                                    class="icon trash"></i></span>
                                    </div>
                                </td>
                            </tr>
							<?php
						}

						?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="3"><span class="vi-ui button positive icon mini wcpr-share-reviews-add"><i
                                            class="icon add"></i></span></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="vi-ui bottom tab segment" data-tab="update">
                    <table class="form-table">
                        <tr>
                            <th>
                                <label for="auto-update-key"><?php esc_html_e( 'Auto Update Key', 'woocommerce-photo-reviews' ) ?></label>
                            </th>
                            <td>
                                <div class="fields">
                                    <div class="ten wide field">
                                        <input type="text" name="wcpr-key" id="auto-update-key"
                                               class="villatheme-autoupdate-key-field"
                                               value="<?php echo $this->settings->get_params( 'key' ); ?>">
                                    </div>
                                    <div class="six wide field">
                                        <span class="vi-ui button green villatheme-get-key-button"
                                              data-href="https://api.envato.com/authorization?response_type=code&client_id=villatheme-download-keys-6wzzaeue&redirect_uri=https://villatheme.com/update-key"
                                              data-id="21245349"><?php echo esc_html__( 'Get Key', 'woocommerce-photo-reviews' ) ?></span>
                                    </div>
                                </div>

								<?php do_action( 'woocommerce-photo-reviews_key' ) ?>
                                <p><?php _e( 'Please fill the key you get from <a target="_blank" href="https://villatheme.com/my-download">https://villatheme.com/my-download</a> to be able to automatically update WooCommerce Photo Reviews plugin. Please read <a target="_blank" href="https://villatheme.com/knowledge-base/how-to-use-auto-update-feature/">guide</a>', 'woocommerce-photo-reviews' ) ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                <p class="wcpr-button-save-settings-container">
                    <button type="submit" class="vi-ui primary button labeled icon" name="wcpr_save_settings"><i
                                class="icon save"></i>
						<?php esc_html_e( 'Save Settings', 'woocommerce-photo-reviews' ) ?>
                    </button>
                    <button class="vi-ui button  labeled icon" type="submit"
                            name="wcpr_check_key"><i class="icon save"></i>
						<?php esc_html_e( 'Save & Check Key', 'woocommerce-photo-reviews' ); ?>
                    </button>
                </p>
            </form>
        </div>
		<?php
		do_action( 'villatheme_support_woocommerce-photo-reviews' );
	}

	/**
	 * @param $folder
	 *
	 * @return string
	 */
	public static function sanitize_folder( $folder ) {
		$folder = remove_accents( stripslashes( $folder ) );
		$folder = explode( '/', $folder );
		$folder = array_map( array( 'VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Admin', 'sanitize_file_name' ), $folder );
		$folder = array_filter( $folder );

		return implode( '/', $folder );
	}

	/**Allow "{" and "}" when using sanitize_file_name for photo prefix
	 *
	 * @param $special_chars
	 *
	 * @return mixed
	 */
	public static function sanitize_file_name_chars( $special_chars ) {
		$search = array_search( '{', $special_chars );
		if ( $search !== false ) {
			unset( $special_chars[ $search ] );
		}
		$search = array_search( '}', $special_chars );
		if ( $search !== false ) {
			unset( $special_chars[ $search ] );
		}

		return $special_chars;
	}

	/**
	 * @param $name
	 *
	 * @return string
	 */
	public static function sanitize_file_name( $name ) {
		add_filter( 'sanitize_file_name_chars', array(
			'VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Admin',
			'sanitize_file_name_chars'
		) );
		$name = remove_accents( sanitize_file_name( $name ) );
		remove_filter( 'sanitize_file_name_chars', array(
			'VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Admin',
			'sanitize_file_name_chars'
		) );

		return $name;
	}

	public function save_settings() {
		global $pagenow;
		if ( $pagenow !== 'admin.php' || ! isset( $_REQUEST['page'] ) || $_REQUEST['page'] !== 'woocommerce-photo-reviews' ) {
			return;
		}
		if ( isset( $_POST['wcpr_check_key'] ) ) {
			delete_transient( '_site_transient_update_plugins' );
			delete_transient( 'villatheme_item_11292' );
			delete_option( 'woocommerce-photo-reviews_messages' );
		}

		/*wpml*/
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			global $sitepress;
			$default_lang           = $sitepress->get_default_language();
			$this->default_language = $default_lang;
			$languages              = icl_get_languages( 'skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str' );
			$this->languages_data   = $languages;
			if ( count( $languages ) ) {
				foreach ( $languages as $key => $language ) {
					if ( $key != $default_lang ) {
						$this->languages[] = $key;
					}
				}
			}
		} elseif ( class_exists( 'Polylang' ) ) {
			/*Polylang*/
			$languages    = pll_languages_list();
			$default_lang = pll_default_language( 'slug' );
			foreach ( $languages as $language ) {
				if ( $language == $default_lang ) {
					continue;
				}
				$this->languages[] = $language;
			}
		}

		global $woo_photo_reviews_settings;
		if ( get_option( 'woocommerce_enable_reviews' ) === 'no' ) {
			?>
            <div class="error">
                <p><?php esc_html_e( 'You have to enable WooCommerce product reviews on WooCommerce settings page to use WooCommerce Photo Reviews and its features!', 'woocommerce-photo-reviews' ) ?></p>
            </div>
			<?php
		}
		if ( get_option( 'woocommerce_enable_coupons' ) === 'no' ) {
			?>
            <div class="error">
                <p><?php esc_html_e( 'You have to enable the use of coupon on WooCommerce settings page to use Coupon feature!', 'woocommerce-photo-reviews' ) ?></p>
            </div>
			<?php
		}

		if ( empty( $_POST['wcpr_nonce_field'] ) || ! wp_verify_nonce( $_POST['wcpr_nonce_field'], 'wcpr_settings_page_save' ) ) {
			return;
		}
		if ( $_POST['kt_coupons_select'] === 'kt_existing_coupon' && ! isset( $_POST['kt_existing_coupons'] ) ) {
			?>
            <div class="error">
                <p><?php esc_html_e( 'Please select a coupon then save settings!', 'woocommerce-photo-reviews' ) ?></p>
            </div>
			<?php
			return;
		}
		$args = array(
			'enable'                         => isset( $_POST['wcpr-enable'] ) ? sanitize_text_field( $_POST['wcpr-enable'] ) : 'off',
			'mobile'                         => isset( $_POST['wcpr-mobile'] ) ? sanitize_text_field( $_POST['wcpr-mobile'] ) : 'off',
			'key'                            => isset( $_POST['wcpr-key'] ) ? sanitize_text_field( $_POST['wcpr-key'] ) : '',
			'photo'                          => array(
				'enable'                     => isset( $_POST['photo_reviews_options'] ) ? sanitize_text_field( $_POST['photo_reviews_options'] ) : "off",
				'maxsize'                    => isset( $_POST['image_maxsize'] ) ? absint( $_POST['image_maxsize'] ) : "",
				'maxfiles'                   => isset( $_POST['max_file_uploads'] ) ? absint( $_POST['max_file_uploads'] ) : "",
				'upload_images_requirement'  => isset( $_POST['upload_images_requirement'] ) ? stripslashes( $_POST['upload_images_requirement'] ) : "",
				'required'                   => isset( $_POST['photo_reviews_required'] ) ? sanitize_text_field( $_POST['photo_reviews_required'] ) : "off",
				'display'                    => isset( $_POST['reviews_display'] ) ? sanitize_text_field( $_POST['reviews_display'] ) : 2,
				'masonry_popup'              => isset( $_POST['masonry_popup'] ) ? sanitize_text_field( $_POST['masonry_popup'] ) : '',
				'image_popup'                => isset( $_POST['image_popup'] ) ? sanitize_text_field( $_POST['image_popup'] ) : '',
				'full_screen_mobile'         => isset( $_POST['full_screen_mobile'] ) ? sanitize_text_field( $_POST['full_screen_mobile'] ) : '',
				'col_num'                    => isset( $_POST['masonry_col_num'] ) ? sanitize_text_field( $_POST['masonry_col_num'] ) : 4,
				'col_num_mobile'             => isset( $_POST['masonry_col_num_mobile'] ) ? sanitize_text_field( $_POST['masonry_col_num_mobile'] ) : 1,
				'grid_bg'                    => isset( $_POST['masonry_grid_bg'] ) ? sanitize_text_field( $_POST['masonry_grid_bg'] ) : '',
				'grid_item_bg'               => isset( $_POST['masonry_grid_item_bg'] ) ? sanitize_text_field( $_POST['masonry_grid_item_bg'] ) : '',
				'grid_item_border_color'     => isset( $_POST['masonry_grid_item_border_color'] ) ? sanitize_text_field( $_POST['masonry_grid_item_border_color'] ) : '',
				'comment_text_color'         => isset( $_POST['masonry_comment_text_color'] ) ? sanitize_text_field( $_POST['masonry_comment_text_color'] ) : '',
				'star_color'                 => isset( $_POST['masonry_star_color'] ) ? sanitize_text_field( $_POST['masonry_star_color'] ) : '',
				'max_content_length'         => isset( $_POST['max_content_length'] ) ? sanitize_text_field( $_POST['max_content_length'] ) : '',
				'sort'                       => array(
					'time' => isset( $_POST['reviews_sort_time'] ) ? sanitize_text_field( $_POST['reviews_sort_time'] ) : 1
				),
				'rating_count'               => isset( $_POST['ratings_count'] ) ? sanitize_text_field( $_POST['ratings_count'] ) : "off",
				'rating_count_bar_color'     => isset( $_POST['rating-count-bar-color'] ) ? sanitize_text_field( $_POST['rating-count-bar-color'] ) : '',
				'filter'                     => array(
					'enable'              => isset( $_POST['filter-enable'] ) ? sanitize_text_field( $_POST['filter-enable'] ) : "off",
					'area_border_color'   => isset( $_POST['filter-area-border-color'] ) ? sanitize_text_field( $_POST['filter-area-border-color'] ) : '',
					'area_bg_color'       => isset( $_POST['filter-area-bg-color'] ) ? sanitize_text_field( $_POST['filter-area-bg-color'] ) : '',
					'button_border_color' => isset( $_POST['filter-button-border-color'] ) ? sanitize_text_field( $_POST['filter-button-border-color'] ) : '',
					'button_color'        => isset( $_POST['filter-button-color'] ) ? sanitize_text_field( $_POST['filter-button-color'] ) : '',
					'button_bg_color'     => isset( $_POST['filter-button-bg-color'] ) ? sanitize_text_field( $_POST['filter-button-bg-color'] ) : '',
				),
				'custom_css'                 => isset( $_POST['photo-reviews-css'] ) ? sanitize_textarea_field( stripslashes( $_POST['photo-reviews-css'] ) ) : "",
				'review_tab_first'           => isset( $_POST['review_tab_first'] ) ? sanitize_textarea_field( $_POST['review_tab_first'] ) : "off",
				'gdpr'                       => isset( $_POST['gdpr_policy'] ) ? sanitize_textarea_field( $_POST['gdpr_policy'] ) : "off",
				'gdpr_message'               => isset( $_POST['gdpr_message'] ) ? wp_kses_post( stripslashes( $_POST['gdpr_message'] ) ) : "",
				'overall_rating'             => isset( $_POST['overall_rating'] ) ? sanitize_text_field( $_POST['overall_rating'] ) : "off",
				'single_product_summary'     => isset( $_POST['single_product_summary'] ) ? sanitize_textarea_field( $_POST['single_product_summary'] ) : "off",
				'verified'                   => isset( $_POST['verified_type'] ) ? sanitize_text_field( $_POST['verified_type'] ) : '',
				'verified_text'              => isset( $_POST['verified_text'] ) ? sanitize_text_field( $_POST['verified_text'] ) : '',
				'verified_badge'             => isset( $_POST['verified_badge'] ) ? sanitize_text_field( $_POST['verified_badge'] ) : '',
				'verified_color'             => isset( $_POST['verified_color'] ) ? sanitize_text_field( $_POST['verified_color'] ) : '',
				'hide_name'                  => isset( $_POST['wcpr_hide_name'] ) ? sanitize_text_field( $_POST['wcpr_hide_name'] ) : "off",
				'show_review_date'           => isset( $_POST['show_review_date'] ) ? sanitize_text_field( $_POST['show_review_date'] ) : "",
				'enable_box_shadow'          => isset( $_POST['enable_box_shadow'] ) ? sanitize_text_field( $_POST['enable_box_shadow'] ) : "",
				'custom_review_date_format'  => isset( $_POST['custom_review_date_format'] ) ? sanitize_text_field( stripslashes( $_POST['custom_review_date_format'] ) ) : "",
				'helpful_button_enable'      => isset( $_POST['helpful_button_enable'] ) ? sanitize_text_field( $_POST['helpful_button_enable'] ) : "",
				'helpful_button_title'       => isset( $_POST['helpful_button_title'] ) ? sanitize_text_field( stripslashes( $_POST['helpful_button_title'] ) ) : "",
				'hide_rating_count_if_empty' => isset( $_POST['hide_rating_count_if_empty'] ) ? sanitize_text_field( $_POST['hide_rating_count_if_empty'] ) : "",
				'hide_filters_if_empty'      => isset( $_POST['hide_filters_if_empty'] ) ? sanitize_text_field( $_POST['hide_filters_if_empty'] ) : "",
			),
			'coupon'                         => array(
				'enable'                   => isset( $_POST['kt_coupons_enable'] ) ? sanitize_text_field( $_POST['kt_coupons_enable'] ) : "off",
				'require'                  => array(
					'photo'      => isset( $_POST['kt_coupons_if_photo'] ) ? sanitize_text_field( $_POST['kt_coupons_if_photo'] ) : "off",
					'min_rating' => isset( $_POST['kt_coupons_if_min_rating'] ) ? absint( sanitize_text_field( $_POST['kt_coupons_if_min_rating'] ) ) : 0,
					'owner'      => isset( $_POST['kt_coupons_if_verified'] ) ? sanitize_text_field( $_POST['kt_coupons_if_verified'] ) : "off",
					'register'   => isset( $_POST['kt_coupons_if_register'] ) ? sanitize_text_field( $_POST['kt_coupons_if_register'] ) : "off"
				),
				'form_title'               => isset( $_POST['review_form_description'] ) ? stripslashes( sanitize_text_field( $_POST['review_form_description'] ) ) : "",
				'products_gene'            => isset( $_POST['kt_products_gen_coupon'] ) ? stripslashes_deep( $_POST['kt_products_gen_coupon'] ) : array(),
				'excluded_products_gene'   => isset( $_POST['kt_excluded_products_gen_coupon'] ) ? stripslashes_deep( $_POST['kt_excluded_products_gen_coupon'] ) : array(),
				'categories_gene'          => isset( $_POST['kt_categories_gen_coupon'] ) ? stripslashes_deep( $_POST['kt_categories_gen_coupon'] ) : array(),
				'excluded_categories_gene' => isset( $_POST['kt_excluded_categories_gen_coupon'] ) ? stripslashes_deep( $_POST['kt_excluded_categories_gen_coupon'] ) : array(),
				'email'                    => array(
					'from_address' => isset( $_POST['from_address'] ) ? sanitize_text_field( $_POST['from_address'] ) : "",
					'subject'      => isset( $_POST['subject'] ) ? sanitize_text_field( $_POST['subject'] ) : "",
					'heading'      => isset( $_POST['heading'] ) ? sanitize_text_field( $_POST['heading'] ) : "",
					'content'      => isset( $_POST['content'] ) ? wp_kses_post( $_POST['content'] ) : ""
				),
				'coupon_select'            => isset( $_POST['kt_coupons_select'] ) ? sanitize_text_field( $_POST['kt_coupons_select'] ) : 'kt_generate_coupon',
				'unique_coupon'            => array(
					'discount_type'               => isset( $_POST['kt_discount_type'] ) ? sanitize_text_field( $_POST['kt_discount_type'] ) : "",
					'coupon_amount'               => isset( $_POST['kt_coupon_amount'] ) ? sanitize_text_field( $_POST['kt_coupon_amount'] ) : 0,
					'allow_free_shipping'         => isset( $_POST['kt_free_shipping'] ) ? sanitize_text_field( $_POST['kt_free_shipping'] ) : 'no',
					'expiry_date'                 => isset( $_POST['kt_expiry_date'] ) ? sanitize_text_field( $_POST['kt_expiry_date'] ) : '',
					'min_spend'                   => isset( $_POST['kt_min_spend'] ) ? wc_format_decimal( $_POST['kt_min_spend'] ) : "",
					'max_spend'                   => isset( $_POST['kt_max_spend'] ) ? wc_format_decimal( $_POST['kt_max_spend'] ) : "",
					'individual_use'              => isset( $_POST['kt_individual_use'] ) ? sanitize_text_field( $_POST['kt_individual_use'] ) : "no",
					'exclude_sale_items'          => isset( $_POST['kt_exclude_sale_items'] ) ? sanitize_text_field( $_POST['kt_exclude_sale_items'] ) : "no",
					'limit_per_coupon'            => isset( $_POST['kt_limit_per_coupon'] ) ? absint( $_POST['kt_limit_per_coupon'] ) : "",
					'limit_to_x_items'            => isset( $_POST['kt_limit_to_x_items'] ) ? absint( $_POST['kt_limit_to_x_items'] ) : "",
					'limit_per_user'              => isset( $_POST['kt_limit_per_user'] ) ? absint( $_POST['kt_limit_per_user'] ) : "",
					'product_ids'                 => isset( $_POST['kt_product_ids'] ) ? stripslashes_deep( $_POST['kt_product_ids'] ) : array(),
					'excluded_product_ids'        => isset( $_POST['kt_excluded_product_ids'] ) ? stripslashes_deep( $_POST['kt_excluded_product_ids'] ) : array(),
					'product_categories'          => isset( $_POST['kt_product_categories'] ) ? stripslashes_deep( $_POST['kt_product_categories'] ) : array(),
					'excluded_product_categories' => isset( $_POST['kt_excluded_product_categories'] ) ? stripslashes_deep( $_POST['kt_excluded_product_categories'] ) : array(),
					'coupon_code_prefix'          => isset( $_POST['kt_coupon_code_prefix'] ) ? sanitize_text_field( $_POST['kt_coupon_code_prefix'] ) : ""
				),
				'existing_coupon'          => isset( $_POST['kt_existing_coupons'] ) ? sanitize_text_field( $_POST['kt_existing_coupons'] ) : ""
			),
			'followup_email'                 => array(
				'enable'                      => isset( $_POST['follow_up_email_enable'] ) ? sanitize_text_field( $_POST['follow_up_email_enable'] ) : "off",
				'subject'                     => isset( $_POST['follow_up_email_subject'] ) ? sanitize_text_field( $_POST['follow_up_email_subject'] ) : "",
				'content'                     => isset( $_POST['follow_up_email_content'] ) ? wp_kses_post( $_POST['follow_up_email_content'] ) : "",
				'heading'                     => isset( $_POST['follow_up_email_heading'] ) ? sanitize_text_field( $_POST['follow_up_email_heading'] ) : "",
				'from_address'                => isset( $_POST['follow_up_email_from_address'] ) ? sanitize_text_field( $_POST['follow_up_email_from_address'] ) : "",
				'exclude_addresses'           => isset( $_POST['exclude_addresses'] ) ? array_filter( array_map( 'sanitize_email', array_unique( $_POST['exclude_addresses'] ) ) ) : array(),
				'amount'                      => isset( $_POST['email_time_amount'] ) ? sanitize_text_field( $_POST['email_time_amount'] ) : "",
				'unit'                        => isset( $_POST['email_time_unit'] ) ? sanitize_text_field( $_POST['email_time_unit'] ) : "",
				'products_restriction'        => isset( $_POST['follow-up-email-products-restriction'] ) ? stripslashes_deep( $_POST['follow-up-email-products-restriction'] ) : array(),
				'excluded_categories'         => isset( $_POST['follow-up-email-excluded-categories'] ) ? stripslashes_deep( $_POST['follow-up-email-excluded-categories'] ) : array(),
				'exclude_non_coupon_products' => isset( $_POST['exclude_non_coupon_products'] ) ? stripslashes_deep( $_POST['exclude_non_coupon_products'] ) : 'off',
				'review_button'               => isset( $_POST['button-review-now'] ) ? sanitize_text_field( $_POST['button-review-now'] ) : "",
//				'empty_product_price'               => isset( $_POST['empty_product_price'] ) ? sanitize_text_field( $_POST['empty_product_price'] ) : "",
				'review_button_color'         => isset( $_POST['button-review-now-color'] ) ? sanitize_text_field( $_POST['button-review-now-color'] ) : "",
				'review_button_bg_color'      => isset( $_POST['button-review-now-bg-color'] ) ? sanitize_text_field( $_POST['button-review-now-bg-color'] ) : "",
				'review_form_page'            => isset( $_POST['review_form_page'] ) ? sanitize_text_field( $_POST['review_form_page'] ) : "",
				'product_image_width'         => isset( $_POST['product_image_width'] ) ? absint( sanitize_text_field( $_POST['product_image_width'] ) ) : 150,
				'auto_login'                  => isset( $_POST['auto_login'] ) ? sanitize_text_field( $_POST['auto_login'] ) : "",
				'auto_login_exclude'          => isset( $_POST['auto_login_exclude'] ) ? stripslashes_deep( $_POST['auto_login_exclude'] ) : array(),
				'order_statuses'              => isset( $_POST['wcpr_order_statuses'] ) ? stripslashes_deep( $_POST['wcpr_order_statuses'] ) : array(),
			),
			'pagination_ajax'                => isset( $_POST['wcpr_pagination_ajax'] ) ? sanitize_text_field( $_POST['wcpr_pagination_ajax'] ) : "",
			'loadmore_button'                => isset( $_POST['wcpr_loadmore_button'] ) ? sanitize_text_field( $_POST['wcpr_loadmore_button'] ) : "",
			'reviews_container'              => isset( $_POST['wcpr_reviews_container'] ) ? sanitize_text_field( $_POST['wcpr_reviews_container'] ) : "",
			'reviews_anchor_link'            => isset( $_POST['wcpr_reviews_anchor_link'] ) ? sanitize_text_field( $_POST['wcpr_reviews_anchor_link'] ) : "",
			'set_email_restriction'          => isset( $_POST['set_email_restriction'] ) ? sanitize_text_field( $_POST['set_email_restriction'] ) : "",
			'multi_language'                 => isset( $_POST['wcpr_multi_language'] ) ? sanitize_text_field( $_POST['wcpr_multi_language'] ) : "",
			/*image caption*/
			'image_caption_enable'           => isset( $_POST['image_caption_enable'] ) ? sanitize_text_field( $_POST['image_caption_enable'] ) : "",
			'image_caption_font_size'        => isset( $_POST['image_caption_font_size'] ) ? sanitize_text_field( $_POST['image_caption_font_size'] ) : "",
			'image_caption_color'            => isset( $_POST['image_caption_color'] ) ? sanitize_text_field( $_POST['image_caption_color'] ) : "",
			'image_caption_bg_color'         => isset( $_POST['image_caption_bg_color'] ) ? sanitize_text_field( $_POST['image_caption_bg_color'] ) : "",
			'custom_fields_enable'           => isset( $_POST['wcpr_custom_fields_enable'] ) ? sanitize_text_field( $_POST['wcpr_custom_fields_enable'] ) : "",
			'custom_fields_from_variations'  => isset( $_POST['wcpr_custom_fields_from_variations'] ) ? sanitize_text_field( $_POST['wcpr_custom_fields_from_variations'] ) : "",
			'allow_empty_comment'            => isset( $_POST['allow_empty_comment'] ) ? sanitize_text_field( $_POST['allow_empty_comment'] ) : "",
			'import_upload_folder'           => isset( $_POST['import_upload_folder'] ) ? self::sanitize_folder( $_POST['import_upload_folder'] ) : "",
			'import_upload_prefix'           => isset( $_POST['import_upload_prefix'] ) ? self::sanitize_file_name( $_POST['import_upload_prefix'] ) : "",
			'user_upload_folder'             => isset( $_POST['user_upload_folder'] ) ? self::sanitize_folder( $_POST['user_upload_folder'] ) : "",
			'user_upload_prefix'             => isset( $_POST['user_upload_prefix'] ) ? self::sanitize_file_name( $_POST['user_upload_prefix'] ) : "",
			'filter_default_image'           => isset( $_POST['filter_default_image'] ) ? sanitize_text_field( $_POST['filter_default_image'] ) : "",
			'filter_default_verified'        => isset( $_POST['filter_default_verified'] ) ? sanitize_text_field( $_POST['filter_default_verified'] ) : "",
			'filter_default_rating'          => isset( $_POST['filter_default_rating'] ) ? sanitize_text_field( $_POST['filter_default_rating'] ) : "",
			'thank_you_message'              => isset( $_POST['thank_you_message'] ) ? sanitize_text_field( stripslashes( $_POST['thank_you_message'] ) ) : "",
			'thank_you_message_coupon'       => isset( $_POST['thank_you_message_coupon'] ) ? sanitize_text_field( stripslashes( $_POST['thank_you_message_coupon'] ) ) : "",
			'review_title_enable'            => isset( $_POST['review_title_enable'] ) ? sanitize_text_field( $_POST['review_title_enable'] ) : "",
			'review_title_placeholder'       => isset( $_POST['review_title_placeholder'] ) ? sanitize_text_field( $_POST['review_title_placeholder'] ) : "",
			'show_review_country'            => isset( $_POST['show_review_country'] ) ? sanitize_text_field( $_POST['show_review_country'] ) : "",
			'restrict_number_of_reviews'     => isset( $_POST['restrict_number_of_reviews'] ) ? sanitize_text_field( $_POST['restrict_number_of_reviews'] ) : "",
			'my_account_order_statuses'      => isset( $_POST['wcpr_my_account_order_statuses'] ) ? stripslashes_deep( $_POST['wcpr_my_account_order_statuses'] ) : array(),
			'email_template'                 => isset( $_POST['email_template'] ) ? sanitize_text_field( $_POST['email_template'] ) : '',
			'reminder_email_template'        => isset( $_POST['reminder_email_template'] ) ? sanitize_text_field( $_POST['reminder_email_template'] ) : '',
			'import_reviews_to'              => isset( $_POST['import_reviews_to'] ) ? stripslashes_deep( $_POST['import_reviews_to'] ) : array(),
			'search_product_by'              => isset( $_POST['search_product_by'] ) ? sanitize_text_field( $_POST['search_product_by'] ) : '',
			'secret_key'                     => isset( $_POST['secret_key'] ) ? sanitize_text_field( $_POST['secret_key'] ) : '',
			'import_reviews_status'          => isset( $_POST['import_reviews_status'] ) ? sanitize_text_field( $_POST['import_reviews_status'] ) : '',
			'import_reviews_verified'        => isset( $_POST['import_reviews_verified'] ) ? sanitize_text_field( $_POST['import_reviews_verified'] ) : '',
			'import_reviews_vote'            => isset( $_POST['import_reviews_vote'] ) ? sanitize_text_field( $_POST['import_reviews_vote'] ) : '',
			'import_reviews_download_images' => isset( $_POST['import_reviews_download_images'] ) ? sanitize_text_field( $_POST['import_reviews_download_images'] ) : '',
			'import_reviews_order_info'      => isset( $_POST['import_reviews_order_info'] ) ? sanitize_text_field( $_POST['import_reviews_order_info'] ) : '',
			'share_reviews'                  => isset( $_POST['share_reviews'] ) ? array_values(stripslashes_deep( $_POST['share_reviews'] )) : array(),
		);
		if ( ! empty( $_POST['phrases_filter']['from_string'] ) && is_array( $_POST['phrases_filter']['from_string'] ) ) {
			$strings          = $_POST['phrases_filter']['from_string'];
			$strings_replaces = array(
				'from_string' => array(),
				'to_string'   => array(),
				'sensitive'   => array(),
			);
			$count            = count( $strings );
			for ( $i = 0; $i < $count; $i ++ ) {
				if ( $strings[ $i ] !== '' ) {
					$strings_replaces['from_string'][] = $_POST['phrases_filter']['from_string'][ $i ];
					$strings_replaces['to_string'][]   = $_POST['phrases_filter']['to_string'][ $i ];
					$strings_replaces['sensitive'][]   = $_POST['phrases_filter']['sensitive'][ $i ];
				}
			}
			$args['phrases_filter'] = $strings_replaces;
		}
		$wcpr_custom_fields = isset( $_POST['wcpr_custom_fields'] ) ? stripslashes_deep( $_POST['wcpr_custom_fields'] ) : array();
		$custom_fields      = array();
		if ( isset( $wcpr_custom_fields['name'] ) && is_array( $wcpr_custom_fields['name'] ) && count( $wcpr_custom_fields['name'] ) ) {
			foreach ( $wcpr_custom_fields['name'] as $custom_fields_id_k => $custom_fields_id_v ) {
				if ( empty( $custom_fields_id_v ) ) {
					continue;
				}
				$custom_fields[] = array(
					'name'        => $wcpr_custom_fields['name'][ $custom_fields_id_k ],
					'label'       => $wcpr_custom_fields['label'][ $custom_fields_id_k ],
					'placeholder' => $wcpr_custom_fields['placeholder'][ $custom_fields_id_k ],
					'language'    => isset( $wcpr_custom_fields['language'][ $custom_fields_id_k ] ) ? $wcpr_custom_fields['language'][ $custom_fields_id_k ] : '',
					'value'       => $wcpr_custom_fields['value'][ $custom_fields_id_k ] ? explode( '|', $wcpr_custom_fields['value'][ $custom_fields_id_k ] ) : array(),
					'unit'        => $wcpr_custom_fields['unit'][ $custom_fields_id_k ] ? explode( '|', $wcpr_custom_fields['unit'][ $custom_fields_id_k ] ) : array(),
				);
			}
		}
		$args['custom_fields'] = $custom_fields;
		if ( count( $this->languages ) ) {
			foreach ( $this->languages as $key => $value ) {
				$args['photo'][ 'upload_images_requirement_' . $value ] = isset( $_POST[ 'upload_images_requirement_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'upload_images_requirement_' . $value ] ) ) : "";
				$args['photo'][ 'gdpr_message_' . $value ]              = isset( $_POST[ 'gdpr_message_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'gdpr_message_' . $value ] ) ) : "";
				$args['coupon'][ 'email_' . $value ]                    = array(
					'subject' => isset( $_POST[ 'subject_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'subject_' . $value ] ) ) : "",
					'heading' => isset( $_POST[ 'heading_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'heading_' . $value ] ) ) : "",
					'content' => isset( $_POST[ 'content_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'content_' . $value ] ) ) : "",
				);
				$args['followup_email'][ 'subject_' . $value ]          = isset( $_POST[ 'follow_up_email_subject_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'follow_up_email_subject_' . $value ] ) ) : "";
				$args['followup_email'][ 'heading_' . $value ]          = isset( $_POST[ 'follow_up_email_heading_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'follow_up_email_heading_' . $value ] ) ) : "";
				$args['followup_email'][ 'content_' . $value ]          = isset( $_POST[ 'follow_up_email_content_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'follow_up_email_content_' . $value ] ) ) : "";
				$args['followup_email'][ 'review_button_' . $value ]    = isset( $_POST[ 'button-review-now_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'button-review-now_' . $value ] ) ) : "";
				$args['photo'][ 'helpful_button_title_' . $value ]      = isset( $_POST[ 'helpful_button_title_' . $value ] ) ? sanitize_text_field( stripslashes( $_POST[ 'helpful_button_title_' . $value ] ) ) : "";
				$args['coupon'][ 'form_title_' . $value ]               = isset( $_POST[ 'review_form_description_' . $value ] ) ? wp_kses_post( stripslashes( $_POST[ 'review_form_description_' . $value ] ) ) : "";
				$args[ 'thank_you_message_' . $value ]                  = isset( $_POST[ 'thank_you_message_' . $value ] ) ? sanitize_text_field( stripslashes( $_POST[ 'thank_you_message_' . $value ] ) ) : "";
				$args[ 'thank_you_message_coupon_' . $value ]           = isset( $_POST[ 'thank_you_message_coupon_' . $value ] ) ? sanitize_text_field( stripslashes( $_POST[ 'thank_you_message_coupon_' . $value ] ) ) : "";
				$args[ 'review_title_placeholder_' . $value ]           = isset( $_POST[ 'review_title_placeholder_' . $value ] ) ? sanitize_text_field( stripslashes( $_POST[ 'review_title_placeholder_' . $value ] ) ) : "";
				$args[ 'email_template_' . $value ]                     = isset( $_POST[ 'email_template_' . $value ] ) ? sanitize_text_field( $_POST[ 'email_template_' . $value ] ) : "";
				$args[ 'reminder_email_template_' . $value ]            = isset( $_POST[ 'reminder_email_template_' . $value ] ) ? sanitize_text_field( $_POST[ 'reminder_email_template_' . $value ] ) : "";
			}
		}
		update_option( '_wcpr_nkt_setting', $args );
		$woo_photo_reviews_settings = $args;
		$this->settings             = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance( true );
		add_action( 'admin_notices', array( $this, 'settings_saved_message' ) );
	}

	public function settings_saved_message() {
		?>
        <div class="updated">
            <p><?php esc_html_e( 'Your settings have been saved!', 'woocommerce-photo-reviews' ) ?></p>
        </div>
		<?php
	}

	public function add_meta_box_title() {
		add_meta_box(
			'wcpr-review-title', __( 'Review Title', 'woocommerce-photo-reviews' ), array(
			$this,
			'add_meta_box_title_callback'
		), 'comment', 'normal', 'high'
		);
	}

	public function add_meta_box_photo() {
		add_meta_box(
			'wcpr-comment-photos', __( 'Photos', 'woocommerce-photo-reviews' ), array(
			$this,
			'add_meta_box_photo_callback'
		), 'comment', 'normal', 'high'
		);
	}

	public function save_comment_meta( $comment_id ) {
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( $screen && $screen->id == 'comment' ) {
				if ( $_POST['photo-reviews-id'] ) {
					update_comment_meta( $comment_id, 'reviews-images', $_POST['photo-reviews-id'] );
				} elseif ( get_comment_meta( $comment_id, 'reviews-images', true ) ) {
					delete_comment_meta( $comment_id, 'reviews-images' );
				}
				$review_title = isset( $_POST['wcpr_review_title'] ) ? sanitize_text_field( $_POST['wcpr_review_title'] ) : '';
				if ( $review_title ) {
					update_comment_meta( $comment_id, 'wcpr_review_title', $review_title );
				} elseif ( get_comment_meta( $comment_id, 'wcpr_review_title', true ) ) {
					delete_comment_meta( $comment_id, 'wcpr_review_title' );
				}
			}
		}
	}

	public function add_meta_box_title_callback( $comment ) {
		$review_title = get_comment_meta( $comment->comment_ID, 'wcpr_review_title', true );

		?>
        <div class="wcpr-review-title-container" title="<?php echo esc_attr( $review_title ); ?>">
            <input class="wcpr-review-title" type="text" name="wcpr_review_title"
                   value="<?php echo esc_attr( htmlentities( $review_title ) ); ?>">
        </div>
		<?php

	}

	public function add_meta_box_photo_callback( $comment ) {
		wp_nonce_field( 'wcpr_edit_comment_save', 'wcpr_edit_comment_nonce_field' );
		echo '<div class="kt-wc-reviews-images-wrap-wrap ui-sortable">';
		if ( get_comment_meta( $comment->comment_ID, 'reviews-images' ) ) {
			$image_post_ids = get_comment_meta( $comment->comment_ID, 'reviews-images', true );
			foreach ( $image_post_ids as $image_post_id ) {
				if ( ! wc_is_valid_url( $image_post_id ) ) {
					$image_data = wp_get_attachment_metadata( $image_post_id );
					$image      = get_post( $image_post_id );
					?>
                    <div class="wcpr-review-image-container">
                        <div class="wcpr-review-image-wrap">
							<?php
							if ( $this->settings->get_params( 'image_caption_enable' ) ) {
								?>
                                <div class="wcpr-review-image-caption"><?php echo $image->post_excerpt ?></div>
								<?php
							}
							?>
                            <img style="border: 1px solid;" class="review-images"
                                 data-image_id="<?php echo $image_post_id ?>"
                                 src="<?php echo wp_get_attachment_thumb_url( $image_post_id ); ?>"/>

                            <input class="photo-reviews-id" name="photo-reviews-id[]" type="hidden"
                                   value="<?php echo esc_attr( $image_post_id ); ?>"/>
                        </div>
                        <a class="wcpr-remove-image" href="#">
							<?php _e( 'Remove' ) ?>
                        </a>
                    </div>
					<?php
				} else {
					?>
                    <div class="wcpr-review-image-container">
                        <a href="<?php echo $image_post_id; ?>"
                           data-lightbox="photo-reviews-<?php echo $comment->comment_ID; ?>"
                           data-img_post_id="<?php echo $image_post_id; ?>"><img style="border: 1px solid;"
                                                                                 class="review-images"
                                                                                 src="<?php echo( $image_post_id ); ?>"/></a>
                        <input class="photo-reviews-id" name="photo-reviews-id[]" type="hidden"
                               value="<?php echo esc_attr( $image_post_id ); ?>"/>
                        <a class="wcpr-remove-image" href="#">
							<?php _e( 'Remove' ) ?>
                        </a>
                    </div>
					<?php
				}
			}
		}
		?>
        <div id="wcpr-new-image" style="float: left;">
        </div>
        <a href="#"
           class="button-primary wcpr-upload-custom-img"><?php esc_html_e( 'Add Image', 'woocommerce-photo-reviews' ); ?></a>
		<?php
		echo '</div>';
	}

	public function load_photos_in_comment_list() {
		$screen = get_current_screen();
		add_filter( "manage_{$screen->id}_columns", array( $this, 'add_columns' ) );
	}

	public function add_columns( $cols ) {
		$cols['wcpr_title']    = __( 'Review Title', 'woocommerce-photo-reviews' );
		$cols['wcpr_photos']   = __( 'Photos', 'woocommerce-photo-reviews' );
		$cols['wcpr_rating']   = __( 'Rating', 'woocommerce-photo-reviews' );
		$cols['wcpr_verified'] = __( 'Verified', 'woocommerce-photo-reviews' );

		return $cols;
	}

	public function register_bulk_actions( $bulk_actions ) {
		$bulk_actions['wcpr_verified']       = __( 'Set review verified', 'woocommerce-photo-reviews' );
		$bulk_actions['wcpr_phrases_filter'] = __( 'Apply Phrases Filter', 'woocommerce-photo-reviews' );

		return $bulk_actions;
	}

	public function bulk_action_handler( $redirect_to, $doaction, $comment_ids ) {
		switch ( $doaction ) {
			case 'wcpr_verified':
				$count = 0;
				if ( count( $comment_ids ) ) {
					foreach ( $comment_ids as $comment_id ) {
						if ( ! get_comment_meta( $comment_id, 'verified', true ) ) {
							update_comment_meta( $comment_id, 'verified', 1 );
							$count ++;
						}
					}
					$redirect_to = add_query_arg( 'wcpr_verified', $count, $redirect_to );
				}
				break;
			case 'wcpr_phrases_filter':
				$count = count( $comment_ids );
				if ( $count ) {
					$phrases_filter = $this->settings->get_params( 'phrases_filter' );
					if ( isset( $phrases_filter['to_string'] ) && is_array( $phrases_filter['to_string'] ) && $str_replace_count = count( $phrases_filter['to_string'] ) ) {
						foreach ( $comment_ids as $comment_id ) {
							$comment = get_comment( $comment_id );
							if ( $comment ) {
								$comment_author  = $comment->comment_author;
								$comment_content = $comment->comment_content;
								for ( $i = 0; $i < $str_replace_count; $i ++ ) {
									if ( $phrases_filter['sensitive'][ $i ] ) {
										$comment_author  = function_exists( 'mb_str_replace' ) ? mb_str_replace( $phrases_filter['from_string'][ $i ], $phrases_filter['to_string'][ $i ], $comment_author ) : str_replace( $phrases_filter['from_string'][ $i ], $phrases_filter['to_string'][ $i ], $comment_author );
										$comment_content = function_exists( 'mb_str_replace' ) ? mb_str_replace( $phrases_filter['from_string'][ $i ], $phrases_filter['to_string'][ $i ], $comment_content ) : str_replace( $phrases_filter['from_string'][ $i ], $phrases_filter['to_string'][ $i ], $comment_content );
									} else {
										$comment_author  = str_ireplace( $phrases_filter['from_string'][ $i ], $phrases_filter['to_string'][ $i ], $comment_author );
										$comment_content = str_ireplace( $phrases_filter['from_string'][ $i ], $phrases_filter['to_string'][ $i ], $comment_content );
									}
								}
								wp_update_comment( array(
									'comment_ID'      => $comment_id,
									'comment_author'  => $comment_author,
									'comment_content' => $comment_content,
								) );
							}
						}
					}
					$redirect_to = add_query_arg( 'wcpr_phrases_filter', $count, $redirect_to );
				}
				break;
			default:
		}

		return $redirect_to;
	}

	public function column_callback( $col, $comment_id ) {
		switch ( $col ) {
			case 'wcpr_title':
				$review_title = get_comment_meta( $comment_id, 'wcpr_review_title', true );
				if ( $review_title ) {
					?>
                    <div class="wcpr-review-title"><strong><?php echo esc_html( $review_title ); ?></strong></div>
					<?php
				}
				break;
			case 'wcpr_photos':
				if ( ( $image_post_ids = get_comment_meta( $comment_id, 'reviews-images', true ) ) && sizeof( $image_post_ids ) ) {
					echo '<div class="kt-wc-reviews-images-wrap-wrap">';
					foreach ( $image_post_ids as $image_post_id ) {
						if ( ! wc_is_valid_url( $image_post_id ) ) {
							$image_data = wp_get_attachment_metadata( $image_post_id );
							?>
                            <a href="<?php echo( isset( $image_data['sizes']['wcpr-photo-reviews'] ) ? wp_get_attachment_image_url( $image_post_id, 'wcpr-photo-reviews' ) : ( isset( $image_data['sizes']['medium_large'] ) ? wp_get_attachment_image_url( $image_post_id, 'medium_large' ) : ( isset( $image_data['sizes']['medium'] ) ? wp_get_attachment_image_url( $image_post_id, 'medium' ) : wp_get_attachment_thumb_url( $image_post_id ) ) ) ); ?>"
                               data-lightbox="photo-reviews-<?php echo $comment_id; ?>"><img
                                        style="border: 1px solid;"
                                        class="review-images"
                                        src="<?php echo wp_get_attachment_thumb_url( $image_post_id ); ?>"/></a>
							<?php
						} else {
							?>
                            <a href="<?php echo $image_post_id; ?>"
                               data-lightbox="photo-reviews-<?php echo $comment_id; ?>"><img
                                        style="border: 1px solid;"
                                        class="review-images"
                                        src="<?php echo $image_post_id; ?>"/></a>

							<?php
						}
					}
					echo '</div>';
				}
				break;
			case 'wcpr_rating':
				$rating = get_comment_meta( $comment_id, 'rating', true );
				if ( $rating > 0 ) {
					echo wc_get_rating_html( $rating );
				}
				break;
			case 'wcpr_verified':
				$verified = get_comment_meta( $comment_id, 'verified', true );
				if ( $verified ) {
					?>
                    <span class="dashicons dashicons-yes" style="color:#1aba7b;"></span>
					<?php
				}
				break;
		}
	}

	public function add_menu_system_status() {
		add_submenu_page(
			'woocommerce-photo-reviews', __( 'Status', 'woocommerce-photo-reviews' ), __( 'System Status', 'woocommerce-photo-reviews' ), 'manage_options', 'kt-wcpr-status', array(
				$this,
				'status'
			)
		);
	}

	public function add_menu() {
		add_menu_page(
			esc_html__( 'WooCommerce Photo Reviews', 'woocommerce-photo-reviews' ), esc_html__( 'Photo Reviews', 'woocommerce-photo-reviews' ), 'manage_options', 'woocommerce-photo-reviews', array(
			$this,
			'settings_page'
		), 'dashicons-star-filled', 2
		);

		add_submenu_page(
			'woocommerce-photo-reviews', __( 'Add A Review', 'woocommerce-photo-reviews' ), __( 'Add A Review', 'woocommerce-photo-reviews' ), 'manage_options', 'kt-wcpr-add-review', array(
				$this,
				'admin_add_review'
			)
		);
	}


	public function admin_enqueue() {
		global $wp_version;
		$page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		if ( in_array( $page, array( 'woocommerce-photo-reviews', 'kt-wcpr-status', 'kt-wcpr-add-review' ) ) ) {
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
			wp_enqueue_script( 'wcpr-semantic-js-accordion', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'accordion.min.js', array( 'jquery' ) );
			wp_enqueue_style( 'wcpr-semantic-css-accordion', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'accordion.min.css' );
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

			wp_enqueue_script( 'wcpr-jquery-address', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'jquery.address-1.6.min.js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_enqueue_script( 'wcpr-semantic-dropdown-js', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'dropdown.js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_enqueue_script( 'wcpr-transition', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'transition.min.js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_enqueue_style( 'wcpr-verified-badge-icon', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'woocommerce-photo-reviews-badge.min.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_enqueue_script( 'wcpr_admin_select2_script', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'select2.js', array( 'jquery' ) );
			wp_enqueue_style( 'wcpr_admin_seletct2', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'select2.min.css' );
			/*Color picker*/
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
			/*Fix wpColorPickerL10n*/
			if ( version_compare( $wp_version, '5.4.99', '>=' ) ) {
				wp_localize_script(
					'wp-color-picker',
					'wpColorPickerL10n',
					array(
						'clear'            => __( 'Clear' ),
						'clearAriaLabel'   => __( 'Clear color' ),
						'defaultString'    => __( 'Default' ),
						'defaultAriaLabel' => __( 'Select default color' ),
						'pick'             => __( 'Select Color' ),
						'defaultLabel'     => __( 'Color value' ),
					) );
			}
			wp_enqueue_script(
				'iris', admin_url( 'js/iris.min.js' ), array(
				'jquery-ui-draggable',
				'jquery-ui-slider',
				'jquery-touch-punch'
			), false, 1 );
			wp_enqueue_script( 'woocommerce-photo-reviews-alpha-color-picker', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'wp-color-picker-alpha.min.js', array( 'wp-color-picker' ) );
			if ( $page == 'woocommerce-photo-reviews' ) {
				wp_enqueue_script( 'wcpr_admin_script', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'admin-javascript.js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
				ob_start();
				?>
                <tr>
                    <td><input type="text" name="wcpr_custom_fields[name][]">
                    </td>
                    <td><input type="text" name="wcpr_custom_fields[label][]">
                    </td>
                    <td><input type="text" name="wcpr_custom_fields[placeholder][]">
                    </td>
                    <td><input type="text" name="wcpr_custom_fields[value][]">
                    </td>
                    <td><input type="text" name="wcpr_custom_fields[unit][]">
                    </td>
					<?php
					if ( count( $this->languages ) ) {
						$language_label = $this->default_language;
						if ( isset( $this->languages_data[ $this->default_language ]['translated_name'] ) ) {
							$language_label .= '(' . $this->languages_data[ $this->default_language ]['translated_name'] . ')';
						}
						?>
                        <td>
                            <select name="wcpr_custom_fields[language][]">
                                <option value=""><?php echo $language_label ?></option>
								<?php
								foreach ( $this->languages as $key => $value ) {
									$language_label = $value;
									if ( isset( $this->languages_data[ $value ]['translated_name'] ) ) {
										$language_label .= '(' . $this->languages_data[ $value ]['translated_name'] . ')';
									}
									?>
                                    <option value="<?php echo esc_attr( $value ) ?>"><?php echo esc_html( $language_label ) ?></option>
									<?php
								}
								?>
                            </select>
                        </td>
						<?php
					}
					?>
                    <td>
                        <span class="vi-ui button negative wcpr-remove-custom-field"><?php esc_html_e( 'Remove', 'woocommerce-photo-reviews' ) ?></span>
                    </td>
                </tr>
				<?php
				$add_field_html = ob_get_clean();
				wp_localize_script( 'wcpr_admin_script', 'woo_photo_reviews_params_admin',
					array(
						'url'              => admin_url( 'admin-ajax.php' ),
						'text_please_wait' => __( 'Please wait...', 'woocommerce-photo-reviews' ),
						'add_field_html'   => $add_field_html,
					)
				);
				wp_enqueue_style( 'wcpr_admin_style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'admin-css.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
				$css = '';
				if ( $this->settings->get_params( 'photo', 'verified_color' ) ) {
					$css .= '.equal.width.fields .field .verified-text,.equal.width.fields .field .wcpr-verified-badge-wrap{color:' . $this->settings->get_params( 'photo', 'verified_color' ) . ';}';
				}
				wp_add_inline_style( 'wcpr_admin_style', $css );
			} elseif ( $page == 'kt-wcpr-add-review' ) {
				wp_enqueue_script( 'media-upload' );
				if ( ! did_action( 'wp_enqueue_media' ) ) {
					wp_enqueue_media();
				}
				wp_enqueue_script( 'wcpr_admin_add_review_script', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'admin-add-review-javascript.js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
				wp_enqueue_style( 'wcpr_admin_add_review_style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'admin-add-review-css.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			}
		}
		$screen = get_current_screen();
		if ( $screen->id == 'comment' ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_style( 'wcpr_admin_comment', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'comment_screen.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			if ( $this->settings->get_params( 'image_caption_enable' ) ) {
				$css_inline = '.wcpr-review-image-container .wcpr-review-image-caption{';
				$css_inline .= 'font-size:' . $this->settings->get_params( 'image_caption_font_size' ) . 'px;';
				$css_inline .= 'color:' . $this->settings->get_params( 'image_caption_color' ) . ';';
				$css_inline .= 'background-color:' . $this->settings->get_params( 'image_caption_bg_color' ) . ';';
				$css_inline .= '}';
				wp_add_inline_style( 'wcpr_admin_comment', $css_inline );
			}
			wp_enqueue_script( 'wcpr-lightbox-js', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'lightbox.js', array( 'jquery' ) );
			wp_enqueue_style( 'wcpr-lightbox-css', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'lightbox.css' );

			wp_enqueue_script( 'media-upload' );
			if ( ! did_action( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
			}
			wp_enqueue_script( 'wcpr_admin_comment_js', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'comment_screen.js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_localize_script( 'wcpr_admin_comment_js', 'wcpr_admin_comment_params', array( 'image_caption_enable' => $this->settings->get_params( 'image_caption_enable' ) ) );
		}
		if ( $screen->id == 'edit-comments' ) {
			wp_enqueue_script( 'wcpr_admin_select2_script', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'select2.js', array( 'jquery' ) );
			wp_enqueue_style( 'wcpr_admin_seletct2', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'select2.min.css' );
			wp_enqueue_style( 'wcpr_admin_edit-comments', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'edit-comments.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_enqueue_script( 'wcpr-lightbox-js', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'lightbox.js', array( 'jquery' ) );
			wp_enqueue_style( 'wcpr-lightbox-css', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'lightbox.css' );
			wp_enqueue_script( 'wcpr-edit-comments-js', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'edit-comments.js', array( 'jquery' ) );
		}
	}

	public function admin_add_review_handle() {
		if ( isset( $_POST['wcpr_admin_add_review'] ) && isset( $_POST['wcpr_admin_add_review_nonce'] ) && wp_verify_nonce( $_POST['wcpr_admin_add_review_nonce'], 'wcpr_admin_add_review_nonce_action' ) ) {
			$data     = array(
				'product_id'   => isset( $_POST['vi_wcpr_add_review_product_id'] ) ? $_POST['vi_wcpr_add_review_product_id'] : array(),
				'author_name'  => isset( $_POST['vi_wcpr_add_review_author_name'] ) ? $_POST['vi_wcpr_add_review_author_name'] : '',
				'author_email' => isset( $_POST['vi_wcpr_add_review_author_email'] ) ? $_POST['vi_wcpr_add_review_author_email'] : '',
				'review_title' => isset( $_POST['vi_wcpr_add_review_title'] ) ? $_POST['vi_wcpr_add_review_title'] : '',
				'content'      => isset( $_POST['vi_wcpr_add_review_content'] ) ? $_POST['vi_wcpr_add_review_content'] : '',
				'rating'       => isset( $_POST['vi_wcpr_add_review_rating'] ) ? $_POST['vi_wcpr_add_review_rating'] : '',
				'images'       => isset( $_POST['vi_wcpr_add_review_images'] ) ? $_POST['vi_wcpr_add_review_images'] : array(),
				'verified'     => isset( $_POST['vi_wcpr_add_review_verified'] ) ? $_POST['vi_wcpr_add_review_verified'] : '',
				'date'         => isset( $_POST['vi_wcpr_add_review_date'] ) ? $_POST['vi_wcpr_add_review_date'] : current_time( 'timestamp' ),
			);
			$validate = true;
			if ( ! is_array( $data['product_id'] ) || ! count( $data['product_id'] ) ) {
				add_action( 'admin_notices', function () {
					?>
                    <div class="error">
                        <p><?php esc_html_e( 'Please select a product!', 'woocommerce-photo-reviews' ) ?></p>
                    </div>
					<?php
				} );
				$validate = false;
			}
			if ( ! $data['author_name'] ) {
				add_action( 'admin_notices', function () {
					?>
                    <div class="error">
                        <p><?php esc_html_e( 'Please enter Author name!', 'woocommerce-photo-reviews' ) ?></p>
                    </div>
					<?php
				} );

				$validate = false;
			}
			if ( ! $data['content'] && !$this->settings->get_params('allow_empty_comment') ) {
				add_action( 'admin_notices', function () {
					?>
                    <div class="error">
                        <p><?php esc_html_e( 'Please write your review!', 'woocommerce-photo-reviews' ) ?></p>
                    </div>
					<?php
				} );

				$validate = false;
			}
			if ( ! $data['rating'] ) {
				add_action( 'admin_notices', function () {
					?>
                    <div class="error">
                        <p><?php esc_html_e( 'Please select a rating!', 'woocommerce-photo-reviews' ) ?></p>
                    </div>
					<?php
				} );

				$validate = false;
			}
			if ( ! $validate ) {
				return;
			}
			foreach ( $data['product_id'] as $product_id ) {
				$comment_id = wp_insert_comment( array(
					'comment_post_ID'      => $product_id,
					'comment_author'       => $data['author_name'],
					'comment_author_email' => $data['author_email'],
					'comment_author_url'   => '',
					'comment_content'      => $data['content'],
					'comment_type'         => 'review',
					'comment_parent'       => 0,
					'user_id'              => '',
					'comment_author_IP'    => '',
					'comment_agent'        => '',
					'comment_date'         => date( 'Y-m-d H:j:s', strtotime( $data['date'] ) ),
					'comment_date_gmt'     => date( 'Y-m-d H:j:s', strtotime( $data['date'] ) ),
					'comment_approved'     => 1,
				) );
				if ( $comment_id ) {
					$this->new_review_id[ $product_id ] = $comment_id;
					if ( $data['rating'] ) {
						update_comment_meta( $comment_id, 'rating', $data['rating'] );
					}
					if ( $data['review_title'] ) {
						update_comment_meta( $comment_id, 'wcpr_review_title', $data['review_title'] );
					}
					if ( $data['verified'] ) {
						update_comment_meta( $comment_id, 'verified', '1' );
					}
					if ( is_array( $data['images'] ) && count( $data['images'] ) ) {
						update_comment_meta( $comment_id, 'reviews-images', $data['images'] );
					}
					$review_count = get_post_meta( $product_id, '_wc_review_count', true );
					$rating_count = (array) get_post_meta( $product_id, '_wc_rating_count', true );
					if ( $review_count != array_sum( $rating_count ) ) {

						if ( ! isset( $rating_count[ $data['rating'] ] ) ) {
							$rating_count[ $data['rating'] ] = 1;
						} else {
							$rating_count[ $data['rating'] ] += 1;
						}
						update_post_meta( $product_id, '_wc_rating_count', $rating_count );
						$sum = 0;
						foreach ( $rating_count as $key => $value ) {
							$sum += (int) $key * (int) $value;
						}
						$ave_rating = round( $sum / $review_count, 1 );
						update_post_meta( $product_id, '_wc_average_rating', $ave_rating );
					}
				}
			}
			add_action( 'admin_notices', function () {
				if ( count( $this->new_review_id ) ) {
					foreach ( $this->new_review_id as $p_id => $r_id ) {
						?>
                        <div class="updated">
                            <p><?php esc_html_e( 'A new review had been added for ' . get_the_title( $p_id ), 'woocommerce-photo-reviews' ) ?>
                                <a
                                        target="_blank"
                                        href="<?php echo admin_url() . 'comment.php?action=editcomment&c=' . $r_id ?>"><?php esc_html_e( ' View & Edit', 'woocommerce-photo-reviews' ) ?></a>
                            </p>
                        </div>
						<?php
					}
				}

			} );
		}
	}

	public function admin_add_review() {
		$data = array(
			'product_id'   => isset( $_POST['vi_wcpr_add_review_product_id'] ) ? $_POST['vi_wcpr_add_review_product_id'] : array(),
			'author_name'  => isset( $_POST['vi_wcpr_add_review_author_name'] ) ? $_POST['vi_wcpr_add_review_author_name'] : '',
			'author_email' => isset( $_POST['vi_wcpr_add_review_author_email'] ) ? $_POST['vi_wcpr_add_review_author_email'] : '',
			'review_title' => isset( $_POST['vi_wcpr_add_review_title'] ) ? $_POST['vi_wcpr_add_review_title'] : '',
			'content'      => isset( $_POST['vi_wcpr_add_review_content'] ) ? $_POST['vi_wcpr_add_review_content'] : '',
			'rating'       => isset( $_POST['vi_wcpr_add_review_rating'] ) ? $_POST['vi_wcpr_add_review_rating'] : '',
			'images'       => isset( $_POST['vi_wcpr_add_review_images'] ) ? $_POST['vi_wcpr_add_review_images'] : array(),
			'verified'     => isset( $_POST['vi_wcpr_add_review_verified'] ) ? $_POST['vi_wcpr_add_review_verified'] : '',
			'date'         => isset( $_POST['vi_wcpr_add_review_date'] ) ? $_POST['vi_wcpr_add_review_date'] : date( 'Y-m-d\TH:i', current_time( 'timestamp' ) ),
		);
		?>
        <div class="wrap">
            <form class="vi-ui form" method="POST">
				<?php
				wp_nonce_field( 'wcpr_admin_add_review_nonce_action', 'wcpr_admin_add_review_nonce' );
				?>

                <h1><?php esc_html_e( 'Add product review', 'woocommerce-photo-reviews' ); ?></h1>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th><?php esc_html_e( 'Product(*required)', 'woocommerce-photo-reviews' ); ?></th>
                        <td>
                            <select name="vi_wcpr_add_review_product_id[]" class="wcpr-product-search"
                                    multiple="multiple">
								<?php
								if ( is_array( $data['product_id'] ) && count( $data['product_id'] ) ) {
									foreach ( $data['product_id'] as $product_id ) {
										$product = wc_get_product( $product_id );
										if ( $product ) {
											?>
                                            <option value="<?php echo $product_id ?>"
                                                    selected><?php echo $product->get_title() ?></option>
											<?php
										}
									}
								}
								?>
                            </select>
                            <div class="vi-ui warning message wcpr-warning-message-product-id">
                                <i class="close icon"></i>
								<?php esc_html_e( 'Please select product that you want to add review', 'woocommerce-photo-reviews' ) ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Author name(*required)', 'woocommerce-photo-reviews' ); ?></th>
                        <td>
                            <input type="text" name="vi_wcpr_add_review_author_name"
                                   class="vi-wcpr-add-review-customer-name" value="<?php echo $data['author_name'] ?>"
                                   placeholder="<?php esc_html_e( 'Author name', 'woocommerce-photo-reviews' ); ?>">
                            <div class="vi-ui warning message wcpr-warning-message-customer-name">
                                <i class="close icon"></i>
								<?php esc_html_e( 'Please enter author name', 'woocommerce-photo-reviews' ) ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Author email', 'woocommerce-photo-reviews' ); ?></th>
                        <td>
                            <input type="email" name="vi_wcpr_add_review_author_email"
                                   class="vi-wcpr-add-review-customer-email" value="<?php echo $data['author_email'] ?>"
                                   placeholder="<?php esc_html_e( 'Author email', 'woocommerce-photo-reviews' ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Review title', 'woocommerce-photo-reviews' ); ?></th>
                        <td>
                            <input type="text" name="vi_wcpr_add_review_title"
                                   class="vi-wcpr-add-review-title" value="<?php echo $data['review_title'] ?>"
                                   placeholder="<?php esc_html_e( 'Add review title', 'woocommerce-photo-reviews' ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Content(*required)', 'woocommerce-photo-reviews' ); ?></th>
                        <td>
							<?php
							wp_editor( wp_unslash($data['content']), 'vi_wcpr_add_review_content', array(
								'editor_height' => 300,
								'media_buttons' => false
							) )
							?>
                            <div class="vi-ui warning message wcpr-warning-message-content">
                                <i class="close icon"></i>
								<?php esc_html_e( 'Review content cannot be empty', 'woocommerce-photo-reviews' ) ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Rating(*required)', 'woocommerce-photo-reviews' ); ?></th>
                        <td>
                            <select class="vi-ui fluid dropdown" name="vi_wcpr_add_review_rating">
                                <option value="5" <?php selected( $data['rating'], '5' ) ?>><?php esc_html_e( '5 Stars', 'woocommerce-photo-reviews' ); ?></option>
                                <option value="4" <?php selected( $data['rating'], '4' ) ?>><?php esc_html_e( '4 Stars', 'woocommerce-photo-reviews' ); ?></option>
                                <option value="3" <?php selected( $data['rating'], '3' ) ?>><?php esc_html_e( '3 Stars', 'woocommerce-photo-reviews' ); ?></option>
                                <option value="2" <?php selected( $data['rating'], '2' ) ?>><?php esc_html_e( '2 Stars', 'woocommerce-photo-reviews' ); ?></option>
                                <option value="1" <?php selected( $data['rating'], '1' ) ?>><?php esc_html_e( '1 Star', 'woocommerce-photo-reviews' ); ?></option>
                            </select>
                            <div class="vi-ui warning message wcpr-warning-message-rating">
                                <i class="close icon"></i>
								<?php esc_html_e( 'Please select a rating', 'woocommerce-photo-reviews' ) ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Verified Owner', 'woocommerce-photo-reviews' ); ?></th>
                        <td>
                            <div class="vi-ui toggle checkbox">
                                <input type="checkbox" name="vi_wcpr_add_review_verified" <?php if ( $data['verified'] )
									esc_attr_e( 'checked' ) ?>><label></label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Review Date', 'woocommerce-photo-reviews' ); ?></th>
                        <td>
                            <input type="datetime-local" name="vi_wcpr_add_review_date"
                                   value="<?php echo $data['date'] ?>" placeholder="<?php echo $data['date'] ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Review Images', 'woocommerce-photo-reviews' ); ?></th>
                        <td>
                            <div id="wcpr-comment-photos">
                                <div id="wcpr-new-image"></div>
                                <a href="#"
                                   class="vi-ui button positive wcpr-upload-custom-img"><?php esc_html_e( 'Add Image', 'woocommerce-photo-reviews' ); ?></a>
                            </div>

                        </td>
                    </tr>
                    </tbody>
                </table>
                <input type="submit" name="wcpr_admin_add_review"
                       data-empty_content="<?php echo esc_attr($this->settings->get_params('allow_empty_comment') ?:'');?>"
                       value="<?php esc_html_e( 'Add review', 'woocommerce-photo-reviews' ); ?>"
                       class="vi-ui button primary">
            </form>
        </div>
		<?php
	}

	public function search_cate() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$keyword = isset($_REQUEST['keyword'])? sanitize_text_field($_REQUEST['keyword']):'';
		if ( empty( $keyword ) ) {
			die();
		}
		$categories = get_terms(
			array(
				'taxonomy' => 'product_cat',
				'orderby'  => 'name',
				'order'    => 'ASC',
				'search'   => $keyword,
				'number'   => 100
			)
		);
		$items      = array();
		if ( count( $categories ) ) {
			foreach ( $categories as $category ) {
				$item    = array(
					'id'   => $category->term_id,
					'text' => $category->name
				);
				$items[] = $item;
			}
		}
		wp_send_json( $items );
	}

	public function search_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$keyword = isset($_REQUEST['keyword'])? sanitize_text_field($_REQUEST['keyword']):'';
		if ( empty( $keyword ) ) {
			die();
		}
		$args      = array(
			'post_status'    => 'any',
			'post_type'      => 'page',
			'posts_per_page' => - 1,
			's'              => $keyword
		);
		$the_query = new WP_Query( $args );
		$items     = array();
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$items[] = array( 'id' => get_the_ID(), 'text' => get_the_title() );
			}
		}
		wp_reset_postdata();
		wp_send_json( $items );
	}

	public function select_all_products() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$arg            = array(
			'post_status'    => VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::search_product_statuses(),
			'post_type'      => 'product',
			'posts_per_page' => - 1,
		);
		$the_query      = new WP_Query( $arg );
		$found_products = array();
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$product_id       = get_the_ID();
				$product          = array( 'id' => $product_id, 'text' => get_the_title() );
				$found_products[] = $product;
			}
		}
		wp_reset_postdata();
		wp_send_json( $found_products );
	}

	public function search_parent_product() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$keyword = isset($_GET['keyword'])? sanitize_text_field($_GET['keyword']):'';
		if ( empty( $keyword ) ) {
			die();
		}
		$arg            = array(
			'post_status'    => VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::search_product_statuses(),
			'post_type'      => 'product',
			'posts_per_page' => 50,
			's'              => $keyword
		);
		$the_query      = new WP_Query( $arg );
		$found_products = array();
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$product_id = get_the_ID();
				if ( get_post_type( $product_id ) == 'variation' ) {
					continue;
				}
				$product          = array( 'id' => $product_id, 'text' => get_the_title() );
				$found_products[] = $product;
			}
		}
		wp_send_json( $found_products );
	}

	public function search_product() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$keyword = isset($_GET['keyword'])? sanitize_text_field($_GET['keyword']):'';
		if ( empty( $keyword ) ) {
			die();
		}
		$arg            = array(
			'post_status'    => VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::search_product_statuses(),
			'post_type'      => 'product',
			'posts_per_page' => 50,
			's'              => $keyword
		);
		$the_query      = new WP_Query( $arg );
		$found_products = array();
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$prd = wc_get_product( get_the_ID() );
				if ( $prd->has_child() && $prd->is_type( 'variable' ) ) {
					$product_children = $prd->get_children();
					if ( count( $product_children ) ) {
						foreach ( $product_children as $product_child ) {
							if ( woocommerce_version_check() ) {
								$product = array(
									'id'   => $product_child,
									'text' => get_the_title( $product_child )
								);
							} else {
								$child_wc  = wc_get_product( $product_child );
								$get_atts  = $child_wc->get_variation_attributes();
								$attr_name = array_values( $get_atts )[0];
								$product   = array(
									'id'   => $product_child,
									'text' => get_the_title() . ' - ' . $attr_name
								);
							}
							$found_products[] = $product;
						}
					}
				} else {
					$product          = array( 'id' => get_the_ID(), 'text' => get_the_title() );
					$found_products[] = $product;
				}
			}
		}
		wp_send_json( $found_products );
	}

	public function search_coupon() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		ob_start();
		$keyword = isset($_GET['keyword'])? sanitize_text_field($_GET['keyword']):'';
		if ( empty( $keyword ) ) {
			die();
		}
		$arg            = array(
			'post_status'    => 'publish',
			'post_type'      => 'shop_coupon',
			'posts_per_page' => 50,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'wlwl_unique_coupon',
					'compare' => 'NOT EXISTS'
				),
				array(
					'key'     => 'kt_unique_coupon',
					'compare' => 'NOT EXISTS'
				)
			),
			's'              => $keyword
		);
		$the_query      = new WP_Query( $arg );
		$found_products = array();
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$coupon = new WC_Coupon( get_the_ID() );
				if ( $coupon->get_usage_limit() > 0 && $coupon->get_usage_count() >= $coupon->get_usage_limit() ) {
					continue;
				}
				if ( $coupon->get_date_expires() && time() > $coupon->get_date_expires()->getTimestamp() ) {
					continue;
				}
				$product          = array( 'id' => get_the_ID(), 'text' => get_the_title() );
				$found_products[] = $product;
			}
		}
		wp_send_json( $found_products );
		die;
	}

//delete an image when a review is deleted
	public function delete_reviews_image( $comment_id ) {
		if ( get_comment_meta( $comment_id, 'reviews-images', true ) ) {
			$image_post_ids = get_comment_meta( $comment_id, 'reviews-images', true );
			foreach ( $image_post_ids as $image_post_id ) {
				if ( ! wc_is_valid_url( $image_post_id ) ) {
					wp_delete_file( wp_get_attachment_url( $image_post_id ) );
					wp_delete_post( $image_post_id, true );
				}
			}
		}
	}

	public function delete_attachment( $post_id ) {
		$comments = get_comments( array( 'count' => false, 'meta_key' => 'reviews-images' ) );
		foreach ( $comments as $comment ) {
			$comment_id     = $comment->comment_ID;
			$image_post_ids = get_comment_meta( $comment_id, 'reviews-images', true );
			foreach ( $image_post_ids as $key => $image_post_id ) {
				if ( $post_id == $image_post_id ) {
					unset( $image_post_ids[ $key ] );
					break;
				}
			}
			if ( ! count( get_comment_meta( $comment_id, 'reviews-images', true ) ) ) {
				delete_comment_meta( $comment_id, 'reviews-images' );
			} else {
				update_comment_meta( $comment_id, 'reviews-images', $image_post_ids );
			}
		}
	}

	public function restrict_manage_comments() {
		$product_id = isset( $_GET['wcpr_product_id'] ) ? $_GET['wcpr_product_id'] : '';
		$rating     = isset( $_GET['wcpr_product_rating'] ) ? $_GET['wcpr_product_rating'] : 'all';
		$image      = isset( $_GET['wcpr_review_images'] ) ? $_GET['wcpr_review_images'] : 'all';
		?>
        <select name="wcpr_product_id" class="wcpr-product-search">
			<?php
			if ( $product_id ) {
				$product = wc_get_product( $product_id );
				if ( $product ) {
					?>
                    <option value="<?php echo $product_id ?>" selected><?php echo $product->get_title() ?></option>
					<?php
				}
			}
			?>
        </select>
        <select name="wcpr_product_rating">
            <option value="all"><?php esc_html_e( 'All ratings', 'woocommerce-photo-reviews' ) ?></option>
            <option value="1" <?php selected( $rating, '1' ) ?>><?php esc_html_e( '1 star', 'woocommerce-photo-reviews' ) ?></option>
            <option value="2" <?php selected( $rating, '2' ) ?>><?php esc_html_e( '2 stars', 'woocommerce-photo-reviews' ) ?></option>
            <option value="3" <?php selected( $rating, '3' ) ?>><?php esc_html_e( '3 stars', 'woocommerce-photo-reviews' ) ?></option>
            <option value="4" <?php selected( $rating, '4' ) ?>><?php esc_html_e( '4 stars', 'woocommerce-photo-reviews' ) ?></option>
            <option value="5" <?php selected( $rating, '5' ) ?>><?php esc_html_e( '5 stars', 'woocommerce-photo-reviews' ) ?></option>
        </select>
        <select name="wcpr_review_images">
            <option value="all" <?php selected( $image, 'all' ) ?>><?php esc_html_e( 'Both with & without image', 'woocommerce-photo-reviews' ) ?></option>
            <option value="with" <?php selected( $image, 'with' ) ?>><?php esc_html_e( 'With image only', 'woocommerce-photo-reviews' ) ?></option>
            <option value="without" <?php selected( $image, 'without' ) ?>><?php esc_html_e( 'Without image only', 'woocommerce-photo-reviews' ) ?></option>
        </select>
		<?php
	}

	public function wp_list_comments_args( $vars ) {
		if ( ! is_admin() ) {
			return;
		}
		global $pagenow;
		$q_vars     = &$vars->query_vars;
		$product_id = isset( $_GET['wcpr_product_id'] ) ? $_GET['wcpr_product_id'] : '';
		$rating     = isset( $_GET['wcpr_product_rating'] ) ? $_GET['wcpr_product_rating'] : 'all';
		$image      = isset( $_GET['wcpr_review_images'] ) ? $_GET['wcpr_review_images'] : 'all';
		if ( $pagenow == 'edit-comments.php' ) {

			if ( $product_id ) {
				$q_vars['post_id'] = $product_id;
			}
			if ( $image != 'all' || $rating != 'all' ) {
				if ( $q_vars['meta_query'] ) {
					$q_vars['meta_query']['relation'] = 'AND';
					if ( $image != 'all' ) {
						$q_vars['meta_query'][] = array(
							'key'     => 'reviews-images',
							'compare' => $image == 'with' ? 'EXISTS' : 'NOT EXISTS'
						);
					}
					if ( $rating != 'all' ) {
						$q_vars['meta_query'][] = array(
							'key'     => 'rating',
							'value'   => $rating,
							'compare' => '='
						);
					}

				} else {
					$custom = array(
						'relation' => 'AND'
					);

					if ( $image != 'all' ) {
						$custom[] = array(
							'key'     => 'reviews-images',
							'compare' => $image == 'with' ? 'EXISTS' : 'NOT EXISTS'
						);
					}
					if ( $rating != 'all' ) {
						$custom[] = array(
							'key'     => 'rating',
							'value'   => $rating,
							'compare' => '='
						);
					}
					$q_vars['meta_query'] = $custom;

				}
			}

//				if($image!='all'){
//					if ( $q_vars['meta_query'] ) {
//						$custom               = array();
//						$custom['relation']   = 'AND';
//						$custom[]             = array(
//							'key'     => 'reviews-images',
//							'compare' => $image=='with'?'EXISTS':'NOT EXISTS'
//						);
//						$q_vars['meta_query'] = + $custom;
//					} else {
//						$q_vars['meta_query'] = array(
//							'relation' => 'AND',
//							array(
//								'key'     => 'reviews-images',
//								'compare' => $image=='with'?'EXISTS':'NOT EXISTS'
//							)
//						);
//					}
//				}
		}
	}

	public function bulk_admin_notices() {
		global $post_type, $pagenow;
		// Bail out if not on shop order list page.
		if ( 'edit.php' === $pagenow && 'shop_order' === $post_type ) {
			// Check if any status changes happened.
			if ( isset( $_REQUEST['send_reminder'] ) || isset( $_REQUEST['cancel_reminder'] ) ) {  // WPCS: input var ok.

				$number = isset( $_REQUEST['changed'] ) ? absint( $_REQUEST['changed'] ) : 0; // WPCS: input var ok.
				/* translators: %s: orders count */
				$message = sprintf( _n( '%d review reminder ' . ( isset( $_REQUEST['send_reminder'] ) ? 'sent.' : 'canceled.' ), '%d review reminders ' . ( isset( $_REQUEST['send_reminder'] ) ? 'sent.' : 'canceled.' ), $number, 'woocommerce-photo-reviews' ), number_format_i18n( $number ) );
				echo '<div class="updated"><p>' . esc_html( $message ) . '</p></div>';
			}
		} elseif ( 'edit-comments.php' === $pagenow ) {
			if ( ! empty( $_REQUEST['wcpr_verified'] ) ) {
				$reviews_count = intval( $_REQUEST['wcpr_verified'] );
				printf( '<div id="message" class="updated fade">' .
				        _n( 'Set %s review as verified.',
					        'Set %s reviews as verified.',
					        $reviews_count,
					        'woocommerce-photo-reviews'
				        ) . '</div>', $reviews_count );
			} elseif ( ! empty( $_REQUEST['wcpr_phrases_filter'] ) ) {
				$reviews_count = intval( $_REQUEST['wcpr_phrases_filter'] );
				printf( '<div id="message" class="updated fade">' .
				        _n( 'Applied Phrases filter to %s review',
					        'Applied Phrases filter to %s reviews',
					        $reviews_count,
					        'woocommerce-photo-reviews'
				        ) . '</div>', $reviews_count );
			}
		}
	}

	public function print_default_country_flag() {
		if ( count( $this->languages ) ) {
			?>
            <p>
                <label><?php
					if ( isset( $this->languages_data[ $this->default_language ]['country_flag_url'] ) && $this->languages_data[ $this->default_language ]['country_flag_url'] ) {
						?>
                        <img src="<?php echo esc_url( $this->languages_data[ $this->default_language ]['country_flag_url'] ); ?>">
						<?php
					}
					echo $this->default_language;
					if ( isset( $this->languages_data[ $this->default_language ]['translated_name'] ) ) {
						echo '(' . $this->languages_data[ $this->default_language ]['translated_name'] . '):';
					}
					?></label>
            </p>
			<?php
		}
	}

	public function print_other_country_flag( $param, $lang ) {
		?>
        <p>
            <label for="<?php echo "{$param}_{$lang}"; ?>"><?php
				if ( isset( $this->languages_data[ $lang ]['country_flag_url'] ) && $this->languages_data[ $lang ]['country_flag_url'] ) {
					?>
                    <img src="<?php echo esc_url( $this->languages_data[ $lang ]['country_flag_url'] ); ?>">
					<?php
				}
				echo $lang;
				if ( isset( $this->languages_data[ $lang ]['translated_name'] ) ) {
					echo '(' . $this->languages_data[ $lang ]['translated_name'] . ')';
				}
				?>:</label>
        </p>
		<?php
	}
}