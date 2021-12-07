<?php

/**
 * Class VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Import_Csv
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
ini_set( 'auto_detect_line_endings', true );

class VI_WOOCOMMERCE_PHOTO_REVIEWS_Admin_Import_Csv {
	protected $settings;
	protected $process;
	protected $request;
	protected $step;
	protected $file_url;
	protected $header;
	protected $error;
	protected $index;
	public static $background_process;

	public function __construct() {
		$this->settings = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
		add_action( 'init', array( $this, 'background_process' ) );
		add_action( 'admin_menu', array( $this, 'add_menu' ), 19 );
		add_action( 'admin_init', array( $this, 'import_csv' ) );
		add_action( 'wp_ajax_woocommerce_photo_reviews_import', array( $this, 'import' ) );
		add_action( 'woocommerce_photo_reviews_importer_scheduled_cleanup', array(
			$this,
			'scheduled_cleanup'
		) );
	}

	public static function set( $name, $set_name = false ) {
		return VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::set( $name, $set_name );
	}

	public function scheduled_cleanup( $attachment_id ) {
		if ( $attachment_id ) {
			wp_delete_attachment( $attachment_id, true );
		}
	}

	public function background_process() {
		self::$background_process = new WP_WOOCOMMERCE_PHOTO_REVIEWS_Process();
	}

	public function add_menu() {
		add_submenu_page(
			'woocommerce-photo-reviews', __( 'Import Reviews', 'woocommerce-photo-reviews' ), __( 'Import Reviews', 'woocommerce-photo-reviews' ), 'manage_options', 'wcpr_import_reviews', array(
				$this,
				'import_csv_callback'
			)
		);
	}

	public function import_csv() {
		global $pagenow;
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		if ( $pagenow === 'admin.php' && $page === 'wcpr_import_reviews' ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			$this->step     = isset( $_REQUEST['step'] ) ? sanitize_text_field( $_REQUEST['step'] ) : '';
			$this->file_url = isset( $_REQUEST['file_url'] ) ? urldecode_deep( $_REQUEST['file_url'] ) : '';
			if ( $this->step == 'mapping' ) {
				if ( is_file( $this->file_url ) ) {
					if ( ( $handle = fopen( $this->file_url, "r" ) ) !== false ) {
						$this->header = fgetcsv( $handle, 0, "," );
						fclose( $handle );
						if ( ! count( $this->header ) ) {
							$this->step  = '';
							$this->error = esc_html__( 'Invalid file.', 'woocommerce-photo-reviews' );
						}
					} else {
						$this->step  = '';
						$this->error = esc_html__( 'Invalid file.', 'woocommerce-photo-reviews' );
					}
				} else {
					$this->step  = '';
					$this->error = esc_html__( 'Invalid file.', 'woocommerce-photo-reviews' );
				}
			}

			if ( ! isset( $_POST['_woocommerce_photo_reviews_import_nonce'] ) || ! wp_verify_nonce( $_POST['_woocommerce_photo_reviews_import_nonce'], 'woocommerce_photo_reviews_import_action_nonce' ) ) {
				return;
			}
			if ( isset( $_POST['woocommerce_photo_reviews_import'] ) ) {
				$this->step     = 'import';
				$this->file_url = isset( $_POST['woocommerce_photo_reviews_file_url'] ) ? stripslashes( $_POST['woocommerce_photo_reviews_file_url'] ) : '';
				$map_to         = isset( $_POST['wcpr_map_to'] ) ? array_map( 'sanitize_text_field', $_POST['wcpr_map_to'] ) : array();
				if ( is_file( $this->file_url ) ) {
					if ( ( $file_handle = fopen( $this->file_url, "r" ) ) !== false ) {
						$header  = fgetcsv( $file_handle, 0, "," );
						$headers = array(
							'comment_post_ID'      => esc_html__( 'Product ID', 'woocommerce-photo-reviews' ),
							'comment_author'       => esc_html__( 'Author name', 'woocommerce-photo-reviews' ),
							'comment_author_email' => esc_html__( 'Author email', 'woocommerce-photo-reviews' ),
							'comment_author_url'   => esc_html__( 'Author URL', 'woocommerce-photo-reviews' ),
							'wcpr_review_title'    => esc_html__( 'Review title', 'woocommerce-photo-reviews' ),
							'comment_content'      => esc_html__( 'Content', 'woocommerce-photo-reviews' ),
							'comment_approved'     => esc_html__( 'Comment status', 'woocommerce-photo-reviews' ),
							'rating'               => esc_html__( 'Rating', 'woocommerce-photo-reviews' ),
							'verified'             => esc_html__( 'Verified', 'woocommerce-photo-reviews' ),
							'reviews-images'       => esc_html__( 'Photos', 'woocommerce-photo-reviews' ),
							'wcpr_custom_fields'   => esc_html__( 'Optional fields/Variation', 'woocommerce-photo-reviews' ),
							'wcpr_vote_up'         => esc_html__( 'Up-vote count', 'woocommerce-photo-reviews' ),
							'wcpr_vote_down'       => esc_html__( 'Down-vote count', 'woocommerce-photo-reviews' ),
							'comment_parent'       => esc_html__( 'Comment parent', 'woocommerce-photo-reviews' ),
							'user_id'              => esc_html__( 'User id', 'woocommerce-photo-reviews' ),
							'comment_author_IP'    => esc_html__( 'Author IP', 'woocommerce-photo-reviews' ),
							'comment_agent'        => esc_html__( 'Comment agent', 'woocommerce-photo-reviews' ),
							'comment_date'         => esc_html__( 'Comment date', 'woocommerce-photo-reviews' ),
							'comment_date_gmt'     => esc_html__( 'Comment date gmt', 'woocommerce-photo-reviews' ),
						);
						$index   = array();
						foreach ( $headers as $header_k => $header_v ) {
							if ( ! empty( $map_to[ $header_k ] ) ) {
								$field_index = array_search( $map_to[ $header_k ], $header );
								if ( $field_index === false ) {
									$index[ $header_k ] = - 1;
								} else {
									$index[ $header_k ] = $field_index;
								}
							} else {
								$index[ $header_k ] = - 1;
							}
						}
						$required_fields = array(
							'comment_post_ID',
							'comment_author',
							'rating',
							'comment_content',
						);
						foreach ( $required_fields as $required_field ) {
							if ( 0 > $index[ $required_field ] ) {
								wp_safe_redirect( add_query_arg( array( 'wcpr_error' => 1 ), admin_url( 'admin.php?page=wcpr_import_reviews&step=mapping&file_url=' . urlencode( $this->file_url ) ) ) );
								exit();
							}
						}
						$this->index = $index;
					} else {
						wp_safe_redirect( add_query_arg( array( 'wcpr_error' => 2 ), admin_url( 'admin.php?page=wcpr_import_reviews&file_url=' . urlencode( $this->file_url ) ) ) );
						exit();
					}
				} else {
					wp_safe_redirect( add_query_arg( array( 'wcpr_error' => 3 ), admin_url( 'admin.php?page=wcpr_import_reviews&file_url=' . urlencode( $this->file_url ) ) ) );
					exit();
				}

			} else if ( isset( $_POST['woocommerce_photo_reviews_select_file'] ) ) {
				if ( ! isset( $_FILES['woocommerce_photo_reviews_file'] ) ) {
					$error = new WP_Error( 'woocommerce_photo_reviews_csv_importer_upload_file_empty', __( 'File is empty. Please upload something more substantial. This error could also be caused by uploads being disabled in your php.ini or by post_max_size being defined as smaller than upload_max_filesize in php.ini.', 'woocommerce-photo-reviews' ) );
					wp_die( $error->get_error_messages() );
				} elseif ( ! empty( $_FILES['woocommerce_photo_reviews_file']['error'] ) ) {
					$error = new WP_Error( 'woocommerce_photo_reviews_csv_importer_upload_file_error', __( 'File is error.', 'woocommerce-photo-reviews' ) );
					wp_die( $error->get_error_messages() );
				} else {
					$import    = $_FILES['woocommerce_photo_reviews_file'];
					$overrides = array(
						'test_form' => false,
						'mimes'     => array(
							'csv' => 'text/csv',
						),
						'test_type' => true,
					);
					$upload    = wp_handle_upload( $import, $overrides );
					if ( isset( $upload['error'] ) ) {
						wp_die( $upload['error'] );
					}
					// Construct the object array.
					$object = array(
						'post_title'     => basename( $upload['file'] ),
						'post_content'   => $upload['url'],
						'post_mime_type' => $upload['type'],
						'guid'           => $upload['url'],
						'context'        => 'import',
						'post_status'    => 'private',
					);

					// Save the data.
					$id = wp_insert_attachment( $object, $upload['file'] );
					if ( is_wp_error( $id ) ) {
						wp_die( $id->get_error_messages() );
					}
					/*
					 * Schedule a cleanup for one day from now in case of failed
					 * import or missing wp_import_cleanup() call.
					 */
					wp_schedule_single_event( time() + DAY_IN_SECONDS, 'woocommerce_photo_reviews_importer_scheduled_cleanup', array( $id ) );
					wp_safe_redirect( add_query_arg( array(
						'step'     => 'mapping',
						'file_url' => urlencode( $upload['file'] ),
					) ) );
					exit();
				}
			}

		}
	}

	public function get_woo_product_id( $shopify_product_id , $type = '') {
		$product_id = '';
		if ( $shopify_product_id ) {
			$product_args = array(
				'post_type'      => 'product',
				'post_status'    => array( 'publish', 'pending', 'draft' ),
				'posts_per_page' => '1',
				'no_found_rows'  => true,
				'fields'         => 'ids',
			);
			switch ($type){
			    case 'slug':
			        $product_args['post_name__in'] = [$shopify_product_id];
			        break;
                default:
                    $product_args['meta_query']= array(
	                    'relation' => 'AND',
	                    array(
		                    'relation' => 'OR',
		                    array(
			                    'key'     => '_shopify_product_id',
			                    'value'   => $shopify_product_id,
			                    'compare' => '=',
		                    ),
		                    array(
			                    'key'     => '_s2w_shopipy_product_id',
			                    'value'   => $shopify_product_id,
			                    'compare' => '=',
		                    ),
	                    )
                    );
            }
			$the_query    = new WP_Query( $product_args );
			if ( $the_query->have_posts() ) {
				$the_query->the_post();
				$product_id = get_the_ID();

			}
			wp_reset_postdata();
		}

		return $product_id;
	}

	public function import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'You do not have permission.', 'woocommerce-photo-reviews' ),
				)
			);
		}
		$file_url               = isset( $_POST['file_url'] ) ? stripslashes( $_POST['file_url'] ) : '';
		$start                  = isset( $_POST['start'] ) ? absint( sanitize_text_field( $_POST['start'] ) ) : 0;
		$ftell                  = isset( $_POST['ftell'] ) ? absint( sanitize_text_field( $_POST['ftell'] ) ) : 0;
		$total                  = isset( $_POST['total'] ) ? absint( sanitize_text_field( $_POST['total'] ) ) : 0;
		$step                   = isset( $_POST['step'] ) ? sanitize_text_field( $_POST['step'] ) : '';
		$index                  = isset( $_POST['wcpr_index'] ) ? array_map( 'intval', $_POST['wcpr_index'] ) : array();
		$reviews_per_request    = isset( $_POST['reviews_per_request'] ) ? absint( sanitize_text_field( $_POST['reviews_per_request'] ) ) : 1;
		$import_from_loox       = isset( $_POST['import_from_loox'] ) ? sanitize_text_field( $_POST['import_from_loox'] ) : '';
		$search_id_by_sku       = isset( $_POST['search_id_by_sku'] ) ? sanitize_text_field( $_POST['search_id_by_sku'] ) : '';
		$search_id_by_slug       = isset( $_POST['search_id_by_slug'] ) ? sanitize_text_field( $_POST['search_id_by_slug'] ) : '';
		$imported               = isset( $_POST['imported'] ) ? absint( sanitize_text_field( $_POST['imported'] ) ) : 0;
		$import_csv_date_format = isset( $_POST['import_csv_date_format'] ) ? sanitize_text_field( stripslashes( $_POST['import_csv_date_format'] ) ) : '';
		$gmt_offset             = get_option( 'gmt_offset' );

		if ( is_file( $file_url ) ) {
			if ( ( $file_handle = fopen( $file_url, "r" ) ) !== false ) {
				$header = fgetcsv( $file_handle, 0, "," );
				unset( $header );
				$count = 0;
				if ( $step === 'check' ) {
					$settings                           = $this->settings->get_params();
					$settings['reviews_per_request']    = $reviews_per_request;
					$settings['search_id_by_sku']       = $search_id_by_sku;
					$settings['search_id_by_slug']       = $search_id_by_slug;
					$settings['import_csv_date_format'] = $import_csv_date_format;
					update_option( '_wcpr_nkt_setting', $settings );
					$count = 1;
					while ( ( $item = fgetcsv( $file_handle, 0, "," ) ) !== false ) {
						$count ++;
					}
					fclose( $file_handle );
					wp_send_json( array(
						'status' => 'success',
						'total'  => $count,
					) );
				}
				if ( $ftell > 0 ) {
					fseek( $file_handle, $ftell );
				} elseif ( $start > 1 ) {
					for ( $i = 0; $i < $start; $i ++ ) {
						$buff = fgetcsv( $file_handle, 0, "," );
						unset( $buff );
					}
				}
				if ( $import_from_loox ) {
					while ( ( $item = fgetcsv( $file_handle, 0, "," ) ) !== false ) {
						$count ++;
						$start ++;
						$ftell        = ftell( $file_handle );
						$comment_args = array(
							'comment_post_ID'      => '',
							'comment_author'       => '',
							'comment_author_email' => '',
							'comment_author_url'   => '',
							'comment_content'      => '',
							'comment_approved'     => '1',
							'comment_parent'       => '',
							'user_id'              => '',
							'comment_author_IP'    => '',
							'comment_agent'        => '',
							'comment_date'         => '',
							'comment_date_gmt'     => '',
						);
						foreach ( $comment_args as $comment_arg_k => $comment_arg_v ) {
							if ( $index[ $comment_arg_k ] > - 1 ) {
								$comment_args[ $comment_arg_k ] = $item[ $index[ $comment_arg_k ] ];
							}
						}
						if ( $index['rating'] < 0 ) {
							continue;
						}
						if ( empty( $comment_args['comment_post_ID'] ) || empty( $comment_args['comment_author'] ) ) {
							continue;
						}
						$rating = intval( $item[ $index['rating'] ] );
						if ( $rating < 1 || $rating > 5 ) {
							continue;
						}
						$comment_args['comment_type'] = 'review';
						$comment_args['comment_meta'] = array( 'rating' => $rating );
						if ($search_id_by_slug){
							$product_id = $this->get_woo_product_id( $comment_args['comment_post_ID'], 'slug');
                        } elseif ( $search_id_by_sku ) {
							$product_id = wc_get_product_id_by_sku( $comment_args['comment_post_ID'] );
						} else {
							$product_id = $this->get_woo_product_id( $comment_args['comment_post_ID'] );
						}

						if ( $product_id ) {
							if ( 'product' !== get_post_type( $product_id ) ) {
								continue;
							}
							$comment_args['comment_post_ID'] = $product_id;
							$comment_status                  = strtolower( $comment_args['comment_approved'] );

							if ( $comment_status === 'pending' || $comment_status === 'false' ) {
								$comment_args['comment_approved'] = '0';
							} elseif ( $comment_status === 'rejected' ) {
								$comment_args['comment_approved'] = 'spam';
							} else {
								$comment_args['comment_approved'] = '1';
							}


							if ( empty( $comment_args['user_id'] ) && ! empty( $comment_args['comment_author_email'] ) ) {
								$user = get_user_by( 'email', $comment_args['comment_author_email'] );
								if ( false !== $user ) {
									$comment_args['user_id'] = $user->ID;
								}
							}
							if ( ! empty( $comment_args['comment_date'] ) ) {
								$date = DateTime::createFromFormat( $import_csv_date_format, $comment_args['comment_date'] );
								if ( $date ) {
									$comment_args['comment_date'] = $date->format( 'Y-m-d H:i:s' );
									if ( ! empty( $comment_args['comment_date_gmt'] ) ) {
										$date = DateTime::createFromFormat( $import_csv_date_format, $comment_args['comment_date_gmt'] );
										if ( $date ) {
											$comment_args['comment_date_gmt'] = $date->format( 'Y-m-d H:i:s' );
										}
									} else {
										$comment_args['comment_date_gmt'] = date( 'Y-m-d H:i:s', ( strtotime( $comment_args['comment_date'] ) - $gmt_offset * 3600 ) );
									}
								}

							} elseif ( ! empty( $comment_args['comment_date_gmt'] ) ) {
								$date = DateTime::createFromFormat( $import_csv_date_format, $comment_args['comment_date_gmt'] );
								if ( $date ) {
									$comment_args['comment_date_gmt'] = $date->format( 'Y-m-d H:i:s' );
									$comment_args['comment_date']     = date( 'Y-m-d H:i:s', ( strtotime( $comment_args['comment_date_gmt'] ) + $gmt_offset * 3600 ) );
								}
							}
							if ( ! empty( $comment_args['comment_author_email'] ) ) {
								$comment_count_args = array(
									'author_email' => $comment_args['comment_author_email'],
									'type'         => 'review',
									'post_id'      => $product_id,
									'count'        => true,
									'meta_query'   => array(
										'relation' => 'AND',
										array(
											'key'     => 'rating',
											'value'   => $rating,
											'compare' => '=',
										)
									),
								);
								if ( get_comments( $comment_count_args ) ) {
									continue;
								}
							}
							$comment_id = $this->insert_comment( $comment_args );

							if ( $comment_id ) {
								if ( - 1 < $index['verified'] ) {
									$verified = $item[ $index['verified'] ];
									if ( $verified && strtolower( $verified ) !== 'anonymous' ) {
										update_comment_meta( $comment_id, 'verified', 1 );
									}
								}
								if ( - 1 < $index['wcpr_review_title'] ) {
									$review_title = $item[ $index['wcpr_review_title'] ];
									if ( $review_title ) {
										update_comment_meta( $comment_id, 'wcpr_review_title', $review_title );
									}
								}
								if ( - 1 < $index['reviews-images'] ) {
									$reviews_images = $item[ $index['reviews-images'] ];
									if ( $reviews_images ) {
										update_comment_meta( $comment_id, 'reviews-images', explode( ',', $reviews_images ) );
										$images = array( 'comment_id' => $comment_id );
										self::$background_process->push_to_queue( $images );
										self::$background_process->save()->dispatch();
									}
								}
								if ( - 1 < $index['wcpr_custom_fields'] ) {
									$optional_fields    = $item[ $index['wcpr_custom_fields'] ];
									$variations         = explode( '/', $optional_fields );
									$custom_fields_data = array();
									foreach ( $variations as $variation ) {
										if ( $variation ) {
											$custom_fields = explode( ':', $variation );
											if ( count( $custom_fields ) == 2 ) {
												$custom_fields_data[] = array(
													'name'  => $custom_fields[0],
													'value' => $custom_fields[1],
													'unit'  => '',
												);
											} elseif ( count( $custom_fields ) == 3 ) {
												$custom_fields_data[] = array(
													'name'  => $custom_fields[0],
													'value' => $custom_fields[1],
													'unit'  => $custom_fields[2],
												);
											}
										}
									}
									if ( count( $custom_fields_data ) ) {
										update_comment_meta( $comment_id, 'wcpr_custom_fields', $custom_fields_data );
									}
								}
								if ( - 1 < $index['wcpr_vote_up'] ) {
									$upVoteCount = intval( $item[ $index['wcpr_vote_up'] ] );
									if ( $upVoteCount > 0 ) {
										update_comment_meta( $comment_id, 'wcpr_vote_up_count', $upVoteCount );
									}
								}
								if ( - 1 < $index['wcpr_vote_down'] ) {
									$downVoteCount = intval( $item[ $index['wcpr_vote_down'] ] );
									if ( $downVoteCount > 0 ) {
										update_comment_meta( $comment_id, 'wcpr_vote_down_count', $downVoteCount );
									}
								}
								$imported ++;
								if ( $comment_args['comment_approved'] == 1 ) {
									self::update_product_reviews_and_rating( $product_id, $rating );
								}
							}

						}
						if ( $count >= $reviews_per_request ) {
							fclose( $file_handle );
							wp_send_json( array(
								'status'   => 'success',
								'imported' => $imported,
								'start'    => $start,
								'ftell'    => $ftell,
								'percent'  => intval( 100 * ( $start ) / $total ),
							) );
						}
					}
				} else {
					while ( ( $item = fgetcsv( $file_handle, 0, "," ) ) !== false ) {
						$count ++;
						$start ++;
						$ftell        = ftell( $file_handle );
						$comment_args = array(
							'comment_post_ID'      => '',
							'comment_author'       => '',
							'comment_author_email' => '',
							'comment_author_url'   => '',
							'comment_content'      => '',
							'comment_approved'     => '1',
							'comment_parent'       => '',
							'user_id'              => '',
							'comment_author_IP'    => '',
							'comment_agent'        => '',
							'comment_date'         => '',
							'comment_date_gmt'     => '',
						);
						foreach ( $comment_args as $comment_arg_k => $comment_arg_v ) {
							if ( $index[ $comment_arg_k ] > - 1 ) {
								$comment_args[ $comment_arg_k ] = $item[ $index[ $comment_arg_k ] ];
							}
						}
						if ( $index['rating'] < 0 ) {
							continue;
						}
						if ( empty( $comment_args['comment_post_ID'] ) || empty( $comment_args['comment_author'] ) ) {
							continue;
						}
						$rating = intval( $item[ $index['rating'] ] );
						if ( $rating < 1 || $rating > 5 ) {
							continue;
						}
						$comment_args['comment_type'] = 'review';
						$comment_args['comment_meta'] = array( 'rating' => $rating );
						if ($search_id_by_slug){
							$product_id = $this->get_woo_product_id( $comment_args['comment_post_ID'], 'slug');
						} elseif ( $search_id_by_sku ) {
							$product_id = wc_get_product_id_by_sku( $comment_args['comment_post_ID'] );
							if ( ! $product_id ) {
								continue;
							} else {
								$comment_args['comment_post_ID'] = $product_id;
							}
						} else {
							$product_id = $comment_args['comment_post_ID'];
						}

						if ( 'product' != get_post_type( $product_id ) ) {
							continue;
						}
						$comment_status = strtolower( $comment_args['comment_approved'] );
						if ( empty( $comment_status ) ) {
							$comment_args['comment_approved'] = '0';
						} else {
							if ( $comment_status === 'spam' ) {
								$comment_args['comment_approved'] = 'spam';
							} elseif ( $comment_status === 'trash' ) {
								$comment_args['comment_approved'] = 'trash';
							} elseif ( $comment_status === 'approve' || $comment_status === '1' || $comment_status === 'true' ) {
								$comment_args['comment_approved'] = '1';
							} else {
								$comment_args['comment_approved'] = '0';
							}
						}
						if ( empty( $comment_args['user_id'] ) && ! empty( $comment_args['comment_author_email'] ) ) {
							$user = get_user_by( 'email', $comment_args['comment_author_email'] );
							if ( false !== $user ) {
								$comment_args['user_id'] = $user->ID;
							}
						}
						if ( ! empty( $comment_args['comment_date'] ) ) {
							$date = DateTime::createFromFormat( $import_csv_date_format, $comment_args['comment_date'] );
							if ( $date ) {
								$comment_args['comment_date'] = $date->format( 'Y-m-d H:i:s' );
								if ( ! empty( $comment_args['comment_date_gmt'] ) ) {
									$date = DateTime::createFromFormat( $import_csv_date_format, $comment_args['comment_date_gmt'] );
									if ( $date ) {
										$comment_args['comment_date_gmt'] = $date->format( 'Y-m-d H:i:s' );
									}
								} else {
									$comment_args['comment_date_gmt'] = date( 'Y-m-d H:i:s', ( strtotime( $comment_args['comment_date'] ) - $gmt_offset * 3600 ) );
								}
							}
						} elseif ( ! empty( $comment_args['comment_date_gmt'] ) ) {
							$date = DateTime::createFromFormat( $import_csv_date_format, $comment_args['comment_date_gmt'] );
							if ( $date ) {
								$comment_args['comment_date_gmt'] = $date->format( 'Y-m-d H:i:s' );
								$comment_args['comment_date']     = date( 'Y-m-d H:i:s', ( strtotime( $comment_args['comment_date_gmt'] ) + $gmt_offset * 3600 ) );
							}
						}
						if ( ! empty( $comment_args['comment_author_email'] ) ) {
							$comment_count_args = array(
								'author_email' => $comment_args['comment_author_email'],
								'type'         => 'review',
								'post_id'      => $product_id,
								'count'        => true,
								'meta_query'   => array(
									'relation' => 'AND',
									array(
										'key'     => 'rating',
										'value'   => $rating,
										'compare' => '=',
									)
								),
							);
							if ( get_comments( $comment_count_args ) ) {
								continue;
							}
						}

						$comment_id = $this->insert_comment( $comment_args );

						if ( $comment_id ) {
							if ( - 1 < $index['verified'] ) {
								$verified = $item[ $index['verified'] ];
								if ( $verified && strtolower( $verified ) !== 'anonymous' ) {
									update_comment_meta( $comment_id, 'verified', 1 );
								}
							}
							if ( - 1 < $index['wcpr_review_title'] ) {
								$review_title = $item[ $index['wcpr_review_title'] ];
								if ( $review_title ) {
									update_comment_meta( $comment_id, 'wcpr_review_title', $review_title );
								}
							}
							if ( - 1 < $index['reviews-images'] ) {
								$reviews_images = $item[ $index['reviews-images'] ];
								if ( $reviews_images ) {
									update_comment_meta( $comment_id, 'reviews-images', explode( ',', $reviews_images ) );
									$images = array( 'comment_id' => $comment_id );
									self::$background_process->push_to_queue( $images );
									self::$background_process->save()->dispatch();
								}
							}
							if ( - 1 < $index['wcpr_vote_up'] ) {
								$upVoteCount = intval( $item[ $index['wcpr_vote_up'] ] );
								if ( $upVoteCount > 0 ) {
									update_comment_meta( $comment_id, 'wcpr_vote_up_count', $upVoteCount );
								}
							}
							if ( - 1 < $index['wcpr_vote_down'] ) {
								$downVoteCount = intval( $item[ $index['wcpr_vote_down'] ] );
								if ( $downVoteCount > 0 ) {
									update_comment_meta( $comment_id, 'wcpr_vote_down_count', $downVoteCount );
								}
							}
							if ( - 1 < $index['wcpr_custom_fields'] ) {
								$optional_fields    = $item[ $index['wcpr_custom_fields'] ];
								$variations         = explode( '/', $optional_fields );
								$custom_fields_data = array();
								foreach ( $variations as $variation ) {
									if ( $variation ) {
										$custom_fields = explode( ':', $variation );
										if ( count( $custom_fields ) === 2 ) {
											$custom_fields_data[] = array(
												'name'  => $custom_fields[0],
												'value' => $custom_fields[1],
												'unit'  => '',
											);
										} elseif ( count( $custom_fields ) === 3 ) {
											$custom_fields_data[] = array(
												'name'  => $custom_fields[0],
												'value' => $custom_fields[1],
												'unit'  => $custom_fields[2],
											);
										}
									}
								}
								if ( count( $custom_fields_data ) ) {
									update_comment_meta( $comment_id, 'wcpr_custom_fields', $custom_fields_data );
								}
							}
							$imported ++;
							if ( $comment_args['comment_approved'] == 1 ) {
								self::update_product_reviews_and_rating( $product_id, $rating );
							}
						}
						unset( $item );
						if ( $count >= $reviews_per_request ) {
							fclose( $file_handle );
							wp_send_json( array(
								'status'   => 'success',
								'imported' => $imported,
								'start'    => $start,
								'ftell'    => $ftell,
								'percent'  => intval( 100 * ( $start ) / $total ),
							) );
						}
					}
				}
				fclose( $file_handle );
				wp_send_json( array(
					'status'   => 'finish',
					'imported' => $imported,
					'start'    => $start,
					'ftell'    => $ftell,
					'percent'  => intval( 100 * ( $start ) / $total ),
				) );

			} else {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => esc_html__( 'Invalid file.', 'woocommerce-photo-reviews' ),
					)
				);
			}
		} else {
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Invalid file.', 'woocommerce-photo-reviews' ),
				)
			);
		}
	}

	public static function update_product_reviews_and_rating( $post_id, $rating ) {
		$review_count = get_post_meta( $post_id, '_wc_review_count', true );
		$rating_count = get_post_meta( $post_id, '_wc_rating_count', true );
		if ( ! is_array( $rating_count ) ) {
			$rating_count = array();
		}
		if ( $review_count != array_sum( $rating_count ) ) {

			if ( ! isset( $rating_count[ $rating ] ) ) {
				$rating_count[ $rating ] = 1;
			} else {
				$rating_count[ $rating ] += 1;
			}
			update_post_meta( $post_id, '_wc_rating_count', $rating_count );
			$sum = 0;
			foreach ( $rating_count as $key => $value ) {
				$sum += $key * $value;
			}
			$ave_rating = round( $sum / $review_count, 1 );
			update_post_meta( $post_id, '_wc_average_rating', $ave_rating );
		}
	}

	public function insert_comment( $commentdata ) {
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

	public function admin_enqueue_scripts() {
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
		wp_enqueue_script( 'wcpr-semantic-js-progress', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'progress.min.js', array( 'jquery' ) );
		wp_enqueue_style( 'wcpr-semantic-css-progress', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'progress.min.css' );
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
		wp_enqueue_style( 'wcpr-semantic-step-css', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'step.min.css' );
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
		wp_enqueue_script( 'woocommerce-photo-reviews-import', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'import.js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		wp_enqueue_style( 'woocommerce-photo-reviews-import', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'import.css', '', VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		wp_localize_script( 'woocommerce-photo-reviews-import', 'woocommerce_photo_reviews_import_params', array(
			'url'                    => admin_url( 'admin-ajax.php' ),
			'step'                   => $this->step,
			'file_url'               => $this->file_url,
			'nonce'                  => isset( $_POST['_woocommerce_photo_reviews_import_nonce'] ) ? sanitize_text_field( $_POST['_woocommerce_photo_reviews_import_nonce'] ) : '',
			'import_from_loox'       => isset( $_POST['wcpr_import_from_loox'] ) ? sanitize_text_field( $_POST['wcpr_import_from_loox'] ) : '',
			'search_id_by_sku'       => isset( $_POST['wcpr_search_id_by_sku'] ) ? sanitize_text_field( $_POST['wcpr_search_id_by_sku'] ) : '',
			'search_id_by_slug'       => isset( $_POST['wcpr_search_id_by_slug'] ) ? sanitize_text_field( $_POST['wcpr_search_id_by_slug'] ) : '',
			'wcpr_index'             => $this->index,
			'reviews_per_request'    => isset( $_POST['wcpr_reviews_per_request'] ) ? sanitize_text_field( $_POST['wcpr_reviews_per_request'] ) : '',
			'custom_start'           => isset( $_POST['wcpr_custom_start'] ) ? sanitize_text_field( $_POST['wcpr_custom_start'] ) : 1,
			'import_csv_date_format' => isset( $_POST['wcpr_import_csv_date_format'] ) ? sanitize_text_field( stripslashes( $_POST['wcpr_import_csv_date_format'] ) ) : '',
			'required_fields'        => array(
				'comment_post_ID' => 'Product ID',
				'comment_author'  => 'Author name',
				'rating'          => 'Rating',
				'comment_content' => 'Content',
			),
		) );
	}

	public function import_csv_callback() {
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'Import Product From CSV file', 'woocommerce-photo-reviews' ); ?></h2>
			<?php
			$steps_state = array(
				'start'   => '',
				'mapping' => '',
				'import'  => '',
			);
			if ( $this->step == 'mapping' ) {
				$steps_state['start']   = '';
				$steps_state['mapping'] = 'active';
				$steps_state['import']  = 'disabled';
			} elseif ( $this->step == 'import' ) {
				$steps_state['start']   = '';
				$steps_state['mapping'] = '';
				$steps_state['import']  = 'active';
			} else {
				$steps_state['start']   = 'active';
				$steps_state['mapping'] = 'disabled';
				$steps_state['import']  = 'disabled';
			}
			?>
            <div class="vi-ui segment">
                <div class="vi-ui steps fluid">
                    <div class="step <?php esc_attr_e( $steps_state['start'] ) ?>">
                        <i class="upload icon"></i>
                        <div class="content">
                            <div class="title"><?php esc_html_e( 'Select file', 'woocommerce-photo-reviews' ); ?></div>
                        </div>
                    </div>
                    <div class="step <?php esc_attr_e( $steps_state['mapping'] ) ?>">
                        <i class="exchange icon"></i>
                        <div class="content">
                            <div class="title"><?php esc_html_e( 'Settings & Mapping', 'woocommerce-photo-reviews' ); ?></div>
                        </div>
                    </div>
                    <div class="step <?php esc_attr_e( $steps_state['import'] ) ?>">
                        <i class="refresh icon"></i>
                        <div class="content">
                            <div class="title"><?php esc_html_e( 'Import', 'woocommerce-photo-reviews' ); ?></div>
                        </div>
                    </div>
                </div>
				<?php
				if ( isset( $_REQUEST['wcpr_error'] ) ) {
					$file_url = isset( $_REQUEST['file_url'] ) ? urldecode( $_REQUEST['file_url'] ) : '';
					?>
                    <div class="vi-ui negative message">
                        <div class="header">
							<?php
							switch ( $_REQUEST['wcpr_error'] ) {
								case 1:
									esc_html_e( 'Please set mapping for all required fields', 'woocommerce-photo-reviews' );
									break;
								case 2:
									if ( $file_url ) {
										_e( "Can not open file: <strong>{$file_url}</strong>", 'woocommerce-photo-reviews' );
									} else {
										esc_html_e( 'Can not open file', 'woocommerce-photo-reviews' );
									}
									break;
								default:
									if ( $file_url ) {
										_e( "File not exists: <strong>{$file_url}</strong>", 'woocommerce-photo-reviews' );
									} else {
										esc_html_e( 'File not exists', 'woocommerce-photo-reviews' );
									}
							}
							?>
                        </div>
                    </div>
					<?php
				}
				switch ( $this->step ) {
					case 'mapping':
						?>
                        <form class="<?php esc_attr_e( self::set( 'import-container-form' ) ) ?> vi-ui form"
                              method="post"
                              enctype="multipart/form-data"
                              action="<?php esc_attr_e( remove_query_arg( array(
							      'step',
							      'file_url',
							      'wcpr_error'
						      ) ) ) ?>">
							<?php
							wp_nonce_field( 'woocommerce_photo_reviews_import_action_nonce', '_woocommerce_photo_reviews_import_nonce' );
							if ( $this->error ) {
								?>
                                <div class="error">
									<?php
									echo $this->error;
									?>
                                </div>
								<?php
							}
							?>

                            <div class="vi-ui segment">
                                <table class="form-table">
                                    <tbody>
                                    <tr>
                                        <th>
                                            <label for="<?php esc_attr_e( self::set( 'reviews_per_request' ) ) ?>"><?php esc_html_e( 'Reviews per step', 'woocommerce-photo-reviews' ); ?></label>
                                        </th>
                                        <td>
                                            <input type="number"
                                                   class="<?php esc_attr_e( self::set( 'reviews_per_request' ) ) ?>"
                                                   id="<?php esc_attr_e( self::set( 'reviews_per_request' ) ) ?>"
                                                   name="<?php esc_attr_e( self::set( 'reviews_per_request', true ) ) ?>"
                                                   min="1"
                                                   value="<?php esc_attr_e( $this->settings->get_params( 'reviews_per_request' ) ) ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="<?php esc_attr_e( self::set( 'custom_start' ) ) ?>"><?php esc_html_e( 'Start line', 'woocommerce-photo-reviews' ); ?></label>
                                        </th>
                                        <td>
                                            <input type="number"
                                                   class="<?php esc_attr_e( self::set( 'custom_start' ) ) ?>"
                                                   id="<?php esc_attr_e( self::set( 'custom_start' ) ) ?>"
                                                   name="<?php esc_attr_e( self::set( 'custom_start', true ) ) ?>"
                                                   min="2"
                                                   value="2">
                                            <p class="description"><?php esc_html_e( 'Only import products from this line on.', 'woocommerce-photo-reviews' ) ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="<?php esc_attr_e( self::set( 'search_id_by_sku' ) ) ?>"><?php esc_html_e( 'Use product SKU instead of ID', 'woocommerce-photo-reviews' ) ?></label>
                                        </th>
                                        <td>
                                            <div class="vi-ui toggle checkbox checked">
                                                <input type="checkbox" class="instead_of_id_by_other_field"
                                                       name="<?php esc_attr_e( self::set( 'search_id_by_sku', true ) ) ?>"
                                                       id="<?php esc_attr_e( self::set( 'search_id_by_sku' ) ) ?>"
                                                       value="1" <?php checked( $this->settings->get_params( 'search_id_by_sku' ), '1' ) ?>>
                                                <label for="<?php esc_attr_e( self::set( 'search_id_by_sku' ) ) ?>"></label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="<?php esc_attr_e( self::set( 'search_id_by_slug' ) ) ?>"><?php esc_html_e( 'Use product SLUG instead of ID', 'woocommerce-photo-reviews' ) ?></label>
                                        </th>
                                        <td>
                                            <div class="vi-ui toggle checkbox checked">
                                                <input type="checkbox"
                                                       class="instead_of_id_by_other_field"
                                                       name="<?php esc_attr_e( self::set( 'search_id_by_slug', true ) ) ?>"
                                                       id="<?php esc_attr_e( self::set( 'search_id_by_slug' ) ) ?>"
                                                       value="1" <?php checked( $this->settings->get_params( 'search_id_by_slug' ), '1' ) ?>>
                                                <label for="<?php esc_attr_e( self::set( 'search_id_by_slug' ) ) ?>"></label>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label for="<?php esc_attr_e( self::set( 'import_csv_date_format' ) ) ?>"><?php esc_html_e( 'Date format', 'woocommerce-photo-reviews' ); ?></label>
                                        </th>
                                        <td>
                                            <input type="text"
                                                   class="<?php esc_attr_e( self::set( 'import_csv_date_format' ) ) ?>"
                                                   id="<?php esc_attr_e( self::set( 'import_csv_date_format' ) ) ?>"
                                                   name="<?php esc_attr_e( self::set( 'import_csv_date_format', true ) ) ?>"
                                                   value="<?php esc_attr_e( $this->settings->get_params( 'import_csv_date_format' ) ) ?>">
                                            <p class="description"><?php esc_html_e( 'You have to enter correct date format of review date in your csv file to map it correctly', 'woocommerce-photo-reviews' ) ?></p>
                                            <p><?php printf( __( 'Use <strong>%s</strong> if your time is <strong>%s</strong> or <strong>%s</strong>', '' ), 'd/m/Y H:i', '31/12/2019 00:00', '31/12/2019 0:00' ) ?></p>
                                            <p><?php printf( __( 'Use <strong>%s</strong> if your time is <strong>%s</strong> or <strong>%s</strong>', '' ), 'm/d/Y H:i', '12/31/2019 00:00', '12/31/2019 0:00' ) ?></p>
                                            <p><?php printf( __( 'Use <strong>%s</strong> if your time is <strong>%s</strong>', '' ), 'F d, Y H:i:s', 'Dec 31, 2019 00:00:00' ) ?></p>
                                            <p><?php printf( __( 'Use <strong>%s</strong> if your time is <strong>%s</strong>', '' ), 'Y-m-d H:i:s', '2019-12-31 00:00:00' ) ?></p>
                                            <p><a href="https://wordpress.org/support/article/formatting-date-and-time/"
                                                  target="_blank"><?php esc_html_e( 'Documentation on date and time formatting.', 'woocommerce-photo-reviews' ); ?></a>
                                            </p>
                                        </td>
                                    </tr>
									<?php
									if ( is_plugin_active( 's2w-import-shopify-to-woocommerce/s2w-import-shopify-to-woocommerce.php' ) || is_plugin_active( 'import-shopify-to-woocommerce/import-shopify-to-woocommerce.php' ) ) {
										?>
                                        <tr>
                                            <th>
                                                <label for="<?php esc_attr_e( self::set( 'import_from_loox' ) ) ?>"><?php esc_html_e( 'Import from Shopify', 'woocommerce-photo-reviews' ) ?></label>
                                            </th>
                                            <td>
                                                <div class="vi-ui toggle checkbox checked">
                                                    <input type="checkbox"
                                                           name="<?php esc_attr_e( self::set( 'import_from_loox', true ) ) ?>"
                                                           id="<?php esc_attr_e( self::set( 'import_from_loox' ) ) ?>"
                                                           value="1" <?php checked( $this->settings->get_params( 'import_from_loox' ), '1' ) ?>>
                                                    <label for="<?php esc_attr_e( self::set( 'import_from_loox' ) ) ?>"><?php esc_html_e( 'Enable this if you\'re about to import reviews from Shopify', 'woocommerce-photo-reviews' ) ?></label>
                                                </div>
                                            </td>
                                        </tr>
										<?php
									}
									?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="vi-ui segment">
                                <table class="form-table">
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Column name', 'woocommerce-photo-reviews' ) ?></th>
                                        <th><?php esc_html_e( 'Map to field', 'woocommerce-photo-reviews' ) ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
									<?php
									$required_fields = array(
										'comment_post_ID',
										'comment_author',
										'rating',
										'comment_content',
									);
									$headers         = array(
										'comment_post_ID'      => esc_html__( 'Product ID', 'woocommerce-photo-reviews' ),
										'comment_author'       => esc_html__( 'Author name', 'woocommerce-photo-reviews' ),
										'comment_author_email' => esc_html__( 'Author email', 'woocommerce-photo-reviews' ),
										'comment_author_url'   => esc_html__( 'Author URL', 'woocommerce-photo-reviews' ),
										'wcpr_review_title'    => esc_html__( 'Review title', 'woocommerce-photo-reviews' ),
										'comment_content'      => esc_html__( 'Content', 'woocommerce-photo-reviews' ),
										'comment_approved'     => esc_html__( 'Comment status', 'woocommerce-photo-reviews' ),
										'rating'               => esc_html__( 'Rating', 'woocommerce-photo-reviews' ),
										'verified'             => esc_html__( 'Verified', 'woocommerce-photo-reviews' ),
										'reviews-images'       => esc_html__( 'Photos', 'woocommerce-photo-reviews' ),
										'wcpr_custom_fields'   => esc_html__( 'Optional fields/Variations', 'woocommerce-photo-reviews' ),
										'wcpr_vote_up'         => esc_html__( 'Up-vote count', 'woocommerce-photo-reviews' ),
										'wcpr_vote_down'       => esc_html__( 'Down-vote count', 'woocommerce-photo-reviews' ),
										'comment_parent'       => esc_html__( 'Comment parent', 'woocommerce-photo-reviews' ),
										'user_id'              => esc_html__( 'User id', 'woocommerce-photo-reviews' ),
										'comment_author_IP'    => esc_html__( 'Author IP', 'woocommerce-photo-reviews' ),
										'comment_agent'        => esc_html__( 'Comment agent', 'woocommerce-photo-reviews' ),
										'comment_date'         => esc_html__( 'Comment date', 'woocommerce-photo-reviews' ),
										'comment_date_gmt'     => esc_html__( 'Comment date gmt', 'woocommerce-photo-reviews' ),
									);
									$selecteds       = array(
										'comment_post_ID',
										'comment_author',
										'comment_author_email',
										'comment_content',
										'comment_approved',
										'rating',
										'verified',
										'reviews-images',
										'wcpr_custom_fields',
										'wcpr_review_title',
										'comment_date',
									);
									foreach ( $headers as $header_k => $header_v ) {
										if ( in_array( $header_k, $selecteds ) ) {
											?>
                                            <tr>
                                                <td>
                                                    <select id="<?php esc_attr_e( self::set( $header_k ) ) ?>"
                                                            class="vi-ui fluid dropdown"
                                                            name="<?php echo self::set( 'map_to', true ) ?>[<?php echo $header_k ?>]">
                                                        <option value=""><?php esc_html_e( 'Do not import', 'woocommerce-photo-reviews' ) ?></option>
														<?php
														foreach ( $this->header as $file_header ) {
															?>
                                                            <option value="<?php echo $file_header ?>"<?php selected( $header_v, $file_header ) ?>><?php echo $file_header ?></option>
															<?php
														}
														?>
                                                    </select>
                                                </td>
                                                <td>
													<?php
													$label = $header_v;
													if ( in_array( $header_k, $required_fields ) ) {
														$label .= '(*Required)';
													}
													?>
                                                    <label for="<?php esc_attr_e( self::set( $header_k ) ) ?>"><?php esc_html_e( $label ); ?></label>
                                                </td>
                                            </tr>
											<?php
										} else {
											?>
                                            <tr>
                                                <td>
                                                    <select id="<?php esc_attr_e( self::set( $header_k ) ) ?>"
                                                            class="vi-ui fluid dropdown"
                                                            name="<?php echo self::set( 'map_to', true ) ?>[<?php echo $header_k ?>]">
                                                        <option value=""><?php esc_html_e( 'Do not import', 'woocommerce-photo-reviews' ) ?></option>
														<?php
														foreach ( $this->header as $file_header ) {
															?>
                                                            <option value="<?php echo $file_header ?>"><?php echo $file_header ?></option>
															<?php
														}
														?>
                                                    </select>
                                                </td>
                                                <td>
													<?php
													$label = $header_v;
													?>
                                                    <label for="<?php esc_attr_e( self::set( $header_k ) ) ?>"><?php esc_html_e( $label ); ?></label>
                                                </td>
                                            </tr>
											<?php
										}
									}
									?>
                                    </tbody>
                                </table>
                            </div>
                            <input type="hidden" name="woocommerce_photo_reviews_file_url"
                                   value="<?php esc_attr_e( stripslashes( $this->file_url ) ) ?>">
                            <p>
                                <input type="submit" name="woocommerce_photo_reviews_import"
                                       class="vi-ui primary button <?php esc_attr_e( self::set( 'import-continue' ) ) ?>"
                                       value="<?php esc_attr_e( 'Import', 'woocommerce-photo-reviews' ); ?>">
                            </p>
                        </form>
						<?php
						break;
					case 'import':
						?>
                        <div>
                            <div class="vi-ui indicating progress standard <?php esc_attr_e( self::set( 'import-progress' ) ) ?>">
                                <div class="label"></div>
                                <div class="bar">
                                    <div class="progress"></div>
                                </div>
                            </div>
                        </div>
						<?php
						break;
					default:
						?>
                        <form class="<?php esc_attr_e( self::set( 'import-container-form' ) ) ?> vi-ui form"
                              method="post"
                              enctype="multipart/form-data">
							<?php
							wp_nonce_field( 'woocommerce_photo_reviews_import_action_nonce', '_woocommerce_photo_reviews_import_nonce' );
							if ( $this->error ) {
								?>
                                <div class="error">
									<?php
									echo $this->error;
									?>
                                </div>
								<?php
							}
							?>
                            <div class="<?php esc_attr_e( self::set( 'import-container' ) ) ?>">
                                <label for="<?php esc_attr_e( self::set( 'import-file' ) ) ?>"><?php _e( 'Select csv file to import', 'woocommerce-photo-reviews' ); ?></label>
                                <div>
                                    <input type="file" name="woocommerce_photo_reviews_file"
                                           id="<?php esc_attr_e( self::set( 'import-file' ) ) ?>"
                                           class="<?php esc_attr_e( self::set( 'import-file' ) ) ?>"
                                           accept=".csv"
                                           required>
                                </div>
                            </div>
                            <p><input type="submit" name="woocommerce_photo_reviews_select_file"
                                      class="vi-ui primary button <?php esc_attr_e( self::set( 'import-continue' ) ) ?>"
                                      value="<?php esc_attr_e( 'Continue', 'woocommerce-photo-reviews' ); ?>">
                            </p>
                        </form>
					<?php
				}
				?>
            </div>
        </div>
		<?php
	}
}
