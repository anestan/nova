<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_API
 */
class VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_API {
	protected $product_data;
	protected $settings;
	protected $orders_tracking_carriers;
	protected $found_carriers;
	protected $process_description;
	protected $namespace;

	public function __construct() {
		$this->found_carriers = array(
			'url'      => array(),
			'carriers' => array(),
		);
		$this->settings       = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
		$this->namespace      = 'woocommerce-photo-reviews';
		add_action( 'rest_api_init', array( $this, 'register_api' ) );
		add_filter( 'woocommerce_rest_is_request_to_rest_api', array(
			$this,
			'woocommerce_rest_is_request_to_rest_api'
		) );
	}

	public function woocommerce_rest_is_request_to_rest_api( $is_request_to_rest_api ) {
		$rest_prefix = trailingslashit( rest_get_url_prefix() );
		$request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		if ( false !== strpos( $request_uri, $rest_prefix . 'woocommerce-photo-reviews/' ) ) {
			$is_request_to_rest_api = true;
		}

		return $is_request_to_rest_api;
	}

	/**
	 * Register API json
	 */
	public function register_api() {
		register_rest_route(
			$this->namespace, '/import_reviews', array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'import_reviews_normal' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'product_sku'     => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'description'       => '',
					),
					'reviews_data'    => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'description'       => '',
					),
					'require_version' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'description'       => '',
					),
					'secret_key'      => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'description'       => '',
					),
					'import_from'     => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'description'       => '',
					),
				),
			)
		);

		/*Auth method*/
