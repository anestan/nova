<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend {
	protected static $settings, $language;
	public static $is_ajax, $rating, $verified, $image;
	protected $is_mobile, $frontend_style;
	protected $characters_array;
	protected $anchor_link;
	protected $enctype_start;
	public static $product_id;

	public function __construct() {
		$this->enctype_start = false;
		self::$settings      = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
		if ( self::$settings->get_params( 'enable' ) !== 'on' ) {
			return;
		}
		$photo_enable = self::$settings->get_params( 'photo', 'enable' );
		add_action( 'edit_comment', array( $this, 'coupon_for_not_logged_in' ), 10, 1 );
		add_action( 'wp_set_comment_status', array( $this, 'coupon_for_not_logged_in' ), 10, 1 );
		/*Keep this hook wpr_schedule_email for emails scheduled previously with free version*/
		add_action( 'wpr_schedule_email', array( $this, 'send_schedule_email' ), 10, 7 );
		add_action( 'wcpr_schedule_email', array( $this, 'send_schedule_email_new' ), 10, 1 );

		add_action( 'comment_form_before', array( $this, 'thank_you_message_after_review' ) );
		add_action( 'comment_form_top', array( $this, 'add_form_description' ), 20 );
		if ( ( 'off' === $photo_enable || 'off' === self::$settings->get_params( 'coupon', 'require' )['photo'] ) && 'yes' === get_option( 'woocommerce_enable_coupons' ) && 'on' === self::$settings->get_params( 'coupon', 'enable' ) ) {
			add_action( 'comment_post', array( $this, 'send_coupon_after_reviews' ), 10, 3 );
		}
		//email reminder
		if ( 'on' === self::$settings->get_params( 'followup_email', 'enable' ) ) {
			$order_statuses = self::$settings->get_params( 'followup_email', 'order_statuses' );
			if ( count( $order_statuses ) ) {
				foreach ( $order_statuses as $status ) {
					$status = substr( $status, 3 );
					add_action( "woocommerce_order_status_{$status}", array( $this, 'follow_up_email' ), 10, 2 );
				}
			}
		}
		//mobile detect
		global $wcpr_detect;
		$this->is_mobile = $wcpr_detect->isMobile() && ! $wcpr_detect->isTablet();
		if ( $this->is_mobile && self::$settings->get_params( 'mobile' ) !== 'on' ) {
			return;
		}
		$this->anchor_link = '#' . self::$settings->get_params( 'reviews_anchor_link' );
		$display_mobile    = self::$settings->get_params( 'photo', 'display_mobile' );
		if ( ! $this->is_mobile || ! $display_mobile ) {
			$this->frontend_style = self::$settings->get_params( 'photo', 'display' );
		} else {
			$this->frontend_style = $display_mobile;
		}
		// sort review
		add_filter( 'comments_template_query_args', array( $this, 'sort_reviews' ) );
		if ( 'on' == self::$settings->get_params( 'photo', 'filter' )['enable'] ) {
			add_action( 'parse_comment_query', array( __CLASS__, 'filter_images_and_verified' ) );
			add_action( 'parse_comment_query', array( __CLASS__, 'filter_review_rating' ) );
		}
		add_action( 'viwcpr_get_overall_rating_html', array( $this, 'viwcpr_get_overall_rating_html' ), 10, 1 );
		add_action( 'viwcpr_get_filters_html', array( $this, 'viwcpr_get_filters_html' ), 10, 1 );
		add_action( 'viwcpr_get_pagination_loadmore_html', array(
			$this,
			'viwcpr_get_pagination_loadmore_html'
		), 10, 1 );
		add_action( 'viwcpr_get_template_masonry_html', array( $this, 'viwcpr_get_template_masonry_html' ), 10, 1 );
		add_action( 'viwcpr_get_template_basic_html', array( $this, 'viwcpr_get_template_basic_html' ), 10, 1 );
		add_action( 'wp_ajax_wcpr_ajax_load_more_reviews', array( $this, 'ajax_load_more_reviews' ) );
		add_action( 'wp_ajax_nopriv_wcpr_ajax_load_more_reviews', array( $this, 'ajax_load_more_reviews' ) );
		/*helpful button handle*/
		add_action( 'wp_ajax_wcpr_helpful_button_handle', array( $this, 'helpful_button_handle' ) );
		add_action( 'wp_ajax_nopriv_wcpr_helpful_button_handle', array( $this, 'helpful_button_handle' ) );
		if ( 'on' == self::$settings->get_params( 'photo', 'hide_name' ) ) {
			add_filter( 'comment_author', array( $this, 'comment_author' ), PHP_INT_MAX, 2 );
		}
		add_filter( 'get_comment_link', array( $this, 'get_comment_link' ), 99, 4 );
		//form review
		add_filter( 'woocommerce_product_review_comment_form_args', array(
			$this,
			'add_comment_field'
		), PHP_INT_MAX, 1 );
		add_filter( 'woocommerce_photo_reviews_custom_fields_input', array(
			$this,
			'custom_fields_from_product_variations'
		), 10, 2 );
		if ( 'on' == $photo_enable ) {
			//add enctype attribute to form
			add_action( 'comment_form_before', array( $this, 'add_form_enctype_start' ) );
			add_action( 'comment_form_top', array( $this, 'add_form_enctype_end' ) );
			add_action( 'comment_form_must_log_in_after', array( $this, 'comment_form_must_log_in_after' ) );
		}
		//save review
		/*handle review reminder token*/
		add_action( 'init', array( $this, 'login' ) );
		add_filter( 'allow_empty_comment', array( $this, 'allow_empty_comment' ) );
		add_action( 'comment_post', array( $this, 'save_review_title' ) );
		add_action( 'comment_post', array( $this, 'save_custom_fields' ) );
		add_action( 'comment_post', array( $this, 'fix_get_comment_link' ) );
		add_filter( 'comment_post_redirect', array( $this, 'comment_post_redirect' ), 99, 2 );
		add_filter( 'woocommerce_photo_reviews_image_file_name', array( $this, 'add_prefix_to_photo_name' ), 10, 4 );
		//input#2-handle image field
		add_filter( 'preprocess_comment', array( $this, 'check_review_image' ), 10, 1 );
	}

	public function review_reminder_from_address( $from_address ) {
		$email = self::$settings->get_params( 'followup_email', 'from_address' );
		if ( is_email( $email ) ) {
			$from_address = sanitize_email( $email );
		}

		return $from_address;
	}

	public function send_schedule_email( $user_email, $customer_name, $products, $order_id, $time, $date_create, $date_complete ) {
		global $wcpr_products_to_review;
		if ( count( $products ) ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				return;
			}
			$order_meta = get_post_meta( $order_id, '_wcpr_review_reminder', true );
			$token      = isset( $order_meta['token'] ) ? $order_meta['token'] : '';
			$language   = '';
			if ( self::$settings->get_params( 'multi_language' ) ) {
				$language = get_post_meta( $order_id, 'wpml_language', true );
				if ( ! $language && isset( $order_meta['language'] ) && $order_meta['language'] ) {
					$language = $order_meta['language'];
				}
			}
			$email_template = self::$settings->get_params( 'reminder_email_template', '', $language );
			$use_template   = false;
			if ( $email_template && VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::is_email_template_customizer_active() ) {
				$email_template_obj = get_post( $email_template );
				if ( $email_template_obj && $email_template_obj->post_type === 'viwec_template' ) {
					foreach ( $products as $p ) {
						if ( $language && is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
							$p = icl_object_id( $p, 'product', true, $language );
						}
						$product = wc_get_product( $p );
						if ( $product ) {
							$product_image = wp_get_attachment_thumb_url( $product->get_image_id() );
							$product_url   = $product->get_permalink() . $this->anchor_link;
							if ( $token ) {
								$product_url = add_query_arg( 'wcpr_token', $token, $product_url );
							}
							$product_title             = $product->get_title();
							$product_price             = $product->get_price_html();
							$wcpr_products_to_review[] = array(
								'image'      => $product_image,
								'name'       => $product_title,
								'price'      => $product_price,
								'review_url' => $product_url,
							);
						}
					}
					$use_template = true;
					$viwec_email  = new VIWEC_Render_Email_Template( array(
						'template_id' => $email_template,
						'order'       => $order
					) );
					ob_start();
					$viwec_email->get_content();
					$content = ob_get_clean();
					$subject = $viwec_email->get_subject();
					$content = str_replace( array(
						'{wcpr_customer_name}',
						'{wcpr_order_id}',
						'{wcpr_order_date_create}',
						'{wcpr_order_date_complete}'
					), array( $customer_name, $order_id, $date_create, $date_complete ), $content );
					$content = str_replace( array(
						'{site_title}',
						'{wcpr_site_title}'
					), get_bloginfo( 'name' ), $content );
					$subject = str_replace( array(
						'{wcpr_customer_name}',
						'{wcpr_order_id}',
						'{wcpr_order_date_create}',
						'{wcpr_order_date_complete}'
					), array( $customer_name, $order_id, $date_create, $date_complete ), $subject );
					$subject = str_replace( array(
						'{site_title}',
						'{wcpr_site_title}'
					), get_bloginfo( 'name' ), $subject );
				}
			}
			$mailer  = WC()->mailer();
			$email   = new WC_Email();
			$headers = "Content-Type: text/html\r\nReply-to: {$email->get_from_name()} <{$email->get_from_address()}>\r\n";
			add_filter( 'woocommerce_email_from_address', array( $this, 'review_reminder_from_address' ) );
			if ( ! $use_template ) {
				$product_image_width = self::$settings->get_params( 'followup_email', 'product_image_width' );
				$content             = nl2br( stripslashes( self::$settings->get_params( 'followup_email', 'content', $language ) ) );
				$content             = str_replace( '{customer_name}', $customer_name, $content );
				$content             = str_replace( '{order_id}', $order_id, $content );
				$content             = str_replace( '{date_create}', $date_create, $content );
				$content             = str_replace( '{date_complete}', $date_complete, $content );
				$content             = str_replace( '{site_title}', get_bloginfo( 'name' ), $content );
				$content             .= '<table style="width: 100%;">';
				foreach ( $products as $p ) {
					if ( $language && is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
						$p = icl_object_id( $p, 'product', true, $language );
					}
					$product = wc_get_product( $p );
					if ( $product ) {
						$product_image = wp_get_attachment_thumb_url( $product->get_image_id() );
						$product_url   = $product->get_permalink() . $this->anchor_link;
						if ( $token ) {
							$product_url = add_query_arg( 'wcpr_token', $token, $product_url );
						}
						$product_title = $product->get_title();
						$product_price = $product->get_price_html();
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
                                           background-color:<?php echo self::$settings->get_params( 'followup_email', 'review_button_bg_color' ); ?>;
                                           color:<?php echo self::$settings->get_params( 'followup_email', 'review_button_color' ) ?>;"
                                   href="<?php echo $product_url ?>"><?php echo self::$settings->get_params( 'followup_email', 'review_button', $language ) ?>
                                </a>
                            </td>
                        </tr>
						<?php
						$content .= ob_get_clean();
					}
				}

				$content       .= '</table>';
				$subject       = stripslashes( self::$settings->get_params( 'followup_email', 'subject', $language ) );
				$email_heading = self::$settings->get_params( 'followup_email', 'heading', $language );
				$content       = $email->style_inline( $mailer->wrap_message( $email_heading, $content ) );
			}
			$email->send( $user_email, $subject, $content, $headers, array() );
			update_post_meta( $order_id, '_wcpr_review_reminder', array(
				'status'   => 'sent',
				'time'     => $time,
				'token'    => $token,
				'language' => $language
			) );
			remove_filter( 'woocommerce_email_from_address', array( $this, 'review_reminder_from_address' ) );
		}
	}

	/**
	 * @param $user_email
	 * @param $customer_name
	 * @param $products
	 * @param $order WC_Order
	 * @param $date_create
	 * @param $date_complete
	 * @param $language
	 * @param string $token
	 * @param string $user_id
	 */
	public function send_schedule_email1( $user_email, $customer_name, $products, $order, $date_create, $date_complete, $language, $token = '', $user_id = '' ) {
		global $wcpr_products_to_review;
		$wcpr_products_to_review = array();
		$review_form_page        = self::$settings->get_params( 'followup_email', 'review_form_page' );
		$product_image_width     = self::$settings->get_params( 'followup_email', 'product_image_width' );
		$review_form_page_url    = '';
		if ( $review_form_page ) {
			$review_form_page_url = get_permalink( $review_form_page );
		}
		$products_html = '';
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
				$product_price             = $product->get_price_html();
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
                            <a target="_blank" href="<?php echo $product_url ?>"><?php echo $product_title ?></a>
                        </p>
                        <p><?php echo $product_price ?></p>
                        <a target="_blank"
                           style="text-align: center;padding: 10px;text-decoration: none;font-weight: 800;
                                   background-color:<?php echo self::$settings->get_params( 'followup_email', 'review_button_bg_color' ); ?>;
                                   color:<?php echo self::$settings->get_params( 'followup_email', 'review_button_color' ) ?>;"
                           href="<?php echo $product_url ?>"><?php echo self::$settings->get_params( 'followup_email', 'review_button', $language ) ?>
                        </a>
                    </td>
                </tr>
				<?php
				$products_html .= ob_get_clean();
			}
		}
		if ( $products_html ) {
			$order_id       = $order->get_id();
			$email_template = self::$settings->get_params( 'reminder_email_template', '', $language );
			$use_template   = false;
			if ( $email_template && VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::is_email_template_customizer_active() ) {
				$email_template_obj = get_post( $email_template );
				if ( $email_template_obj && $email_template_obj->post_type === 'viwec_template' ) {
					$use_template = true;
					$viwec_email  = new VIWEC_Render_Email_Template( array(
						'template_id' => $email_template,
						'order'       => $order
					) );
					ob_start();
					$viwec_email->get_content();
					$content = ob_get_clean();
					$subject = $viwec_email->get_subject();
					$content = str_replace( array(
						'{wcpr_customer_name}',
						'{wcpr_order_id}',
						'{wcpr_order_date_create}',
						'{wcpr_order_date_complete}'
					), array( $customer_name, $order_id, $date_create, $date_complete ), $content );
					$content = str_replace( array(
						'{site_title}',
						'{wcpr_site_title}'
					), get_bloginfo( 'name' ), $content );
					$subject = str_replace( array(
						'{wcpr_customer_name}',
						'{wcpr_order_id}',
						'{wcpr_order_date_create}',
						'{wcpr_order_date_complete}'
					), array( $customer_name, $order_id, $date_create, $date_complete ), $subject );
					$subject = str_replace( array(
						'{site_title}',
						'{wcpr_site_title}'
					), get_bloginfo( 'name' ), $subject );
				}
			}
			$mailer = WC()->mailer();
			$email  = new WC_Email();
			if ( ! $use_template ) {
				$content = nl2br( stripslashes( self::$settings->get_params( 'followup_email', 'content', $language ) ) );
				$content = str_replace( '{customer_name}', $customer_name, $content );
				$content = str_replace( '{order_id}', $order_id, $content );
				$content = str_replace( '{date_create}', $date_create, $content );
				$content = str_replace( '{date_complete}', $date_complete, $content );
				$content = str_replace( '{site_title}', get_bloginfo( 'name' ), $content );
				$content .= '<table style="width: 100%;">' . $products_html . '</table>';
				add_filter( 'woocommerce_email_from_address', array( $this, 'review_reminder_from_address' ) );
				$subject       = stripslashes( self::$settings->get_params( 'followup_email', 'subject', $language ) );
				$email_heading = self::$settings->get_params( 'followup_email', 'heading', $language );
				$content       = $email->style_inline( $mailer->wrap_message( $email_heading, $content ) );
			}
			$headers = "Content-Type: text/html\r\nReply-to: {$email->get_from_name()} <{$email->get_from_address()}>\r\n";
			$email->send( $user_email, $subject, $content, $headers, array() );

			$review_reminder = get_post_meta( $order_id, '_wcpr_review_reminder', true );
			$time            = isset( $review_reminder['time'] ) ? $review_reminder['time'] : '';
			update_post_meta( $order_id, '_wcpr_review_reminder', array(
				'status' => 'sent',
				'time'   => $time,
			) );
			remove_filter( 'woocommerce_email_from_address', array( $this, 'review_reminder_from_address' ) );
		}
	}

	public function send_schedule_email_new( $order_id ) {
		$language = '';
		if ( self::$settings->get_params( 'multi_language' ) ) {
			$language = get_post_meta( $order_id, 'wpml_language', true );
			if ( ! $language && function_exists( 'pll_get_post_language' ) ) {
				$language = pll_get_post_language( $order_id );
			}
		}
		$order = wc_get_order( $order_id );
		$sent  = false;
		if ( $order ) {
			$date_format = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_date_format();
			$date_create = $order->get_date_created();
			if ( $date_create ) {
				$date_create = $date_create->date_i18n( $date_format );
			}
			$date_complete = $order->get_date_completed();
			if ( $date_complete ) {
				$date_complete = $date_complete->date_i18n( $date_format );
			}
			$items                = $order->get_items();
			$products             = array();
			$products_restriction = self::$settings->get_params( 'followup_email', 'products_restriction' );
			$excluded_categories  = self::$settings->get_params( 'followup_email', 'excluded_categories' );
			if ( 'on' == self::$settings->get_params( 'followup_email', 'exclude_non_coupon_products' ) ) {
				$excluded_categories  = array_merge( self::$settings->get_params( 'coupon', 'excluded_categories_gene' ), $excluded_categories );
				$products_restriction = array_merge( self::$settings->get_params( 'coupon', 'excluded_products_gene' ), $products_restriction );
				foreach ( $items as $item ) {
					$product_id = $item->get_product_id();
					if ( in_array( $product_id, $products_restriction ) ) {
						continue;
					}
					if ( count( self::$settings->get_params( 'coupon', 'products_gene' ) ) && ! in_array( $product_id, self::$settings->get_params( 'coupon', 'products_gene' ) ) ) {
						continue;
					}
					$product = wc_get_product( $product_id );
					if ( $product ) {
						$categories = $product->get_category_ids();
						if ( count( self::$settings->get_params( 'coupon', 'categories_gene' ) ) && ! count( array_intersect( $categories, self::$settings->get_params( 'coupon', 'categories_gene' ) ) ) ) {
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
			$products = array_unique( $products );
			if ( count( $products ) ) {
				$user_email    = $order->get_billing_email();
				$customer_name = $order->get_billing_first_name();

				$user_id = $order->get_user_id();
				if ( ! $user_id ) {
					$user = get_user_by( 'email', $user_email );
					if ( $user ) {
						$user_id = $user->ID;
					}
				}

				if ( $user_id ) {
					$user_reviewed_products = get_user_meta( $user_id, 'wcpr_user_reviewed_product', false );
					$auto_login             = self::$settings->get_params( 'followup_email', 'auto_login' );
					$auto_login_exclude     = self::$settings->get_params( 'followup_email', 'auto_login_exclude' );
					$token                  = '';
					if ( ! count( $user_reviewed_products ) ) {
						/*this user did not review any products*/
						if ( count( $products ) ) {
							if ( $auto_login ) {
								$user = new WP_User( $user_id );
								if ( ! count( array_intersect( $auto_login_exclude, $user->roles ) ) ) {
									$token = uniqid( md5( $order_id ) );
									set_transient( $token, $user_id, 2592000 );
								}
							}
							$sent = true;
							$this->send_schedule_email1( $user_email, $customer_name, $products, $order, $date_create, $date_complete, $language, $token, $user_id );
						}
					} else {
						/*only send review reminder if there are products in the order that this user has not reviewed*/
						$not_reviewed_products = array_diff( $products, $user_reviewed_products );
						if ( count( $not_reviewed_products ) ) {
							if ( $auto_login ) {
								$user = new WP_User( $user_id );
								if ( ! count( array_intersect( $auto_login_exclude, $user->roles ) ) ) {
									$token = uniqid( md5( $order_id ) );
									set_transient( $token, $user_id, 2592000 );
								}
							}
							$sent = true;
							$this->send_schedule_email1( $user_email, $customer_name, $not_reviewed_products, $order, $date_create, $date_complete, $language, $token, $user_id );
						}
					}
				} else {
					$sents = array();
					foreach ( $products as $p ) {
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
						$comments = self::get_comments( $args );
						if ( ! count( $comments ) ) {
							$sents[] = $p;
						}
					}
					if ( count( $sents ) ) {
						$sent = true;
						$this->send_schedule_email1( $user_email, $customer_name, $sents, $order, $date_create, $date_complete, $language, '', $user_id );
					}
				}
			}
		}
		if ( ! $sent ) {
			delete_post_meta( $order_id, '_wcpr_review_reminder' );
		}
	}

	public function schedule( $order_id ) {
		$next_schedule = wp_next_scheduled( 'wcpr_schedule_email', array( $order_id ) );
		if ( $next_schedule ) {
			wp_unschedule_event( $next_schedule, 'wcpr_schedule_email', array( $order_id ) );
		}
		$t_amount = self::$settings->get_params( 'followup_email', 'amount' );
		$t_unit   = self::$settings->get_params( 'followup_email', 'unit' );
		switch ( $t_unit ) {
			case 's':
				$t = $t_amount;
				break;
			case 'm':
				$t = $t_amount * 60;
				break;
			case 'h':
				$t = $t_amount * 3600;
				break;
			case 'd':
				$t = $t_amount * 86400;
				break;
			default:
				$t = 0;
		}
		$time     = time() + $t;
		$schedule = wp_schedule_single_event( $time, 'wcpr_schedule_email', array( $order_id ) );
		if ( $schedule !== false ) {
			update_post_meta( $order_id, '_wcpr_review_reminder', array(
				'status' => 'pending',
				'time'   => $time,
			) );
		}
	}

	/**
	 * @param $order_id
	 * @param $order WC_Order
	 */
	public function follow_up_email( $order_id, $order ) {
		$user_email = $order->get_billing_email();
		if ( in_array( $user_email, self::$settings->get_params( 'followup_email', 'exclude_addresses' ) ) ) {
			return;
		}
		$items                = $order->get_items();
		$products             = array();
		$products_restriction = self::$settings->get_params( 'followup_email', 'products_restriction' );
		$excluded_categories  = self::$settings->get_params( 'followup_email', 'excluded_categories' );
		if ( 'on' == self::$settings->get_params( 'followup_email', 'exclude_non_coupon_products' ) ) {
			$excluded_categories  = array_merge( self::$settings->get_params( 'coupon', 'excluded_categories_gene' ), $excluded_categories );
			$products_restriction = array_merge( self::$settings->get_params( 'coupon', 'excluded_products_gene' ), $products_restriction );
			foreach ( $items as $item ) {
				$product_id = $item->get_product_id();
				if ( in_array( $product_id, $products_restriction ) ) {
					continue;
				}
				if ( count( self::$settings->get_params( 'coupon', 'products_gene' ) ) && ! in_array( $product_id, self::$settings->get_params( 'coupon', 'products_gene' ) ) ) {
					continue;
				}
				$product = wc_get_product( $product_id );
				if ( $product ) {
					$categories = $product->get_category_ids();
					if ( count( self::$settings->get_params( 'coupon', 'categories_gene' ) ) && ! count( array_intersect( $categories, self::$settings->get_params( 'coupon', 'categories_gene' ) ) ) ) {
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
		$products = array_unique( $products );
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
				if ( ! count( $user_reviewed_products ) ) {
					/*this user did not review any products*/
					$this->schedule( $order_id );
				} else {
					/*only send review reminder if there are products in the order that this user did not review*/
					$not_reviewed_products = array_diff( $products, $user_reviewed_products );
					if ( count( $not_reviewed_products ) ) {
						$this->schedule( $order_id );
					}
				}
			} else {
				$sents = array();
				foreach ( $products as $p ) {
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
					$comments = self::get_comments( $args );
					if ( ! count( $comments ) ) {
						$sents[] = $p;
					}
				}
				if ( count( $sents ) ) {
					$this->schedule( $order_id );
				}
			}
		}
	}

	public function send_email( $user_email, $customer_name, $coupon_code, $date_expires, $language = '' ) {
		add_filter( 'woocommerce_email_from_address', array( $this, 'review_coupon_from_address' ) );
		$email_temp     = self::$settings->get_params( 'coupon', 'email', $language );
		$email_template = self::$settings->get_params( 'email_template', '', $language );
		$use_template   = false;
		if ( $email_template && VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::is_email_template_customizer_active() ) {
			$email_template_obj = get_post( $email_template );
			if ( $email_template_obj && $email_template_obj->post_type === 'viwec_template' ) {
				$use_template = true;
				$viwec_email  = new VIWEC_Render_Email_Template( array( 'template_id' => $email_template, ) );
				ob_start();
				$viwec_email->get_content();
				$content = ob_get_clean();
				$subject = $viwec_email->get_subject();
				$content = str_replace( array(
					'{wcpr_customer_name}',
					'{wcpr_coupon_code}',
					'{wcpr_date_expires}'
				), array(
					$customer_name,
					$coupon_code,
					empty( $date_expires ) ? esc_html__( 'never expires', 'woocommerce-photo-reviews' ) : date_i18n( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_date_format(), strtotime( $date_expires ) )
				), $content );
				$subject = str_replace( array(
					'{wcpr_customer_name}',
					'{wcpr_coupon_code}',
					'{wcpr_date_expires}'
				), array(
					$customer_name,
					$coupon_code,
					empty( $date_expires ) ? esc_html__( 'never expires', 'woocommerce-photo-reviews' ) : date_i18n( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_date_format(), strtotime( $date_expires ) )
				), $subject );
			}
		}
		$mailer  = WC()->mailer();
		$email   = new WC_Email();
		$headers = "Content-Type: text/html\r\nReply-to: {$email->get_from_name()} <{$email->get_from_address()}>\r\n";
		if ( ! $use_template ) {
			$subject       = stripslashes( $email_temp['subject'] );
			$content       = nl2br( stripslashes( $email_temp['content'] ) );
			$content       = str_replace( '{customer_name}', $customer_name, $content );
			$content       = str_replace( '{coupon_code}', '<span style="font-size: x-large;">' . strtoupper( $coupon_code ) . '</span>', $content );
			$content       = str_replace( '{date_expires}', empty( $date_expires ) ? esc_html__( 'never expires', 'woocommerce-photo-reviews' ) : date_i18n( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_date_format(), strtotime( $date_expires ) ), $content );
			$email_heading = isset( $email_temp['heading'] ) ? $email_temp['heading'] : esc_html__( 'Thank You For Your Review!', 'woocommerce-photo-reviews' );
			$content       = $email->style_inline( $mailer->wrap_message( $email_heading, $content ) );
		}
		$email->send( $user_email, $subject, $content, $headers, array() );
		remove_filter( 'woocommerce_email_from_address', array( $this, 'review_coupon_from_address' ) );
	}

	protected function rand() {
		if ( $this->characters_array === null ) {
			$this->characters_array = array_merge( range( 0, 9 ), range( 'a', 'z' ) );
		}
		$rand = rand( 0, count( $this->characters_array ) - 1 );

		return $this->characters_array[ $rand ];
	}

	protected function create_code() {
		$code = self::$settings->get_params( 'coupon', 'unique_coupon' )['coupon_code_prefix'];
		for ( $i = 0; $i < 6; $i ++ ) {
			$code .= $this->rand();
		}
		$args      = array(
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'title'          => $code
		);
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			wp_reset_postdata();
			$code = $this->create_code();
		}
		wp_reset_postdata();

		return $code;
	}

	public function generate_coupon() {
		remove_all_filters( 'woocommerce_coupon_get_date_expires' );
		if ( self::$settings->get_params( 'coupon', 'coupon_select' ) === 'kt_generate_coupon' ) {
			$coupon_generate = self::$settings->get_params( 'coupon', 'unique_coupon' );
			$code            = $this->create_code();
			$coupon          = new WC_Coupon( $code );
			$today           = strtotime( date( 'Ymd' ) );
			$date_expires    = ( $coupon_generate['expiry_date'] ) ? ( ( $coupon_generate['expiry_date'] + 1 ) * 86400 + $today ) : '';
			$coupon->set_amount( $coupon_generate['coupon_amount'] );
			$coupon->set_date_expires( $date_expires );
			$coupon->set_discount_type( $coupon_generate['discount_type'] );
			$coupon->set_individual_use( $coupon_generate['individual_use'] == 'yes' ? 1 : 0 );
			if ( $coupon_generate['product_ids'] ) {
				$coupon->set_product_ids( $coupon_generate['product_ids'] );
			}
			if ( $coupon_generate['excluded_product_ids'] ) {
				$coupon->set_excluded_product_ids( $coupon_generate['excluded_product_ids'] );
			}
			$coupon->set_usage_limit( $coupon_generate['limit_per_coupon'] );
			$coupon->set_usage_limit_per_user( $coupon_generate['limit_per_user'] );
			$coupon->set_limit_usage_to_x_items( $coupon_generate['limit_to_x_items'] );
			$coupon->set_free_shipping( $coupon_generate['allow_free_shipping'] == 'yes' ? 1 : 0 );
			$coupon->set_product_categories( $coupon_generate['product_categories'] );
			$coupon->set_excluded_product_categories( $coupon_generate['excluded_product_categories'] );
			$coupon->set_exclude_sale_items( $coupon_generate['exclude_sale_items'] == 'yes' ? 1 : 0 );
			$coupon->set_minimum_amount( $coupon_generate['min_spend'] );
			$coupon->set_maximum_amount( $coupon_generate['max_spend'] );
			$coupon->save();
			$code = $coupon->get_code();
			/*Update date expires this way to prevent Advanced Coupons for WooCommerce Premium from override this value(set empty)*/
			update_post_meta( $coupon->get_id(), 'date_expires', $date_expires );
			update_post_meta( $coupon->get_id(), 'kt_unique_coupon', 'yes' );
		} else {
			$coupon = new WC_Coupon( self::$settings->get_params( 'coupon', 'existing_coupon' ) );
			$code   = $coupon->get_code();
			if ( $coupon->get_usage_limit() > 0 && $coupon->get_usage_count() >= $coupon->get_usage_limit() ) {
				return false;
			}
			if ( $coupon->get_date_expires() && time() > $coupon->get_date_expires()->getTimestamp() ) {
				return false;
			}
		}

		return $code;
	}

	public function coupon_for_not_logged_in( $comment_id ) {
		if ( "0" === get_comment_meta( $comment_id, 'coupon_for_reviews', true ) ) {
			$comment = get_comment( $comment_id );
			if ( $comment->comment_approved != 1 ) {
				return;
			}
			if ( get_comment_meta( $comment_id, 'coupon_email', true ) ) {
				return;
			}
			$product_id               = $comment->comment_post_ID;
			$pgc                      = self::$settings->get_params( 'coupon', 'products_gene' );
			$excluded_pgc             = self::$settings->get_params( 'coupon', 'excluded_products_gene' );
			$categories_gene          = self::$settings->get_params( 'coupon', 'categories_gene' );
			$excluded_categories_gene = self::$settings->get_params( 'coupon', 'excluded_categories_gene' );
			if ( count( $pgc ) && ! in_array( $product_id, $pgc ) ) {
				return;
			}
			if ( in_array( $product_id, $excluded_pgc ) ) {
				return;
			}
			if ( count( $categories_gene ) || count( $excluded_categories_gene ) ) {
				$product            = wc_get_product( $product_id );
				$product_categories = $product->get_category_ids();
				if ( count( $categories_gene ) ) {
					if ( ! count( array_intersect( $product_categories, $categories_gene ) ) ) {
						return;
					}
				} elseif ( count( array_intersect( $product_categories, $excluded_categories_gene ) ) ) {
					return;
				}
			}
			$coupon_require = self::$settings->get_params( 'coupon', 'require' );

			if ( intval( get_comment_meta( $comment_id, 'rating', true ) ) < intval( $coupon_require['min_rating'] ) ) {
				return;
			}
			if ( $coupon_require['owner'] == 'on' && 1 != get_comment_meta( $comment_id, 'verified', true ) ) {
				return;
			}
			if ( 'on' === self::$settings->get_params( 'photo', 'enable' ) && 'on' === $coupon_require['photo'] && ! get_comment_meta( $comment_id, 'reviews-images', true ) ) {
				return;
			}
			$language = '';
			if ( self::$settings->get_params( 'multi_language' ) ) {
				$current_language = get_comment_meta( $comment_id, 'wcpr_current_language', true );
				if ( $current_language ) {
					if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
						$default_lang = apply_filters( 'wpml_default_language', null );
						if ( $current_language !== $default_lang ) {
							$language = $current_language;
						}
					} else if ( class_exists( 'Polylang' ) ) {
						$default_lang = pll_default_language( 'slug' );
						if ( $current_language !== $default_lang ) {
							$language = $current_language;
						}
					}
				}
			}
			$user_email    = $comment->comment_author_email;
			$customer_name = $comment->comment_author;

			$user_id = $comment->user_id;
			if ( ! $user_id ) {
				$user = get_user_by( 'email', $user_email );
				if ( $user ) {
					$user_id = $user->ID;
				}
			}
			if ( $user_id ) {
				$user_coupon = get_user_meta( $user_id, 'wcpr_user_reviewed_product', false );
				if ( ! $user_coupon || ! count( $user_coupon ) ) {
					$code = $this->generate_coupon();
					if ( $code ) {
						$c  = new WC_Coupon( $code );
						$er = $c->get_email_restrictions();
						if ( self::$settings->get_params( 'set_email_restriction' ) && ! in_array( $user_email, $er ) ) {
							$er[] = $user_email;
							$c->set_email_restrictions( $er );
							$c->save();
						}
						$coupon_code  = $c->get_code();
						$date_expires = $c->get_date_expires();
						$this->send_email( $user_email, $customer_name, $coupon_code, $date_expires, $language );
						add_user_meta( $user_id, 'wcpr_user_reviewed_product', $product_id );
						update_comment_meta( $comment_id, 'coupon_email', 'sent' );
						update_comment_meta( $comment_id, 'coupon_for_reviews', 1 );
					}
				} elseif ( ! in_array( $product_id, $user_coupon ) ) {
					$code = $this->generate_coupon();
					if ( $code ) {
						$c = new WC_Coupon( $code );
						add_user_meta( $user_id, 'wcpr_user_reviewed_product', $product_id );
						$er = $c->get_email_restrictions();
						if ( self::$settings->get_params( 'set_email_restriction' ) && ! in_array( $user_email, $er ) ) {
							$er[] = $user_email;
							$c->set_email_restrictions( $er );
							$c->save();
						}
						$coupon_code  = $c->get_code();
						$date_expires = $c->get_date_expires();
						$this->send_email( $user_email, $customer_name, $coupon_code, $date_expires, $language );
						update_comment_meta( $comment_id, 'coupon_email', 'sent' );
						update_comment_meta( $comment_id, 'coupon_for_reviews', 1 );
					}
				}
			} elseif ( 'on' !== $coupon_require['register'] ) {
				$args     = array(
					'post_type'    => 'product',
					'type'         => 'review',
					'author_email' => $user_email,
					'post_id'      => $product_id,
					'meta_query'   => array(
						'relation' => 'AND',
						array(
							'key'     => 'id_import_reviews_from_ali',
							'compare' => 'NOT EXISTS'
						),
						array(
							'key'     => 'coupon_email',
							'compare' => 'EXISTS'
						),
					)
				);
				$comments = self::get_comments( $args );
				if ( ! count( $comments ) ) {
					$code = $this->generate_coupon();

					if ( $code ) {
						$c  = new WC_Coupon( $code );
						$er = $c->get_email_restrictions();
						if ( self::$settings->get_params( 'set_email_restriction' ) && ! in_array( $user_email, $er ) ) {
							$er[] = $user_email;
							$c->set_email_restrictions( $er );
							$c->save();
						}
						$coupon_code  = $c->get_code();
						$date_expires = $c->get_date_expires();
						$this->send_email( $user_email, $customer_name, $coupon_code, $date_expires, $language );
						update_comment_meta( $comment_id, 'coupon_email', 'sent' );
						update_comment_meta( $comment_id, 'coupon_for_reviews', 1 );
					}
				}
			}
		}
	}

	public function review_coupon_from_address( $from_address ) {
		if ( isset( self::$settings->get_params( 'coupon', 'email' )['from_address'] ) && is_email( self::$settings->get_params( 'coupon', 'email' )['from_address'] ) ) {
			$from_address = sanitize_email( self::$settings->get_params( 'coupon', 'email' )['from_address'] );
		}

		return $from_address;
	}

	public function send_coupon_after_reviews( $comment_id, $approve, $commentdata ) {
		$comment_type = isset( $commentdata['comment_type'] ) ? $commentdata['comment_type'] : '';
		if ( $comment_type !== 'review' ) {
			return;
		}
		$coupon_require = self::$settings->get_params( 'coupon', 'require' );
		if ( intval( get_comment_meta( $comment_id, 'rating', true ) ) < intval( $coupon_require['min_rating'] ) ) {
			return;
		}
		$comment                  = get_comment( $comment_id );
		$product_id               = $comment->comment_post_ID;
		$pgc                      = self::$settings->get_params( 'coupon', 'products_gene' );
		$excluded_pgc             = self::$settings->get_params( 'coupon', 'excluded_products_gene' );
		$categories_gene          = self::$settings->get_params( 'coupon', 'categories_gene' );
		$excluded_categories_gene = self::$settings->get_params( 'coupon', 'excluded_categories_gene' );
		if ( count( $pgc ) && ! in_array( $product_id, $pgc ) ) {
			return;
		}
		if ( in_array( $product_id, $excluded_pgc ) ) {
			return;
		}
		if ( count( $categories_gene ) || count( $excluded_categories_gene ) ) {
			$product            = wc_get_product( $product_id );
			$product_categories = $product->get_category_ids();
			if ( count( $categories_gene ) ) {
				if ( ! count( array_intersect( $product_categories, $categories_gene ) ) ) {
					return;
				}
			} elseif ( count( array_intersect( $product_categories, $excluded_categories_gene ) ) ) {
				return;
			}
		}
		$user_id       = $comment->user_id;
		$user_email    = $comment->comment_author_email;
		$customer_name = $comment->comment_author;
		$language      = self::get_language();
		if ( $coupon_require['owner'] == 'on' ) {
			$verified = false;
			if ( 'product' === get_post_type( $product_id ) ) {
				$verified = wc_customer_bought_product( $user_email, $user_id, $product_id );
			}
			if ( ! $verified ) {
				return;
			}
		}
		if ( $comment->comment_approved != 1 ) {
			update_comment_meta( $comment_id, 'coupon_for_reviews', "0" );
			if ( $language ) {
				update_comment_meta( $comment_id, 'wcpr_current_language', $language );
			}

			return;
		}
		if ( ! $user_id ) {
			$user = get_user_by( 'email', $user_email );
			if ( $user ) {
				$user_id = $user->ID;
			}
		}
		if ( $user_id ) {
			$user_coupon = get_user_meta( $user_id, 'wcpr_user_reviewed_product', false );
			if ( ! count( $user_coupon ) || ! in_array( $product_id, $user_coupon ) ) {
				$code = $this->generate_coupon();
				if ( $code ) {
					$c = new WC_Coupon( $code );
					add_user_meta( $user_id, 'wcpr_user_reviewed_product', $product_id );
					$er = $c->get_email_restrictions();
					if ( self::$settings->get_params( 'set_email_restriction' ) && ! in_array( $user_email, $er ) ) {
						$er[] = $user_email;
						$c->set_email_restrictions( $er );
						$c->save();
					}
					$coupon_code  = $c->get_code();
					$date_expires = $c->get_date_expires();
					$this->send_email( $user_email, $customer_name, $coupon_code, $date_expires, $language );
					update_comment_meta( $comment_id, 'coupon_email', 'sent' );
				}
			}
		} elseif ( 'on' != $coupon_require['register'] ) {
			$args     = array(
				'post_type'    => 'product',
				'type'         => 'review',
				'author_email' => $user_email,
				'post_id'      => $product_id,
				'meta_query'   => array(
					'relation' => 'AND',
					array(
						'key'     => 'id_import_reviews_from_ali',
						'compare' => 'NOT EXISTS'
					),
					array(
						'key'     => 'coupon_email',
						'compare' => 'EXISTS'
					),
				)
			);
			$comments = self::get_comments( $args );
			if ( ! count( $comments ) ) {
				$code = $this->generate_coupon();

				if ( $code ) {
					$c  = new WC_Coupon( $code );
					$er = $c->get_email_restrictions();
					if ( self::$settings->get_params( 'set_email_restriction' ) && ! in_array( $user_email, $er ) ) {
						$er[] = $user_email;
						$c->set_email_restrictions( $er );
						$c->save();
					}
					$coupon_code  = $c->get_code();
					$date_expires = $c->get_date_expires();
					$this->send_email( $user_email, $customer_name, $coupon_code, $date_expires, $language );
					update_comment_meta( $comment_id, 'coupon_email', 'sent' );
				}
			}
		}
	}

	/**
	 * @param $name
	 * @param $comment_id
	 * @param $post_id
	 * @param $is_sideload
	 *
	 * @return mixed
	 */
	public function add_prefix_to_photo_name( $name, $comment_id, $post_id, $is_sideload ) {
		if ( $is_sideload ) {
			$import_upload_prefix = self::$settings->get_params( 'import_upload_prefix' );
			if ( $import_upload_prefix ) {
				$import_upload_prefix = str_replace( array( '{comment_id}', '{product_id}' ), array(
					$comment_id,
					$post_id
				), $import_upload_prefix );
				$name                 = sanitize_file_name( $import_upload_prefix ) . $name;
			}
		} else {
			$user_upload_prefix = self::$settings->get_params( 'user_upload_prefix' );
			if ( $user_upload_prefix ) {
				$user_upload_prefix = str_replace( array( '{comment_id}', '{product_id}' ), array(
					$comment_id,
					$post_id
				), $user_upload_prefix );
				$name               = sanitize_file_name( $user_upload_prefix ) . $name;
			}
		}

		return $name;
	}

	/**Custom folder to save images uploaded in users' reviews
	 *
	 * @param $param
	 *
	 * @return mixed
	 */
	public static function user_upload_folder( $param ) {
		$settings           = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
		$user_upload_folder = $settings->get_params( 'user_upload_folder' );
		if ( $user_upload_folder ) {
			$subdir = '';
			if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
				// Generate the yearly and monthly dirs
				$time = current_time( 'mysql' );

				$y      = substr( $time, 0, 4 );
				$m      = substr( $time, 5, 2 );
				$subdir = "/$y/$m";
			}
			if ( ! empty( self::$product_id ) ) {
				$user_upload_folder = str_replace( '{product_id}', self::$product_id, $user_upload_folder );
			}
			$user_upload_folder = '/' . $user_upload_folder;

			$param['path'] = str_replace( $param['basedir'], $param['basedir'] . $user_upload_folder, $param['path'] );
			$param['url']  = str_replace( $param['baseurl'], $param['baseurl'] . $user_upload_folder, $param['url'] );
			if ( $subdir && ! empty( $param['subdir'] ) ) {
				$param['path'] = str_replace( $param['subdir'], $subdir, $param['path'] );
				$param['url']  = str_replace( $param['subdir'], $subdir, $param['url'] );
			}
		}

		return $param;
	}

	public static function reduce_image_sizes( $sizes ) {
		$reduce_array = apply_filters( 'woocommerce_photo_reviews_reduce_array', array(
			'thumbnail',
			'wcpr-photo-reviews',
			'medium'
		) );
		foreach ( $sizes as $k => $size ) {
			if ( in_array( $size, $reduce_array ) ) {
				continue;
			}
			unset( $sizes[ $k ] );
		}

		return $sizes;
	}

	public function comment_post_redirect( $location, $comment ) {
		if ( self::$settings->get_params( 'pagination_ajax' ) && $this->frontend_style == 2 ) {
			$product = wc_get_product( $comment->comment_post_ID );
			if ( $product ) {
				$location = $product->get_permalink() . $this->anchor_link;
			}
		}
		if ( 'sent' === get_comment_meta( $comment->comment_ID, 'coupon_email', true ) ) {
			$location = add_query_arg( array( 'wcpr_thank_you_message' => 1 ), $location );
			update_comment_meta( $comment->comment_ID, 'coupon_email', 'notified' );
		} else {
			$location = add_query_arg( array( 'wcpr_thank_you_message' => 2 ), $location );
		}

		return $location;
	}

	public function add_review_image( $comment_id ) {
		add_filter( 'intermediate_image_sizes', array(
			'VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend',
			'reduce_image_sizes'
		) );
		add_filter( 'upload_dir', array( 'VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend', 'user_upload_folder' ) );
		$post_id          = get_comment( $comment_id )->comment_post_ID;
		self::$product_id = $post_id;
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		$files    = isset( $_FILES['wcpr_image_upload'] ) ? $_FILES['wcpr_image_upload'] : array();
		$captions = isset( $_POST['wcpr_image_caption'] ) ? $_POST['wcpr_image_caption'] : array();
		$img_id   = array();
		if ( is_array( $files['name'][0] ) ) {
			foreach ( $files['name'] as $key => $value ) {
				if ( $files['name'][ $key ][0] ) {
					$meta = array();
					if ( isset( $captions[ $key ] ) && $captions[ $key ] ) {
						$meta['post_excerpt'] = $captions[ $key ];
					}
					$file                   = array(
						'name'     => apply_filters( 'woocommerce_photo_reviews_image_file_name', $files['name'][ $key ][0], $comment_id, $post_id, false ),
						'type'     => $files['type'][ $key ][0],
						'tmp_name' => $files['tmp_name'][ $key ][0],
						'error'    => $files['error'][ $key ][0],
						'size'     => $files['size'][ $key ][0]
					);
					$_FILES ['upload_file'] = $file;
					$attachment_id          = media_handle_upload( 'upload_file', $post_id, $meta );
					if ( is_wp_error( $attachment_id ) ) {
						wp_die( 'Error adding file.' );
					} else {
						$img_id[] = $attachment_id;
					}
				}
			}
		} else {
			foreach ( $files['name'] as $key => $value ) {
				if ( empty( $value ) ) {
					continue;
				}
				if ( $files['name'][ $key ] ) {
					$meta = array();
					if ( isset( $captions[ $key ] ) && $captions[ $key ] ) {
						$meta['post_excerpt'] = $captions[ $key ];
					}
					$file                   = array(
						'name'     => apply_filters( 'woocommerce_photo_reviews_image_file_name', $files['name'][ $key ], $comment_id, $post_id, false ),
						'type'     => $files['type'][ $key ],
						'tmp_name' => $files['tmp_name'][ $key ],
						'error'    => $files['error'][ $key ],
						'size'     => $files['size'][ $key ]
					);
					$_FILES ['upload_file'] = $file;
					$attachment_id          = media_handle_upload( 'upload_file', $post_id, $meta );
					if ( is_wp_error( $attachment_id ) ) {
						wp_die( 'Error adding file.' );
					} else {
						$img_id[] = $attachment_id;
					}
				}
			}
		}
		remove_filter( 'intermediate_image_sizes', array(
			'VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend',
			'reduce_image_sizes'
		) );
		remove_filter( 'upload_dir', array( 'VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend', 'user_upload_folder' ) );
		if ( count( $img_id ) ) {
			update_comment_meta( $comment_id, 'reviews-images', $img_id );
		}
		update_comment_meta( $comment_id, 'gdpr_agree', 1 );
	}

	public function check_review_image( $comment ) {
		$comment_type = isset( $comment['comment_type'] ) ? $comment['comment_type'] : '';
		if ( ! is_admin() && isset( $_POST['comment_post_ID'], $comment['comment_type'] ) && 'product' === get_post_type( absint( $_POST['comment_post_ID'] ) ) && ( '' === $comment_type || 'comment' === $comment_type ) ) {
			$comment_type = $comment['comment_type'] = 'review';
		}
		if ( $comment_type !== 'review' ) {
			return $comment;
		}
		$restrict_number_of_reviews = self::$settings->get_params( 'restrict_number_of_reviews' );
		switch ( $restrict_number_of_reviews ) {
			case 'one':
				if ( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::reviews_count_of_customer( $comment['comment_author_email'], $comment['comment_post_ID'] ) >= 1 ) {
					wp_die( 'You have reached the maximum number of reviews that a user can leave for this product' );
				}
				break;
			case 'one_verified':
				if ( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::reviews_count_of_customer( $comment['comment_author_email'], $comment['comment_post_ID'] ) >= 1 ) {
					wp_die( 'You have reached the maximum number of reviews that a user can leave for this product.' );
				}
				if ( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_orders_count_by_product( $comment['comment_post_ID'], $comment['comment_author_email'], $comment['user_ID'] ) < 1 ) {
					wp_die( 'Only customers who bought this product can leave a review.' );
				}
				break;
			case 'orders_count':
				$orders_count = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_orders_count_by_product( $comment['comment_post_ID'], $comment['comment_author_email'], $comment['user_ID'] );
				if ( $orders_count < 1 ) {
					wp_die( 'Only customers who bought this product can leave a review.' );
				}
				if ( VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::reviews_count_of_customer( $comment['comment_author_email'], $comment['comment_post_ID'] ) >= $orders_count ) {
					wp_die( 'You have reached the maximum number of reviews that you can leave for this product' );
				}
				break;
			case '':
			default:
		}

		$maxsize_allowed = self::$settings->get_params( 'photo', 'maxsize' );
		$max_file_up     = self::$settings->get_params( 'photo', 'maxfiles' );
		$im              = isset( $_FILES['wcpr_image_upload'] ) ? $_FILES['wcpr_image_upload'] : array();
		if ( ( ! isset( $_POST['wcpr_image_upload_nonce'] ) || ! isset( $_FILES['wcpr_image_upload'] ) || ! wp_verify_nonce( $_POST['wcpr_image_upload_nonce'], 'wcpr_image_upload' ) ) && 'on' === self::$settings->get_params( 'photo', 'required' ) ) {
			wp_die( 'Photo is required.' );
		}
		if ( ( 'on' == self::$settings->get_params( 'photo', 'gdpr' ) ) && ( ! isset( $_POST['wcpr_gdpr_checkbox'] ) || ! $_POST['wcpr_gdpr_checkbox'] ) ) {
			wp_die( 'Please agree with the GDPR policy!' );
		}
		if ( is_array( $im['name'][0] ) ) {
			if ( $im['name'][0][0] == '' ) {
				if ( 'on' === self::$settings->get_params( 'photo', 'required' ) ) {
					wp_die( 'Photo is required.' );
				}
			} else {
				if ( count( $im['name'] ) > $max_file_up ) {

					wp_die( "Maximum number of files allowed is: $max_file_up." );
				}
				foreach ( $im['size'] as $k => $size ) {
					if ( ! $size[0] ) {
						if ( self::$settings->get_params( 'image_caption_enable' ) ) {
							continue;
						} else {
							wp_die( "File's too large!" );
						}
					}
					if ( $size[0] > ( $maxsize_allowed * 1024 ) ) {
						wp_die( "Max size allowed: $maxsize_allowed kB." );
					}
				}
				foreach ( $im['type'] as $type_k => $type ) {
					if ( $type[0] != "image/jpg" && $type[0] != "image/jpeg" && $type[0] != "image/bmp" && $type[0] != "image/png" && $type[0] != "image/gif" ) {
						if ( self::$settings->get_params( 'image_caption_enable' ) ) {
							continue;
						} else {
							wp_die( "Only JPG, JPEG, BMP, PNG and GIF are allowed." );
						}
					}
				}
				add_action( 'comment_post', array( $this, 'add_review_image' ) );
				if ( 'on' === self::$settings->get_params( 'photo', 'enable' ) && 'on' === self::$settings->get_params( 'coupon', 'require' )['photo'] && 'yes' == get_option( 'woocommerce_enable_coupons' ) && 'on' == self::$settings->get_params( 'coupon', 'enable' ) ) {
					add_action( 'comment_post', array( $this, 'send_coupon_after_reviews' ), 10, 3 );
				}
			}
		} elseif ( is_array( $im['name'] ) ) {
			foreach ( $im['name'] as $im_name_k => $im_name_v ) {
				if ( ! $im_name_v ) {
					foreach ( $im as $im_k => $im_v ) {
						unset( $im[ $im_k ][ $im_name_k ] );
					}
				}
			}

			if ( ! count( $im['name'] ) ) {
				if ( 'on' === self::$settings->get_params( 'photo', 'required' ) ) {
					wp_die( 'Photo is required.' );
				}
			} else {
				if ( count( $im['name'] ) > $max_file_up ) {

					wp_die( "Maximum number of files allowed is: $max_file_up." );
				}
				foreach ( $im['size'] as $k => $size ) {
					if ( ! $size ) {
						if ( self::$settings->get_params( 'image_caption_enable' ) ) {
							continue;
						} else {
							wp_die( "File's too large!" );
						}
					}
					if ( $size > ( $maxsize_allowed * 1024 ) ) {
						wp_die( "Max size allowed: $maxsize_allowed kilobytes." );
					}
				}
				foreach ( $im['type'] as $type ) {

					if ( $type != "image/jpg" && $type != "image/jpeg" && $type != "image/bmp" && $type != "image/png" && $type != "image/gif" ) {
						if ( self::$settings->get_params( 'image_caption_enable' ) ) {
							continue;
						} else {
							wp_die( "Only JPG, JPEG, BMP, PNG and GIF are allowed." );
						}
					}
				}
				add_action( 'comment_post', array( $this, 'add_review_image' ) );
				if ( 'on' === self::$settings->get_params( 'photo', 'enable' ) && 'on' === self::$settings->get_params( 'coupon', 'require' )['photo'] && 'yes' == get_option( 'woocommerce_enable_coupons' ) && 'on' == self::$settings->get_params( 'coupon', 'enable' ) ) {
					add_action( 'comment_post', array( $this, 'send_coupon_after_reviews' ), 10, 3 );
				}
			}
		}


		return $comment;
	}

	public function save_custom_fields( $comment_id ) {
		if ( ! self::$settings->get_params( 'custom_fields_enable' ) ) {
			return;
		}
		$custom_fields = isset( $_POST['wcpr_custom_fields'] ) ? stripslashes_deep( $_POST['wcpr_custom_fields'] ) : array();
		if ( isset( $custom_fields['name'] ) && is_array( $custom_fields['name'] ) && count( $custom_fields['name'] ) ) {
			$custom_fields_data = array();
			foreach ( $custom_fields['name'] as $custom_field_name_k => $custom_field_name_v ) {
				if ( ! $custom_fields['value'][ $custom_field_name_k ] ) {
					continue;
				}
				$custom_fields_data[] = array(
					'name'  => $custom_field_name_v,
					'value' => $custom_fields['value'][ $custom_field_name_k ],
					'unit'  => $custom_fields['unit'][ $custom_field_name_k ],
				);
			}
			update_comment_meta( $comment_id, 'wcpr_custom_fields', $custom_fields_data );
		}
	}

	public function save_review_title( $comment_id ) {
		if ( ! self::$settings->get_params( 'review_title_enable' ) ) {
			return;
		}
		$title = isset( $_POST['wcpr_review_title'] ) ? sanitize_text_field( $_POST['wcpr_review_title'] ) : '';
		if ( $title ) {
			update_comment_meta( $comment_id, 'wcpr_review_title', $title );
		}
	}

	public function fix_get_comment_link() {
		add_filter( 'get_comment_link', array( $this, 'get_comment_link_1' ), 10, 4 );
	}

	public function allow_empty_comment( $allow ) {
		return boolval( self::$settings->get_params( 'allow_empty_comment' ) );
	}

	public function login() {
		if ( ! is_user_logged_in() ) {
			$token   = isset( $_GET['wcpr_token'] ) ? sanitize_text_field( $_GET['wcpr_token'] ) : '';
			$user_id = get_transient( $token );
			if ( $user_id ) {
				delete_transient( $token );
				wp_set_auth_cookie( $user_id );
				wp_safe_redirect( remove_query_arg( 'wcpr_token', $_SERVER['REQUEST_URI'] ) );
				exit();
			}
		} elseif ( isset( $_GET['wcpr_token'] ) ) {
			$token = sanitize_text_field( $_GET['wcpr_token'] );
			delete_transient( $token );
			wp_safe_redirect( remove_query_arg( 'wcpr_token', $_SERVER['REQUEST_URI'] ) );
			exit();
		}
	}

	public static function add_inline_style( $element, $style, $value, $suffix = array() ) {
		if ( ! $element || ! is_array( $element ) ) {
			return '';
		}
		$element = implode( ',', $element );
		$return  = $element . '{';
		if ( is_array( $style ) && count( $style ) ) {
			foreach ( $style as $k => $v ) {
				$get_value  = $value[ $k ] ?? '';
				$get_suffix = $suffix[ $k ] ?? '';
				$return     .= $v . ':' . $get_value . $get_suffix . ';';
			}
		}
		$return .= '}';

		return $return;
	}

	public static function get_comments( $args ) {
		return apply_filters( 'woocommerce_photo_reviews_get_comments', get_comments( $args ), $args );
	}

	public static function stars_count( $star, $post_id ) {
		$agrs   = array(
			'post__in'   => is_array( $post_id ) ? $post_id : array( $post_id ),
			'post_type'  => 'product',
			'count'      => true,
			'status'     => 'approve',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => 'rating',
					'value'   => $star,
					'compare' => '='
				)
			)
		);
		$return = self::get_comments( $agrs );

		return $return;
	}

	public static function get_language() {
		if ( self::$language !== null ) {
			return self::$language;
		}
		if ( ! self::$settings->get_params( 'multi_language' ) ) {
			return self::$language = '';
		}
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			$default_lang     = apply_filters( 'wpml_default_language', null );
			$current_language = apply_filters( 'wpml_current_language', null );
			if ( $current_language && $current_language !== $default_lang ) {
				self::$language = $current_language;
			}
		} else if ( class_exists( 'Polylang' ) ) {
			$default_lang     = pll_default_language( 'slug' );
			$current_language = pll_current_language( 'slug' );
			if ( $current_language && $current_language !== $default_lang ) {
				self::$language = $current_language;
			}
		}

		return self::$language ?? '';
	}

	public function get_comment_link_1( $link, $comment, $args, $cpage ) {
		global $wp_rewrite;
		$sort = self::$settings->get_params( 'photo', 'sort' )['time'];
		if ( ( $sort == 1 ) ) {
			$link  = get_permalink( $comment->comment_post_ID );
			$cpage = 1;
			if ( get_option( 'page_comments' ) ) {
				if ( $wp_rewrite->using_permalinks() ) {
					if ( $cpage ) {
						$link = trailingslashit( $link ) . $wp_rewrite->comments_pagination_base . '-' . $cpage;
					}

					$link = user_trailingslashit( $link, 'comment' );
				} elseif ( $cpage ) {
					$link = add_query_arg( 'cpage', $cpage, $link );
				}
			}

			if ( $wp_rewrite->using_permalinks() ) {
				$link = user_trailingslashit( $link, 'comment' );
			}

			$link = $link . '#comment-' . $comment->comment_ID;
		}

		return $link;
	}

	public function get_comment_link( $link, $comment, $args, $cpage ) {
		if ( self::$settings->get_params( 'pagination_ajax' ) && $this->frontend_style == 2 ) {
			$product = wc_get_product( $comment->comment_post_ID );
			if ( $product ) {
				$link = $product->get_permalink() . $this->anchor_link;
			}
		}

		return $link;
	}

	public function comment_form_must_log_in_after() {
		if ( $this->enctype_start ) {
			$this->enctype_start = false;
			echo ob_get_clean();
		}
	}

	public function add_form_enctype_end() {
		global $wcpr_review_form;
		if ( ! is_product() || $wcpr_review_form ) {
			return;
		}
		if ( $this->enctype_start ) {
			$this->enctype_start = false;
			$v                   = ob_get_clean();
			$v                   = str_replace( '<form', '<form enctype="multipart/form-data"', $v );
			print( $v );
		}
	}

	public function add_form_enctype_start() {
		global $wcpr_review_form;
		if ( ! is_product() || $wcpr_review_form ) {
			return;
		}
		$this->enctype_start = true;
		ob_start();
	}

	public function add_form_description() {
		if ( ! is_product() ) {
			return;
		}
		if ( 'on' == self::$settings->get_params( 'coupon', 'enable' ) ) {
			echo apply_filters( 'viwcpr_get_form_description_html', '<div class="wcpr-form-description">' . self::$settings->get_params( 'coupon', 'form_title', self::get_language() ) . '</div>' );
		}
	}

	public function thank_you_message_after_review() {
		if ( is_product() ) {
			$message_type = isset( $_GET['wcpr_thank_you_message'] ) ? sanitize_text_field( $_GET['wcpr_thank_you_message'] ) : '';
			if ( $message_type == 1 ) {
				echo apply_filters( 'viwcpr_thank_you_message_after_review', sprintf( '<div class="woocommerce-message"><p>%s</p></div>',
					esc_html( self::$settings->get_params( 'thank_you_message_coupon', '', self::get_language() ) ) ) );
			} elseif ( $message_type == 2 ) {
				echo apply_filters( 'viwcpr_thank_you_message_after_review', sprintf( '<div class="woocommerce-message"><p>%s</p></div>',
					esc_html( self::$settings->get_params( 'thank_you_message', '', self::get_language() ) ) ) );
			}
		}
	}

	public function custom_fields_from_product_variations( $custom_fields, $product ) {
		if ( self::$settings->get_params( 'custom_fields_from_variations' ) ) {
			if ( $product && $product->is_type( 'variable' ) ) {
				$attributes = $product->get_variation_attributes();
				if ( is_array( $attributes ) && count( $attributes ) ) {
					foreach ( $attributes as $attribute_name => $attribute_value ) {
						if ( is_array( $attribute_value ) && count( $attribute_value ) ) {
							$custom_fields[] = array(
								'name'     => wc_attribute_label( $attribute_name ),
								'language' => self::get_language(),
								'label'    => '',
								'value'    => array_map( 'wc_attribute_label', $attribute_value ),
								'unit'     => array(),
							);
						}
					}
				}
			}
		}

		return $custom_fields;
	}

	//add wp_nonce_field(for image field)
	public function add_image_upload_nonce() {
		wp_nonce_field( 'wcpr_image_upload', 'wcpr_image_upload_nonce' );
	}

	public function add_comment_field( $comment_form ) {
		$comment_field                 = wc_get_template_html(
			'viwcpr-comment-field-html.php',
			array(
				'comment_form' => $comment_form,
				'settings'     => self::$settings,
			),
			'woocommerce-photo-reviews' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
			WOOCOMMERCE_PHOTO_REVIEWS_TEMPLATES
		);
		$comment_form['comment_field'] = $comment_field ?: ( $comment_form['comment_field'] ?? '' );
		add_action( 'comment_form', array( $this, 'add_image_upload_nonce' ) );

		return $comment_form;
	}

	public function helpful_button_handle() {
		global $wpdb;
		$vote       = isset( $_POST['vote'] ) ? sanitize_text_field( $_POST['vote'] ) : '';
		$comment_id = isset( $_POST['comment_id'] ) ? sanitize_text_field( $_POST['comment_id'] ) : '';
		$response   = array(
			'status' => 'error',
			'up'     => '',
			'down'   => '',
		);
		if ( $vote && $comment_id ) {
			$user = wp_get_current_user();
			if ( $user ) {
				if ( ! empty( $user->ID ) ) {
					$vote_info = $user->ID;
				} else {
					$vote_info = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_the_user_ip();
				}
			} else {
				$vote_info = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_the_user_ip();
			}
			$up_votes   = get_comment_meta( $comment_id, 'wcpr_vote_up', false );
			$down_votes = get_comment_meta( $comment_id, 'wcpr_vote_down', false );
			if ( $vote == 'up' ) {
				if ( ! in_array( $vote_info, $up_votes ) ) {
					$response['status'] = 'success';
					if ( in_array( $vote_info, $down_votes ) ) {
						$query = $wpdb->prepare( "UPDATE {$wpdb->prefix}commentmeta set meta_key='wcpr_vote_up' where comment_id='%s' and meta_key='wcpr_vote_down' and meta_value='%s'", $comment_id, $vote_info );
						$wpdb->query( $query );
						if ( count( $down_votes ) > 0 ) {
							$response['down'] = count( $down_votes ) - 1 + absint( get_comment_meta( $comment_id, 'wcpr_vote_down_count', true ) );
						}
					} else {
						$response['down'] = count( $down_votes ) + absint( get_comment_meta( $comment_id, 'wcpr_vote_down_count', true ) );
						add_comment_meta( $comment_id, 'wcpr_vote_' . $vote, $vote_info );
					}
					$response['up'] = count( $up_votes ) + 1 + absint( get_comment_meta( $comment_id, 'wcpr_vote_up_count', true ) );
				}
			} else {
				if ( ! in_array( $vote_info, $down_votes ) ) {
					$response['status'] = 'success';
					if ( in_array( $vote_info, $up_votes ) ) {
						$query = $wpdb->prepare( "UPDATE {$wpdb->prefix}commentmeta set meta_key='wcpr_vote_down' where comment_id='%s' and meta_key='wcpr_vote_up' and meta_value='%s'", $comment_id, $vote_info );
						$wpdb->query( $query );
						if ( count( $up_votes ) > 0 ) {
							$response['up'] = count( $up_votes ) - 1 + absint( get_comment_meta( $comment_id, 'wcpr_vote_up_count', true ) );
						}
					} else {
						add_comment_meta( $comment_id, 'wcpr_vote_' . $vote, $vote_info );
						$response['up'] = count( $up_votes ) + absint( get_comment_meta( $comment_id, 'wcpr_vote_up_count', true ) );
					}
					$response['down'] = count( $down_votes ) + 1 + absint( get_comment_meta( $comment_id, 'wcpr_vote_down_count', true ) );
				}
			}
		}
		wp_send_json( $response );
	}

	public function comment_author( $author, $comment_id ) {
		global $wcpr_shortcode_count;
		if ( is_admin() && ! $wcpr_shortcode_count ) {
			return $author;
		}
		if ( ! $wcpr_shortcode_count && 'review' !== get_comment_type( $comment_id ) ) {
			return $author;
		}
		if ( $author ) {
			$str_arr    = explode( ' ', $author );
			$return_arr = array();
			foreach ( $str_arr as $key => $value ) {
				$word_length = function_exists( 'mb_strlen' ) ? mb_strlen( $value ) : strlen( $value );
				if ( $word_length == 1 ) {
					$return_arr[] = $value;
					continue;
				}
				$word = function_exists( 'mb_substr' ) ? mb_substr( $value, 0, 1 ) : substr( $value, 0, 1 );
				for ( $i = 0; $i < $word_length - 1; $i ++ ) {
					$word .= '*';
				}
				$return_arr[] = $word;
			}
			if ( count( $return_arr ) ) {
				return implode( ' ', $return_arr );
			} else {
				return $author;
			}
		} else {
			return $author;
		}
	}

	public function ajax_load_more_reviews() {
		self::$is_ajax  = true;
		$frontend_style = isset( $_POST['frontend_style'] ) ? wc_clean( $_POST['frontend_style'] ) : '';
		self::$rating   = isset( $_POST['rating'] ) ? wc_clean( $_POST['rating'] ) : '';
		self::$verified = isset( $_POST['verified'] ) ? wc_clean( $_POST['verified'] ) : '';
		self::$image    = isset( $_POST['image'] ) ? wc_clean( $_POST['image'] ) : '';
		$is_shortcode   = isset( $_POST['is_shortcode'] ) ? sanitize_text_field( $_POST['is_shortcode'] ) : '';
		global $post;
		$post = get_post( $_POST['post_id'] );
		setup_postdata( $post );
		$response = array(
			'html'           => '',
			'cpage'          => '',
			'load_more_html' => '',
			'update_count'   => array()
		);
		if ( class_exists( 'WPSEO_Options' ) ) {
			//Compatibility with Yoast SEO
			set_query_var( 'comments_per_page', 0 );
		}
		$comments_per_page = get_option( 'comments_per_page' );
		$cpage             = isset( $_POST['cpage'] ) ? sanitize_text_field( $_POST['cpage'] ) : 0;
		$filter_type       = isset( $_POST['filter_type'] ) ? sanitize_text_field( $_POST['filter_type'] ) : '';
		if ( $filter_type ) {
			$product_id = $post->ID;
			$cpage      = 0;
			$agrs       = array(
				'post_id'  => $product_id,
				'count'    => true,
				'meta_key' => 'rating',
				'status'   => 'approve',
			);
			$counts     = $response['update_count']['all'] = self::get_comments( $agrs );
			if ( $comments_per_page > 0 ) {
				$cpage = ceil( $counts / $comments_per_page );
				if ( $cpage > 1 ) {
					ob_start();
					do_action( 'viwcpr_get_pagination_loadmore_html', array(
						'only_button'  => true,
						'cpage'        => $cpage,
						'is_shortcode' => $is_shortcode
					) );
					$response['load_more_html'] = ob_get_clean();
				}
				if ( self::$settings->get_params( 'photo', 'sort' )['time'] == 2 ) {
					$cpage = 0;
				}
			}
			$response['cpage'] = $cpage;
			/*Update filter count*/
			for ( $i = 5; $i > 0; $i -- ) {
				$response['update_count'][ $i ] = self::stars_count( $i, $product_id );
			}
			if ( $filter_type !== 'verified' ) {
				$agrs2                                = array(
					'post_id'    => $product_id,
					'post_type'  => 'product',
					'count'      => true,
					'status'     => 'approve',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key'     => 'rating',
							'compare' => 'EXISTS',
						),
						array(
							'key'     => 'verified',
							'value'   => 1,
							'compare' => '=',
						),
					),
				);
				$response['update_count']['verified'] = self::get_comments( $agrs2 );
			}
			if ( $filter_type !== 'image' ) {
				$agrs1                             = array(
					'post_id'  => $product_id,
					'count'    => true,
					'meta_key' => 'reviews-images',
					'status'   => 'approve'
				);
				$response['update_count']['image'] = self::get_comments( $agrs1 );
			}
		}
		if ( $frontend_style === '1' ) {
			$comments_html = wp_list_comments( array(
				'page'              => $cpage, // current comment page
				'per_page'          => $comments_per_page,
				'style'             => 'div', // comments won't wrapped in this tag and it is awesome!
				'short_ping'        => true,
				'reverse_top_level' => self::$settings->get_params( 'photo', 'sort' )['time'] == 1 ? true : false,
				'reply_text'        => __( 'Reply', 'woocommerce-photo-reviews' ),
				'echo'              => false,
			) );
			preg_match_all( '/<article id=\"(.+?)\"/', $comments_html, $matches );
			$comment_ids = array();
			if ( isset( $matches[1] ) && is_array( $matches[1] ) && count( $matches[1] ) ) {
				foreach ( $matches[1] as $match ) {
					$comment_ids[] = substr( $match, 12 );
				}
			} else {
				preg_match_all( '/id=\"comment\-(.*?)\"/', $comments_html, $matches );
				if ( isset( $matches[1] ) && is_array( $matches[1] ) && count( $matches[1] ) ) {
					foreach ( $matches[1] as $match ) {
						$comment_ids[] = $match;
					}
				}
			}
			if ( count( $comment_ids ) ) {
				$masonry_popup     = self::$settings->get_params( 'photo', 'masonry_popup' );
				$enable_box_shadow = self::$settings->get_params( 'photo', 'enable_box_shadow' );
				ob_start();
				foreach ( $comment_ids as $comment_id ) {
					$v = get_comment( $comment_id );
					do_action( 'viwcpr_get_template_masonry_html', array(
						'settings'          => self::$settings,
						'my_comments'       => [ $v ],
						'masonry_popup'     => $masonry_popup,
						'enable_box_shadow' => $enable_box_shadow,
						'show_product'      =>  'off',
						'is_shortcode'      => false,
					) );
				}
				$response['html'] = ob_get_clean();
			}
		} else {
			$response['html'] = wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array(
				'page'              => $cpage, // current comment page
				'per_page'          => $comments_per_page,
				'style'             => 'ul', // comments won't wrapped in this tag and it is awesome!
				'short_ping'        => true,
				'reverse_top_level' => self::$settings->get_params( 'photo', 'sort' )['time'] == 1 ? true : false,
				'reply_text'        => __( 'Reply', 'woocommerce-photo-reviews' ),
				'callback'          => 'woocommerce_comments',
				'echo'              => false,
			) ) );
		}
		wp_send_json( $response );
		wp_die();
	}

	public function viwcpr_get_template_basic_html( $arg ) {
		if ( empty( $arg ) ) {
			return;
		}
		wc_get_template( 'viwcpr-template-basic-html.php', $arg,
			'woocommerce-photo-reviews' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
			WOOCOMMERCE_PHOTO_REVIEWS_TEMPLATES );
	}

	public function viwcpr_get_template_masonry_html( $arg ) {
		if ( empty( $arg ) ) {
			return;
		}
		wc_get_template( 'viwcpr-template-masonry-html.php', $arg,
			'woocommerce-photo-reviews' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
			WOOCOMMERCE_PHOTO_REVIEWS_TEMPLATES );
	}

	public function viwcpr_get_pagination_loadmore_html( $arg ) {
		if ( empty( $arg ) ) {
			return;
		}
		wc_get_template( 'viwcpr-pagination-loadmore-html.php', $arg,
			'woocommerce-photo-reviews' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
			WOOCOMMERCE_PHOTO_REVIEWS_TEMPLATES );
	}

	public function viwcpr_get_filters_html( $arg ) {
		if ( empty( $arg ) ) {
			return;
		}
		wc_get_template( 'viwcpr-filters-html.php', $arg,
			'woocommerce-photo-reviews' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
			WOOCOMMERCE_PHOTO_REVIEWS_TEMPLATES );
	}

	public function viwcpr_get_overall_rating_html( $arg ) {
		if ( empty( $arg ) ) {
			return;
		}
		wc_get_template( 'viwcpr-overall-rating-html.php', $arg,
			'woocommerce-photo-reviews' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
			WOOCOMMERCE_PHOTO_REVIEWS_TEMPLATES );
	}

	/**Do not apply review filter to apply so that reply will not be lost when customer select a filter
	 *
	 * @param $query_vars
	 */
	public static function remove_meta_query_for_reply( &$query_vars ) {
		if ( isset( $query_vars['meta_query'] ) && is_array( $query_vars['meta_query'] ) && count( $query_vars['meta_query'] ) ) {
			foreach ( $query_vars['meta_query'] as $key => $value ) {
				if ( is_array( $value ) && isset( $value['key'] ) && ( $value['key'] === 'reviews-images' || $value['key'] === 'verified' || $value['key'] === 'rating' ) ) {
					unset( $query_vars['meta_query'][ $key ] );
				}
			}
		}
	}

	/**Filter out reviews with a specific rating
	 *
	 * @param $vars
	 */
	public static function filter_review_rating( $vars ) {
		if ( ! empty( $vars->query_vars['parent__in'] ) || ! empty( $vars->query_vars['parent'] ) ) {
			self::remove_meta_query_for_reply( $vars->query_vars );

			return;
		}
		if ( ! self::$is_ajax && ! is_product() ) {
			return;
		}
		global $wcpr_shortcode_count;
		if ( $wcpr_shortcode_count === true ) {
			return;
		}
		$rating = 0;
		if ( self::$is_ajax ) {
			$rating = self::$rating;
		} else {
			if ( empty( $_GET['wcpr_is_ajax'] ) && self::$settings->get_params( 'pagination_ajax' ) && empty( $_GET['wcpr_thank_you_message'] ) ) {
				$filter_default_rating = self::$settings->get_params( 'filter_default_rating' );
				if ( $filter_default_rating ) {
					$rating = $filter_default_rating;
				} else {
					$rating = 0;
				}
			} else {
				if ( isset( $_GET['rating'] ) ) {
					switch ( $_GET['rating'] ) {
						case 1:
						case 2:
						case 3:
						case 4:
						case 5:
							$rating = $_GET['rating'];
							break;
						default:
							$rating = 0;
					}
				}
			}
		}
		if ( $rating ) {
			if ( $vars->query_vars['meta_query'] ) {
				$vars->query_vars['meta_query']['relation'] = 'AND';
				$vars->query_vars['meta_query'][]           = array(
					'key'     => 'rating',
					'value'   => $rating,
					'compare' => '='
				);
			} else {
				$custom                         = array(
					'relation' => 'AND'
				);
				$custom[]                       = array(
					'key'     => 'rating',
					'value'   => $rating,
					'compare' => '='
				);
				$vars->query_vars['meta_query'] = $custom;
			}
		}
	}

	/**Filter our reviews with images/verified
	 *
	 * @param $vars
	 */
	public static function filter_images_and_verified( $vars ) {
		if ( ! empty( $vars->query_vars['parent__in'] ) || ! empty( $vars->query_vars['parent'] ) ) {
			self::remove_meta_query_for_reply( $vars->query_vars );

			return;
		}
		if ( ! self::$is_ajax && ! is_product() ) {
			return;
		}
		global $wcpr_shortcode_count;
		if ( $wcpr_shortcode_count === true ) {
			return;
		}
		if ( self::$is_ajax ) {
			$image    = self::$image;
			$verified = self::$verified;
		} else {
			if ( empty( $_GET['wcpr_is_ajax'] ) && self::$settings->get_params( 'pagination_ajax' ) && empty( $_GET['wcpr_thank_you_message'] ) ) {
				$image    = self::$settings->get_params( 'filter_default_image' );
				$verified = self::$settings->get_params( 'filter_default_verified' );
			} else {
				$image    = isset( $_GET['image'] ) ? isset( $_GET['image'] ) : "";
				$verified = isset( $_GET['verified'] ) ? isset( $_GET['verified'] ) : "";
			}
		}
		if ( $vars->query_vars['meta_query'] ) {
			$vars->query_vars['meta_query']['relation'] = 'AND';
			if ( $image ) {
				$vars->query_vars['meta_query'][] = array(
					'key'     => 'reviews-images',
					'compare' => 'EXISTS'
				);
			}
			if ( $verified ) {
				$vars->query_vars['meta_query'][] = array(
					'key'     => 'verified',
					'value'   => 1,
					'compare' => '='
				);
			}
		} else {
			$custom = array(
				'relation' => 'AND'
			);
			if ( $image ) {
				$custom[] = array(
					'key'     => 'reviews-images',
					'compare' => 'EXISTS'
				);
			}
			if ( $verified ) {
				$custom[] = array(
					'key'     => 'verified',
					'value'   => 1,
					'compare' => '='
				);
			}
			$vars->query_vars['meta_query'] = $custom;
		}
	}

	public function sort_reviews( $comment_args ) {
		if ( self::$is_ajax ) {
			die;
		}
		$comment_args['orderby'] = 'comment_date_gmt';
		if ( self::$settings->get_params( 'photo', 'sort' )['time'] == 1 ) {
			$comment_args['order'] = 'DESC';
		} else {
			$comment_args['order'] = 'ASC';
		}

		return $comment_args;
	}

	/**Custom folder to save images uploaded in reviews imported from AliExpress or CSV
	 *
	 * @param $param
	 *
	 * @return mixed
	 */
	public static function import_upload_folder( $param ) {
		$settings             = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
		$import_upload_folder = $settings->get_params( 'import_upload_folder' );
		if ( $import_upload_folder ) {
			$subdir = '';
			if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
				// Generate the yearly and monthly dirs
				$time = current_time( 'mysql' );

				$y      = substr( $time, 0, 4 );
				$m      = substr( $time, 5, 2 );
				$subdir = "/$y/$m";
			}
			if ( ! empty( self::$product_id ) ) {
				$import_upload_folder = str_replace( '{product_id}', self::$product_id, $import_upload_folder );
			}
			$import_upload_folder = '/' . $import_upload_folder;

			$param['path'] = str_replace( $param['basedir'], $param['basedir'] . $import_upload_folder, $param['path'] );
			$param['url']  = str_replace( $param['baseurl'], $param['baseurl'] . $import_upload_folder, $param['url'] );
			if ( $subdir && ! empty( $param['subdir'] ) ) {
				$param['path'] = str_replace( $param['subdir'], $subdir, $param['path'] );
				$param['url']  = str_replace( $param['subdir'], $subdir, $param['url'] );
			}
		}

		return $param;
	}

	public static function fix_style( $flag_size ) {
		$margin_width = ( 60 - 60 * $flag_size ) / 2;
		$margin_heigh = ( 40 - 40 * $flag_size ) / 2;
		$style        = "transform: scale({$flag_size}); margin: -{$margin_heigh}px -{$margin_width}px";

		return $style;
	}
}