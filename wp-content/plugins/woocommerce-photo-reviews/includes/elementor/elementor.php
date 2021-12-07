<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
if ( ! is_plugin_active( 'elementor/elementor.php' ) ) {
	return;
}
add_action( 'elementor/widgets/widgets_registered', function () {
	if ( is_file( WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . 'elementor/reviews-widget.php' ) ) {
		require_once( 'reviews-widget.php' );
		$reviews_widget = new WCPR_Elementor_Reviews_Widget();
		Elementor\Plugin::instance()->widgets_manager->register_widget_type( $reviews_widget );
	}
	if ( is_file( WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . 'elementor/review-form-widget.php' ) ) {
		require_once( 'review-form-widget.php' );
		$review_form_widget = new WCPR_Elementor_Review_Form_Widget();
		Elementor\Plugin::instance()->widgets_manager->register_widget_type( $review_form_widget );
	}
	if ( is_file( WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . 'elementor/rating-widget.php' ) ) {
		require_once( 'rating-widget.php' );
		$rating_widget = new WCPR_Elementor_Rating_Widget();
		Elementor\Plugin::instance()->widgets_manager->register_widget_type( $rating_widget );
	}
	if ( is_file( WOOCOMMERCE_PHOTO_REVIEWS_INCLUDES . 'elementor/overall-rating-widget.php' ) ) {
		require_once( 'overall-rating-widget.php' );
		$rating_widget = new WCPR_Elementor_Overall_Rating_Widget();
		Elementor\Plugin::instance()->widgets_manager->register_widget_type( $rating_widget );
	}
} );