//		register_rest_route(
//			$this->namespace, '/auth', array(
//				'methods'             => WP_REST_Server::CREATABLE,
//				'callback'            => array( $this, 'auth' ),
//				'permission_callback' => '__return_true',
//			)
//		);
//		register_rest_route(
//			$this->namespace, '/auth/import_reviews', array(
//				'methods'             => WP_REST_Server::CREATABLE,
//				'callback'            => array( $this, 'import_reviews_auth' ),
//				'permission_callback' => array( $this, 'permissions_check' ),
//			)
//		);
	}

	/**
	 * @param $consumer_key
	 *
	 * @return array|object|null
	 */
	private function get_user_data_by_consumer_key( $consumer_key ) {
		global $wpdb;

		$consumer_key = wc_api_hash( sanitize_text_field( $consumer_key ) );
		$user         = $wpdb->get_row(
			$wpdb->prepare(
				"
			SELECT key_id, user_id, permissions, consumer_key, consumer_secret, nonces
			FROM {$wpdb->prefix}woocommerce_api_keys
			WHERE consumer_key = %s
		",
				$consumer_key
			)
		);

		return $user;
	}

	/**
	 * @param $request WP_REST_Request
	 *
	 * @return bool|WP_Error
	 */
	public function permissions_check( $request ) {
		if ( ! wc_rest_check_post_permissions( 'product', 'create' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_create', esc_html__( 'Unauthorized', 'woocommerce-photo-reviews' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * @param $request WP_REST_Request
	 */

	public function auth( $request ) {
		$consumer_key    = sanitize_text_field( $request->get_param( 'consumer_key' ) );
		$consumer_secret = sanitize_text_field( $request->get_param( 'consumer_secret' ) );
		if ( $consumer_key && $consumer_secret ) {
			$user = $this->get_user_data_by_consumer_key( $consumer_key );
			if ( $user && hash_equals( $user->consumer_secret, $consumer_secret ) ) {
				update_option( 'vi_wcpr_temp_api_credentials', $request->get_params() );
			}
		}
	}

	/**Validate request from chrome extension
	 *
	 * @param $request WP_REST_Request
	 * @param bool $check_key
	 */

	public function validate( $request, $check_key = true ) {
		$result = array(
			'status'       => 'error',
			'message'      => '',
			'message_type' => 1,
		);

		/*check ssl*/
		if ( ! is_ssl() ) {
			$result['message']      = esc_html__( 'SSL is required', 'woocommerce-photo-reviews' );
			$result['message_type'] = 2;

			wp_send_json( $result );
		}
		/*check enable*/
		if ( 'on' !== $this->settings->get_params( 'enable' ) ) {
			$result['message']      = esc_html__( 'WooCommerce Photo Reviews plugin is currently disabled. Please enable it to use this function.', 'woocommerce-photo-reviews' );
			$result['message_type'] = 2;

			wp_send_json( $result );
		}
		/*check key*/
		if ( $check_key ) {
			$secret_key = $request->get_param( 'secret_key' );
			if ( ! $secret_key || $secret_key !== $this->settings->get_params( 'secret_key' ) ) {
				$result['message']      = esc_html__( 'Secret key does not match', 'woocommerce-photo-reviews' );
				$result['message_type'] = 2;

				wp_send_json( $result );
			}
		}
		$require_version = $request->get_param( 'require_version' );

		/*check version*/
		if ( version_compare( VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION, $require_version, '<' ) ) {
			$result['message']      = sprintf( esc_html__( 'Require WooCommerce Photo Reviews plugin version %s', 'woocommerce-photo-reviews' ), $require_version );
			$result['message_type'] = 2;

			wp_send_json( $result );
		}
	}

	/**
	 * @param $request WP_REST_Request
	 */
	public function import_reviews_normal( $request ) {
		$this->validate( $request );
		$this->import_reviews( $request );
	}

	/**
	 * @param $request WP_REST_Request
	 */
	public function import_reviews_auth( $request ) {
		$this->validate( $request, false );
		$this->import_reviews( $request );
	}

	/**
	 * @param $request WP_REST_Request
	 */
	public function import_reviews( $request ) {
		$result       = array(
			'status'       => 'success',
			'message'      => esc_html__( 'Import review successfully', 'woocommerce-photo-reviews' ),
			'message_type' => 1,
			'details'      => array(),
		);
		$product_sku  = $request->get_param( 'product_sku' );
		$import_from  = $request->get_param( 'import_from' ) === 'aliexpress' ? 'id_import_reviews_from_ali' : 'id_import_reviews_from_amazon';
		$reviews_data = json_decode( base64_decode( $request->get_param( 'reviews_data' ) ), true, 512, 2 );
		if ( ! $product_sku ) {
			$result['status']  = 'error';
			$result['message'] = esc_html__( 'Invalid product', 'woocommerce-photo-reviews' );
			wp_send_json( $result );
		} elseif ( ! is_array( $reviews_data ) || ! count( $reviews_data ) ) {
			$result['status']  = 'error';
			$result['message'] = esc_html__( 'Invalid review data', 'woocommerce-photo-reviews' );
			wp_send_json( $result );
		}
		$import_reviews_to = $this->settings->get_params( 'import_reviews_to' );
		if ( count( $import_reviews_to ) ) {
			$product_ids = array();
			foreach ( $import_reviews_to as $import_reviews_to_id ) {
				if ( wc_get_product( $import_reviews_to_id ) ) {
					$product_ids[] = $import_reviews_to_id;
				}
			}
		} else {
			$product_ids = $this->get_product_ids( $product_sku );
		}
		if ( ! count( $product_ids ) ) {
			$result['status']  = 'error';
			$result['message'] = esc_html__( 'Product not exists', 'woocommerce-photo-reviews' );
			wp_send_json( $result );
		}
		$phrases_filter            = $this->settings->get_params( 'phrases_filter' );
		$review_status             = $this->settings->get_params( 'import_reviews_status' );
		$verified                  = $this->settings->get_params( 'import_reviews_verified' );
		$vote                      = $this->settings->get_params( 'import_reviews_vote' );
		$download_images           = $this->settings->get_params( 'import_reviews_download_images' );
		$import_reviews_order_info = $this->settings->get_params( 'import_reviews_order_info' );
		$dispatch                  = false;
		foreach ( $reviews_data as $review_data ) {
			$review_id   = strval( $review_data['review_id'] );
			$detail      = array(
				'review_id'         => $review_id,
				'added_to_products' => array(),
				'status'            => '',
				'message'           => '',
			);
			$review_date = strtotime( self::process_review_date( $review_data['review_date'] ) );
			if ( $review_date === false ) {
				$review_date = current_time( 'timestamp' );
			}
			foreach ( $product_ids as $product_id ) {
				if ( ! get_comments( array(
					'post_id'    => $product_id,
					'status'     => array( 0, 1, 'spam' ),
					'meta_key'   => $import_from,
					'meta_value' => $review_id,
					'number'     => 1,
					'count'      => true,
				) ) ) {
					$comment_rating = self::process_rating( $review_data['rating'] );
					if ( $comment_rating < 1 || $comment_rating > 5 ) {
						continue;
					}
					$comment_author  = apply_filters( 'woocommerce_photo_reviews_import_ali_comment_author', isset( $review_data['author'] ) ? $review_data['author'] : '' );
					$comment_content = apply_filters( 'woocommerce_photo_reviews_import_ali_comment_content', isset( $review_data['review'] ) ? trim( strip_tags( $review_data['review'], '<br>' ) ) : '' );

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
					$comment_id = VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Import_Ali::insert_comment( array(
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
						'comment_date'         => date( 'Y-m-d h:i:s', $review_date ),
						'comment_approved'     => $review_status,
						'comment_meta'         => array(
							'rating' => $comment_rating,
						),
					) );
					if ( $comment_id ) {
						$detail['added_to_products'][] = $product_id;
						if ( ! empty( $review_data['country'] ) ) {
							update_comment_meta( $comment_id, 'wcpr_review_country', $review_data['country'] );
						}
						$review_title = isset( $review_data['review_title'] ) ? trim( strip_tags( nl2br( $review_data['review_title'] ) ) ) : '';
						if ( $review_title ) {
							update_comment_meta( $comment_id, 'wcpr_review_title', $review_title );
						}
						if ( $verified ) {
							update_comment_meta( $comment_id, 'verified', '1' );
						}
						if ( $vote ) {
							$upVoteCount   = intval( $review_data['vote_up'] );
							$downVoteCount = intval( $review_data['vote_down'] );
							if ( $upVoteCount > 0 ) {
								update_comment_meta( $comment_id, 'wcpr_vote_up_count', $upVoteCount );
							}
							if ( $downVoteCount > 0 ) {
								update_comment_meta( $comment_id, 'wcpr_vote_down_count', $downVoteCount );
							}
						}
						if ( $import_reviews_order_info && isset( $review_data['attributes'] ) && is_array( $review_data['attributes'] ) ) {
							$custom_fields_data = array();
							foreach ( $review_data['attributes'] as $attribute ) {
								if ( $attribute['name'] && $attribute['value'] ) {
									$custom_fields_data[] = array(
										'name'  => $attribute['name'],
										'value' => $attribute['value'],
										'unit'  => '',
									);
								}
							}
							if ( count( $custom_fields_data ) ) {
								update_comment_meta( $comment_id, 'wcpr_custom_fields', $custom_fields_data );
							}
						}
						update_comment_meta( $comment_id, $import_from, $review_id );
						if ( isset( $review_data['images'] ) && is_array( $review_data['images'] ) && count( $review_data['images'] ) ) {
							update_comment_meta( $comment_id, 'reviews-images', $review_data['images'] );
							if ( $download_images ) {
								$dispatch = true;
								$images   = array( 'comment_id' => $comment_id );
								VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Import_Csv::$background_process->push_to_queue( $images );
							}
						}
						if ( $review_status == 1 ) {
							VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Import_Csv::update_product_reviews_and_rating( $product_id, $comment_rating );
						}
					}
				}
			}
			$added_count = count( $detail['added_to_products'] );
			if ( $added_count > 0 ) {
				$detail['status']  = 'success';
				$detail['message'] = sprintf( _n( 'Added to %s product', 'Added to %s products', $added_count, 'woocommerce-photo-reviews' ), $added_count );
			} else {
				$detail['status']  = 'error';
				$detail['message'] = esc_html__( 'Review exists', 'woocommerce-photo-reviews' );
			}
			$result['details'][] = $detail;
		}
		if ( $dispatch ) {
			VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Import_Csv::$background_process->save()->dispatch();
		}
		wp_send_json( $result );
	}

	public function get_product_ids( $product_sku ) {
		$search_product_by = $this->settings->get_params( 'search_product_by' );
		if ( ! $search_product_by ) {
			$search_product_by = '_sku';
		}
		$args = wp_parse_args( array(
			'post_type'      => 'product',
			'posts_per_page' => 50,
			'meta_key'       => $search_product_by,
			'meta_value'     => $product_sku,
			'post_status'    => array(
				'publish',
				'draft',
				'private'
			),
			'fields'         => 'ids'
		) );

		$the_query   = new WP_Query( $args );
		$product_ids = array();
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$product_ids[] = get_the_ID();
			}
		}
		wp_reset_postdata();

		return $product_ids;
	}

	/**Convert rating from style attribute
	 *
	 * @param $rating
	 *
	 * @return int
	 */
	public static function process_rating( $rating ) {
		preg_match( '/width:(.+?)%/', $rating, $match );
		if ( $match && count( $match ) === 2 ) {
			$rating = intval( $match[1] / 20 );
		}

		return intval( $rating );
	}

	/**Process review date from some other languages
	 *
	 * @param $review_date
	 *
	 * @return mixed
	 */
	public static function process_review_date( $review_date ) {
		$review_date = str_ireplace(
			array(
				'janvier',
				'januar',
				'gennaio',
				'januari',
				'enero',
				'ocak',
				'janeiro',
			),
			'january', $review_date );
		$review_date = str_ireplace(
			array(
				'février',
				'februar',
				'febbraio',
				'februari',
				'febrero',
				'şubat',
				'fevereiro',
			),
			'february', $review_date );
		$review_date = str_ireplace(
			array(
				'mars',
				'märz',
				'marzo',
				'maart',
				'mart',
				'março',
			),
			'march', $review_date );
		$review_date = str_ireplace(
			array(
				'avril',
				'aprile',
				'abril',
				'nisan',
			),
			'april', $review_date );
		$review_date = str_ireplace(
			array(
				'mai',
				'maggio',
				'mei',
				'mayo',
				'mayıs',
				'maio',
			),
			'may', $review_date );
		$review_date = str_ireplace(
			array(
				'juin',
				'juni',
				'giugno',
				'junio',
				'haziran',
				'junho',
			),
			'june', $review_date );
		$review_date = str_ireplace(
			array(
				'juillet',
				'juli',
				'luglio',
				'julio',
				'temmuz',
				'julho',
			),
			'july', $review_date );
		$review_date = str_ireplace(
			array(
				'août',
				'agosto',
				'augustus',
				'ağustos',
			),
			'august', $review_date );
		$review_date = str_ireplace(
			array(
				'septembre',
				'settembre',
				'septiembre',
				'eylül',
				'setembro',
			),
			'september', $review_date );
		$review_date = str_ireplace(
			array(
				'octobre',
				'oktober',
				'ottobre',
				'octubre',
				'ekim',
				'outubro',
			),
			'october', $review_date );
		$review_date = str_ireplace(
			array(
				'novembre',
				'noviembre',
				'kasım',
				'novembro',
			),
			'november', $review_date );
		$review_date = str_ireplace(
			array(
				'décembre',
				'dezember',
				'dicembre',
				'diciembre',
				'aralık',
				'dezembro',
			),
			'december', $review_date );
		preg_match( '/年(.+)月/m', $review_date, $match );
		if ( count( $match ) < 2 ) {
			preg_match( '/년 (.+)월 /m', $review_date, $match );
		}
		if ( count( $match ) === 2 ) {
			$months = array(
				1  => 'january',
				2  => 'february',
				3  => 'march',
				4  => 'april',
				5  => 'may',
				6  => 'june',
				7  => 'july',
				8  => 'august',
				9  => 'september',
				10 => 'october',
				11 => 'november',
				12 => 'december',
			);
			if ( isset( $months[ $match[1] ] ) ) {
				$month       = $months[ $match[1] ];
				$review_date = str_replace( $match[0], $month, $review_date );
				$review_date = preg_replace( '/[^\00-\255]+/u', '', $review_date );
				$dates       = explode( $month, $review_date );
				if ( count( $dates ) > 1 ) {
					$year = trim( $dates[0] );
					$date = trim( $dates[1] );
					if ( count( $dates ) > 1 && ( strlen( $year ) === 4 || $date > 31 ) ) {
						$review_date = "{$dates[1]} {$month} {$dates[0]}";
					}
				}
			}
		}

		return str_replace( array( '.', ' de ' ), '', $review_date );
	}
}