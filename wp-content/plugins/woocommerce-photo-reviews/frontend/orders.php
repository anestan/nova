<?php

/**
 * Class VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Orders
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Orders {
	protected $settings;

	public function __construct() {
		$this->settings = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
		if ( 'on' === $this->settings->get_params( 'enable' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
			add_filter( 'woocommerce_account_orders_columns', array( $this, 'woocommerce_account_orders_columns' ) );
			add_action( 'woocommerce_my_account_my_orders_column_wcpr_reviews', array(
				$this,
				'add_track_button_on_my_account'
			) );
		}
	}

	public function wp_enqueue_scripts() {
		if ( is_account_page() ) {
			wp_enqueue_style( 'woocommerce-photo-reviews-frontend-orders', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'orders.css', '', VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		}
	}

	public function woocommerce_account_orders_columns( $columns ) {
		$columns['wcpr_reviews'] = __( 'Reviews', 'woocommerce-photo-reviews' );

		return $columns;
	}

	/**
	 * @param $order WC_Order
	 *
	 * @throws Exception
	 */
	public function add_track_button_on_my_account( $order ) {
		if ( is_a( $order, 'WC_Order' ) ) {
			$my_account_order_statuses = $this->settings->get_params( 'my_account_order_statuses' );
			if ( in_array( 'wc-' . $order->get_status(), $my_account_order_statuses ) ) {
				$line_items       = array_values( $order->get_items( 'line_item' ) );
				$line_items_count = count( $line_items );
				if ( $line_items_count > 0 ) {
					$count       = 0;
					$anchor_link = '#' . $this->settings->get_params( 'reviews_anchor_link' );
					ob_start();
					foreach ( $line_items as $line_item ) {
						/**
						 * $line_item WC_Order_item
						 */
						$product_id = $line_item->get_product_id();
						$product    = wc_get_product( $product_id );
						if ( $product ) {
							$count ++;
							$review_link   = $product->get_permalink() . $anchor_link;
							$product_title = $product->get_title();
							?>
                            <a class="button wcpr-rate-button"
                               href="<?php echo esc_url( $review_link ) ?>"
                               target="_blank" title="<?php echo esc_attr( $product_title ); ?>"
                               rel="nofollow"><?php printf( esc_html__( 'Rate %s', 'woocommerce-photo-reviews' ), $product_title ) ?></a>
							<?php
						}
					}
					$review_button = ob_get_clean();
					if ( $count > 0 ) {
						?>
                        <div class="wcpr-rate-buttons-container">
                            <span class="woocommerce-button button view"><?php esc_html_e( 'Rate', 'woocommerce-photo-reviews' ) ?></span>
                            <span class="wcpr-rate-buttons">
                                <span class="wcpr-rate-button-container"><?php echo $review_button; ?></span>
                        </span>
                        </div>
						<?php
					}
				}
			}
		}
	}
}
