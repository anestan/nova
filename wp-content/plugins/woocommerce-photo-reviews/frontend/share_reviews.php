<?php

/**
 * Class VI_WOOCOMMERCE_PHOTO_REVIEWS_Share_Reviews
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Share_Reviews {
	protected $settings;
	protected $comments;
	protected $quick_view;
	protected $frontend_style;
	protected $products;

	public function __construct() {
		if (is_admin()) {
			return;
		}
		$this->settings = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
		if ( 'off' !== $this->settings->get_params( 'enable' ) && count( $this->settings->get_params( 'share_reviews' ) ) ) {
			add_filter( 'woocommerce_product_get_rating_counts', array(
				$this,
				'woocommerce_product_get_rating_counts'
			), 10, 2 );
			add_filter( 'woocommerce_product_get_review_count', array(
				$this,
				'woocommerce_product_get_review_count'
			), 10, 2 );
			add_filter( 'woocommerce_product_get_average_rating', array(
				$this,
				'woocommerce_product_get_average_rating'
			), 10, 2 );
			add_filter( 'woocommerce_photo_reviews_get_comments', array(
				$this,
				'woocommerce_photo_reviews_get_comments'
			), 10, 2 );
			add_action( 'parse_comment_query', array( $this, 'parse_comment_query' ) );
			add_filter( 'woocommerce_product_review_comment_form_args', array(
				$this,
				'woocommerce_product_review_comment_form_args'
			) );
		}
	}

	/**
	 * @param $product_id
	 *
	 * @return array|null
	 */
	public function get_products( $product_id ) {
		if ( $this->products === null ) {
			$this->products = array();
			$products       = $this->settings->get_params( 'share_reviews' );
			foreach ( $products as $product_ids ) {
				if ( count( $product_ids ) > 1 && in_array( $product_id, $product_ids ) ) {
					$this->products = array_values( array_diff( $product_ids, array( $product_id ) ) );
					break;
				}
			}
		}

		return $this->products;
	}

	/**
	 * @param $comment_form
	 *
	 * @return mixed
	 */
	public function woocommerce_product_review_comment_form_args( $comment_form ) {
		global $product;
		if ( is_product() && $product ) {
			$product_id = $product->get_id();
			$products   = $this->get_products( $product_id );
			if ( count( $products ) && ! have_comments() ) {
				foreach ( $products as $key => $value ) {
					$pr = wc_get_product( $value );
					if ( $pr ) {
						if ( $pr->get_review_count( 'edit' ) ) {
							$comment_form['title_reply'] = esc_html__( 'Add a review', 'woocommerce' );
							break;
						}
					}
				}
			}
		}

		return $comment_form;
	}

	/**
	 * @param $vars
	 *
	 * @return mixed
	 */
	public function parse_comment_query( $vars ) {
		global $product;
		if ( is_product() && $product ) {
			$product_id = is_a($product, 'WC_Product') ? ($product->get_id()??''):'';
			$products   = $product_id ? $this->get_products( $product_id ): array();
			if ( count( $products ) ) {
				$vars->query_vars['post__in'] = array_merge( $products, array( $product_id ) );
				$vars->query_vars['post_id']  = '';
			}
		}

		return $vars;
	}

	/**
	 * @param $comments
	 * @param $args
	 *
	 * @return array|int
	 */
	public function woocommerce_photo_reviews_get_comments( $comments, $args ) {
		if ( is_product() ) {
			$product_id = isset( $args['post_id'] ) ? $args['post_id'] : '';
			$products   = $this->get_products( $product_id );
			if ( count( $products ) ) {
				$args['post__in'] = array_merge( $products, array( $product_id ) );
				unset( $args['post_id'] );
				$comments = get_comments( $args );
			}
		}

		return $comments;
	}

	/**
	 * @param $value
	 * @param $product WC_Product
	 *
	 * @return float|int
	 */
	public function woocommerce_product_get_average_rating( $value, $product ) {
		if ( is_product() && $product ) {
			$product_id = $product->get_id();
			$products   = $this->get_products( $product_id );
			if ( count( $products ) ) {
				$count          = $value ? 1 : 0;
				$average_rating = $value;
				foreach ( $products as $pr_id ) {
					$pr = wc_get_product( $pr_id );
					if ( $pr ) {
						$rating = $pr->get_average_rating( 'edit' );
						if ( $rating ) {
							$average_rating += $rating;
							$count ++;
						}
					}
				}
				if ( $count > 0 ) {
					$value = $average_rating / $count;
				}
			}
		}

		return $value;
	}

	/**
	 * @param $value
	 * @param $product WC_Product
	 *
	 * @return int
	 */
	public function woocommerce_product_get_review_count( $value, $product ) {
		if ( is_product() && $product ) {
			$product_id = $product->get_id();
			$products   = $this->get_products( $product_id );
			if ( count( $products ) ) {
				foreach ( $products as $pr_id ) {
					$pr = wc_get_product( $pr_id );
					if ( $pr ) {
						$value += $pr->get_review_count( 'edit' );
					}
				}
			}
		}

		return $value;
	}

	/**
	 * @param $value
	 * @param $product WC_Product
	 *
	 * @return int
	 */
	public function woocommerce_product_get_rating_counts( $value, $product ) {
		if ( is_product() && $product ) {
			$product_id = $product->get_id();
			$products   = $this->get_products( $product_id );
			if ( count( $products ) ) {
				foreach ( $products as $pr_id ) {
					$pr = wc_get_product( $pr_id );
					if ( $pr ) {
						$rating_counts = $pr->get_rating_counts( 'edit' );
						if ( $rating_counts ) {
							foreach ( $rating_counts as $rating => $rating_count ) {
								if ( ! isset( $value[ $rating ] ) ) {
									$value[ $rating ] = $rating_count;
								} else {
									$value[ $rating ] += $rating_count;
								}
							}
						}
					}
				}
			}
		}

		return $value;
	}
}
