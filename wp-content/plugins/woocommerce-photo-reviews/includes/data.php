<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA {
	private $params;
	private $default;
	private static $prefix;
	private static $date_format;
	private static $time_format;
	protected static $instance = null;

	/**
	 * VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA constructor.
	 * Init setting
	 */
	public function __construct() {
		self::$prefix = 'wcpr-';
		global $woo_photo_reviews_settings;
		if ( ! $woo_photo_reviews_settings ) {
			$woo_photo_reviews_settings = get_option( '_wcpr_nkt_setting', array() );
		}
		$post_max      = ini_get( 'post_max_size' );
		$upload_max    = absint( ini_get( 'upload_max_filesize' ) );
		$max_allow     = $post_max > $upload_max ? $upload_max : $post_max;
		$maxsize       = $max_allow > 2 ? ( 2000 ) : ( $max_allow * 1000 );
		$this->default = array(
			'enable'                         => 'on',
			'mobile'                         => 'on',
			'key'                            => '',
			'photo'                          => array(
				/*At first, this option must be 'on' to use all following features
				Now this is used to turn images field on/off
				*/
				'enable'                     => 'on',
				'maxsize'                    => $maxsize,
				'maxfiles'                   => 5,
				'upload_images_requirement'  => 'Choose pictures(maxsize: {max_size}, max files: {max_files})',
				'required'                   => 'off',
				'display'                    => 1,
				'masonry_popup'              => 'review',
				'image_popup'                => 'below_thumb',
				'full_screen_mobile'         => '',
				'display_mobile'             => '',
				'col_num'                    => 3,
				'col_num_mobile'             => 1,
				'grid_bg'                    => '',
				'grid_item_bg'               => '#f3f3f3',
				'grid_item_border_color'     => '',
				'comment_text_color'         => '#000',
				'star_color'                 => '#ffb600',
				'max_content_length'         => '150',
				'sort'                       => array(
					'time' => 1
				),
				'enable_box_shadow'          => '1',
				'rating_count'               => 'on',
				'rating_count_bar_color'     => '#96588a',
				'filter'                     => array(
					'enable'                 => 'on',
					'area_border_color'      => '#e5e5e5',
					'area_bg_color'          => '',
					'button_border_color'    => '#e5e5e5',
					'button_color'           => '',
					'button_bg_color'        => '',
					'active_button_color'    => '',
					'active_button_bg_color' => '',
				),
				'custom_css'                 => '',
				'review_tab_first'           => 'off',
				'gdpr'                       => 'off',
				'gdpr_message'               => 'I agree with the privacy policy',
				'overall_rating'             => 'off',
				'single_product_summary'     => 'off',
				'verified'                   => 'default',
				'verified_text'              => 'Verified owner',
				'verified_badge'             => 'woocommerce-photo-reviews-badge-tick',
				'verified_color'             => '#29d50b',
				'verified_size'              => '',
				'hide_name'                  => 'off',
				'show_review_date'           => '1',
				'custom_review_date_format'  => '',
				'helpful_button_enable'      => 1,
				'helpful_button_title'       => 'Helpful?',
				'hide_rating_count_if_empty' => '',
				'hide_filters_if_empty'      => '',
			),
			'coupon'                         => array(
				'enable'                   => 'on',
				'require'                  => array(
					'photo'      => 'off',
					'min_rating' => 0,
					'owner'      => 'off',
					'register'   => 'off',
				),
				'form_title'               => 'Review our product to get a chance to receive coupon!',
				'products_gene'            => array(),
				'excluded_products_gene'   => array(),
				'categories_gene'          => array(),
				'excluded_categories_gene' => array(),
				'email'                    => array(
					'from_address' => '',
					'subject'      => 'Discount Coupon For Your Review',
					'heading'      => 'Thank You For Your Review!',
					'content'      => "Dear {customer_name},\nThank you so much for leaving review on my website!\nWe'd like to offer you this discount coupon as our thankfulness to you.\nCoupon code: {coupon_code}.\nDate expires: {date_expires}.\nYours sincerely!"
				),
				'coupon_select'            => 'kt_generate_coupon',
				'unique_coupon'            => array(
					'discount_type'               => 'percent',
					'coupon_amount'               => 11,
					'allow_free_shipping'         => 'no',
					'expiry_date'                 => null,
					'min_spend'                   => '',
					'max_spend'                   => '',
					'individual_use'              => 'no',
					'exclude_sale_items'          => 'no',
					'limit_per_coupon'            => 1,
					'limit_to_x_items'            => null,
					'limit_per_user'              => 0,
					'product_ids'                 => array(),
					'excluded_product_ids'        => array(),
					'product_categories'          => array(),
					'excluded_product_categories' => array(),
					'coupon_code_prefix'          => ''
				),
				'existing_coupon'          => ''
			),
			'followup_email'                 => array(
				'enable'                      => 'on',
				'from_address'                => '',
				'exclude_addresses'           => array(),
				'subject'                     => 'Review our products to get discount coupon',
				'content'                     => "Dear {customer_name},\nThank you for your recent purchase from our company.\nWe’re excited to count you as a customer. Our goal is always to provide our very best product so that our customers are happy. It\’s also our goal to continue improving. That\’s why we value your feedback.\nThank you so much for taking the time to provide us feedback and review. This feedback is appreciated and very helpful to us.\nBest regards!",
				'heading'                     => 'Review our product now',
				'amount'                      => 10,
				'unit'                        => 's',
				'products_restriction'        => array(),
				'excluded_categories'         => array(),
				'review_button'               => 'Review Now',
				'review_button_color'         => '#ffffff',
				'exclude_non_coupon_products' => 'off',
				'review_button_bg_color'      => '#88256f',
				'empty_product_price'         => '',
				'auto_login'                  => '1',
				'auto_login_exclude'          => array( 'administrator' ),
				'review_form_page'            => '',
				'order_statuses'              => array( 'wc-completed' ),
				'product_image_width'         => '150',
			),
			//new options-> checkbox value 1||0
			'pagination_ajax'                => '',
			'loadmore_button'                => 0,
			'reviews_container'              => '',
			'reviews_anchor_link'            => 'reviews',
			'set_email_restriction'          => 1,
			'multi_language'                 => 0,
			/*image caption*/
			'image_caption_enable'           => 0,
			'image_caption_position'         => 'bottom_wide',
			'image_caption_color'            => '#ffffff',
			'image_caption_bg_color'         => 'rgba(1,1,1,0.4)',
			'image_caption_font_size'        => '14',
			'custom_fields_enable'           => 0,
			'custom_fields_from_variations'  => 0,
			'custom_fields'                  => array(),
			'import_csv_date_format'         => 'Y-m-d H:i:s',
			'reviews_per_request'            => '10',
			'search_id_by_sku'               => '',
			'search_id_by_slug'               => '',
			'allow_empty_comment'            => '',
			'user_upload_folder'             => '',
			'user_upload_prefix'             => '',
			'import_upload_folder'           => '',
			'import_upload_prefix'           => '',
			'filter_default_image'           => '',
			'filter_default_verified'        => '',
			'filter_default_rating'          => '',
			'show_review_country'            => '',
			'review_title_enable'            => '1',
			'review_title_placeholder'       => 'Review Title',
			'thank_you_message'              => 'Thank you so much for reviewing our product.',
			'thank_you_message_coupon'       => 'Thank you for reviewing our product. A coupon code has been sent to your email address. Please check your mailbox for more details.',
			'phrases_filter'                 => array(
				'from_string' => array(),
				'to_string'   => array(),
				'sensitive'   => array(),
			),
			'restrict_number_of_reviews'     => '',
			'my_account_order_statuses'      => array( 'wc-completed' ),
			'email_template'                 => '',
			'reminder_email_template'        => '',
			'secret_key'                     => md5( time() ),
			'search_product_by'              => '_sku',
			'import_reviews_to'              => array(),
			'import_reviews_status'          => 0,
			'import_reviews_verified'        => 1,
			'import_reviews_vote'            => 0,
			'import_reviews_download_images' => 0,
			'import_reviews_order_info'      => 0,
			'share_reviews'                  => array( array() ),
		);

		$this->params = apply_filters( '_wcpr_nkt_setting', wp_parse_args( $woo_photo_reviews_settings, $this->default ) );
	}

	public static function get_instance( $new = false ) {
		if ( $new || null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function get_params( $name = "", $name_sub1 = "", $language = "" ) {
		$language = apply_filters( '_wcpr_nkt_setting_language', $language, $name, $name_sub1 );
		if ( ! $name ) {
			return $this->params;
		} elseif ( isset( $this->params[ $name ] ) ) {
			if ( $name_sub1 ) {
				if ( isset( $this->params[ $name ][ $name_sub1 ] ) ) {
					if ( $language ) {
						$name_language = $name_sub1 . '_' . $language;
						if ( isset( $this->params[ $name ][ $name_language ] ) ) {
							return apply_filters( '_wcpr_nkt_setting_' . $name . '__' . $name_language, $this->params[ $name ][ $name_language ] );
						} else {
							return apply_filters( '_wcpr_nkt_setting_' . $name . '__' . $name_language, $this->params[ $name ][ $name_sub1 ] );
						}
					} else {
						return apply_filters( '_wcpr_nkt_setting_' . $name . '__' . $name_sub1, $this->params[ $name ] [ $name_sub1 ] );
					}
				} elseif ( $this->default[ $name ] [ $name_sub1 ] ) {
					return apply_filters( '_wcpr_nkt_setting_' . $name . '__' . $name_sub1, $this->default[ $name ] [ $name_sub1 ] );
				} else {
					return false;
				}
			} else {
				if ( $language ) {
					$name_language = $name . '_' . $language;
					if ( isset( $this->params[ $name_language ] ) ) {
						return apply_filters( '_wcpr_nkt_setting_' . $name_language, $this->params[ $name_language ] );
					} else {
						return apply_filters( '_wcpr_nkt_setting_' . $name_language, $this->params[ $name ] );
					}
				} else {
					return apply_filters( '_wcpr_nkt_setting_' . $name, $this->params[ $name ] );
				}
			}
		} else {
			return false;
		}
	}

	public function get_default( $name = "", $name_sub1 = '' ) {
		if ( ! $name ) {
			return $this->default;
		} elseif ( isset( $this->default[ $name ] ) ) {
			if ( $name_sub1 ) {
				if ( isset( $this->default[ $name ][ $name_sub1 ] ) ) {
					return apply_filters( '_wcpr_nkt_setting_default_' . $name . '__' . $name_sub1, $this->default[ $name ] [ $name_sub1 ] );
				} else {
					return false;
				}
			} else {
				return apply_filters( '_wcpr_nkt_setting_default_' . $name, $this->default[ $name ] );
			}
		} else {
			return false;
		}
	}

	public static function get_date_format() {
		if ( self::$date_format === null ) {
			self::$date_format = get_option( 'date_format', 'F d, Y' );
			if ( ! self::$date_format ) {
				self::$date_format = 'F d, Y';
			}
		}

		return self::$date_format;
	}

	public static function get_time_format() {
		if ( self::$time_format === null ) {
			self::$time_format = get_option( 'time_format', 'H:i:s' );
			if ( ! self::$time_format ) {
				self::$time_format = 'H:i:s';
			}
		}

		return self::$time_format;
	}

	public static function get_datetime_format() {
		return self::get_date_format() . ' ' . self::get_time_format();
	}

	public static function get_the_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
//check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return $ip;
	}

	public static function set( $name, $set_name = false ) {
		if ( is_array( $name ) ) {
			return implode( ' ', array_map( array( 'VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA', 'set' ), $name ) );
		} else {
			if ( $set_name ) {
				return esc_attr__( str_replace( '-', '_', self::$prefix . $name ) );

			} else {
				return esc_attr__( self::$prefix . $name );

			}
		}
	}

	public static function search_product_statuses() {
		return apply_filters( 'woocommerce_photo_reviews_search_product_statuses', current_user_can( 'edit_private_products' ) ? array(
			'private',
			'publish'
		) : array( 'publish' ) );
	}

	/**Count orders of a customer by product
	 *
	 * @param $product_id
	 * @param $customer_email
	 * @param $user_id
	 *
	 * @return int|string|null
	 */
	public static function get_orders_count_by_product( $product_id, $customer_email, $user_id ) {
		global $wpdb;
		if ( ! $product_id || ( ! $customer_email && ! $user_id ) ) {
			return 0;
		}
		$customer_data = array();

		if ( is_email( $customer_email ) ) {
			$customer_data[] = $customer_email;
		} elseif ( $user_id ) {
			$user = get_user_by( 'id', $user_id );

			if ( isset( $user->user_email ) ) {
				$customer_data[] = $user->user_email;
			}
		}

		$customer_data = array_map( 'esc_sql', array_filter( array_unique( $customer_data ) ) );
		$statuses      = array_map( 'esc_sql', wc_get_is_paid_statuses() );

		if ( count( $customer_data ) === 0 ) {
			return 0;
		}

		$result = $wpdb->get_var(
			"
			SELECT COUNT(im.meta_value) FROM {$wpdb->posts} AS p
			INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
			INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
			INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
			WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
			AND pm.meta_key IN ( '_billing_email', '_customer_user' )
			AND im.meta_key IN ( '_product_id', '_variation_id' )
			AND im.meta_value = {$product_id}
			AND pm.meta_value IN ( '" . implode( "','", $customer_data ) . "' )
		"
		);

		return $result;
	}

	/**
	 * @param $customer_email
	 * @param $product_id
	 * @param $rating
	 *
	 * @return array|int
	 */
	public static function reviews_count_of_customer( $customer_email, $product_id, $rating = '' ) {
		$comment_count_args = array(
			'author_email' => $customer_email,
			'type'         => 'review',
			'count'        => true,
		);
		if ( $product_id ) {
			$comment_count_args['post_id'] = $product_id;
		}
		if ( $rating === '' ) {
			$comment_count_args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key'     => 'rating',
					'compare' => 'EXISTS',
				)
			);
		} elseif ( $rating !== false ) {
			$comment_count_args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key'     => 'rating',
					'value'   => $rating,
					'compare' => '=',
				)
			);
		}

		return get_comments( $comment_count_args );
	}

	public static function is_email_template_customizer_active() {
		return ( class_exists( 'WooCommerce_Email_Template_Customizer' ) || class_exists( 'Woo_Email_Template_Customizer' ) );
	}

	public static function search_product_by() {
		$instance          = self::get_instance();
		$search_product_by = $instance->get_params( 'search_product_by' );
		if ( ! $search_product_by ) {
			$search_product_by = '_sku';
		}

		return $search_product_by;
	}
}