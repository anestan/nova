<?php

/**
 * Class VI_WooCommerce_Photo_Reviews_Frontend_Reviews
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Reviews_Form {
	protected $settings;

	public function __construct() {
		$this->settings = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
		if ( 'on' == $this->settings->get_params( 'enable' ) ) {
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
			add_action( 'elementor/frontend/after_enqueue_scripts', array( $this, 'wp_enqueue_scripts_elementor' ) );
		}
	}

	public function init() {
		add_shortcode( 'woocommerce_photo_reviews_form', array( $this, 'reviews_form' ) );
	}

	public function wp_enqueue_scripts_elementor() {
		if ( ! wp_script_is( 'woocommerce-photo-reviews-form' ) ) {
			$suffix = WP_DEBUG ? '' : 'min.';
			wp_enqueue_style( 'woocommerce-photo-reviews-form', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'shortcode-review-form.'.$suffix.'css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_enqueue_script( 'woocommerce-photo-reviews-form', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'shortcode-review-form.'.$suffix.'js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_localize_script( 'woocommerce-photo-reviews-form', 'woocommerce_photo_reviews_form_params', array(
				'i18n_required_rating_text'  => esc_attr__( 'Please select a rating', 'woocommerce-photo-reviews' ),
				'i18n_required_comment_text' => esc_attr__( 'Please enter your comment', 'woocommerce-photo-reviews' ),
				'i18n_required_name_text'    => esc_attr__( 'Please enter your name', 'woocommerce-photo-reviews' ),
				'i18n_required_email_text'   => esc_attr__( 'Please enter your email', 'woocommerce-photo-reviews' ),
				'i18n_image_caption'         => esc_attr__( 'Caption for this image', 'woocommerce-photo-reviews' ),
				'review_rating_required'     => wc_review_ratings_required() ? 'yes' : 'no',
				'required_image'             => $this->settings->get_params( 'photo', 'required' ),
				'enable_photo'               => $this->settings->get_params( 'photo', 'enable' ),
				'warning_required_image'     => esc_html__( 'Please upload at least one image for your review!', 'woocommerce-photo-reviews' ),
				'warning_gdpr'               => esc_html__( 'Please agree with our term and policy.', 'woocommerce-photo-reviews' ),
				'max_files'                  => $this->settings->get_params( 'photo', 'maxfiles' ),
				'warning_max_files'          => sprintf( _n( 'You can only upload maximum of %s file', 'You can only upload maximum of %s files', $this->settings->get_params( 'photo', 'maxfiles' ), 'woocommerce-photo-reviews' ), $this->settings->get_params( 'photo', 'maxfiles' ) ),
				'allow_empty_comment'        => $this->settings->get_params( 'allow_empty_comment' ),
				'image_caption_enable'       => $this->settings->get_params( 'image_caption_enable' ),
			) );

		}
	}

	public function wp_enqueue_scripts() {
		if ( ! wp_script_is( 'woocommerce-photo-reviews-form', 'registered' ) ) {
			$suffix = WP_DEBUG ? '' : 'min.';
			wp_register_style( 'woocommerce-photo-reviews-form', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'shortcode-review-form.'.$suffix.'css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_register_script( 'woocommerce-photo-reviews-form', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'shortcode-review-form.'.$suffix.'js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		}
	}

	public function reviews_form( $atts ) {
		global $wcpr_review_form;
		$arr = shortcode_atts( array(
			'product_id'           => '',
			'hide_product_details' => '',
			'hide_product_price'   => '',
			'type'                 => '',
			'button_position'      => 'center',
		), $atts );

		$wcpr_review_form = true;
		if ( ! wp_script_is( 'woocommerce-photo-reviews-form' ) ) {
			$suffix = WP_DEBUG ? '' : 'min.';
			wp_enqueue_style( 'woocommerce-photo-reviews-form', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'shortcode-review-form.'.$suffix.'css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_enqueue_script( 'woocommerce-photo-reviews-form', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'shortcode-review-form.'.$suffix.'js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_localize_script( 'woocommerce-photo-reviews-form', 'woocommerce_photo_reviews_form_params', array(
				'i18n_required_rating_text'  => esc_attr__( 'Please select a rating', 'woocommerce-photo-reviews' ),
				'i18n_required_comment_text' => esc_attr__( 'Please enter your comment', 'woocommerce-photo-reviews' ),
				'i18n_required_name_text'    => esc_attr__( 'Please enter your name', 'woocommerce-photo-reviews' ),
				'i18n_required_email_text'   => esc_attr__( 'Please enter your email', 'woocommerce-photo-reviews' ),
				'i18n_image_caption'         => esc_attr__( 'Caption for this image', 'woocommerce-photo-reviews' ),
				'review_rating_required'     => wc_review_ratings_required() ? 'yes' : 'no',
				'required_image'             => $this->settings->get_params( 'photo', 'required' ),
				'enable_photo'               => $this->settings->get_params( 'photo', 'enable' ),
				'warning_required_image'     => esc_html__( 'Please upload at least one image for your review!', 'woocommerce-photo-reviews' ),
				'warning_gdpr'               => esc_html__( 'Please agree with our term and policy.', 'woocommerce-photo-reviews' ),
				'max_files'                  => $this->settings->get_params( 'photo', 'maxfiles' ),
				'warning_max_files'          => sprintf( _n( 'You can only upload maximum of %s file', 'You can only upload maximum of %s files', $this->settings->get_params( 'photo', 'maxfiles' ), 'woocommerce-photo-reviews' ), $this->settings->get_params( 'photo', 'maxfiles' ) ),
				'allow_empty_comment'        => $this->settings->get_params( 'allow_empty_comment' ),
				'image_caption_enable'       => $this->settings->get_params( 'image_caption_enable' ),
			) );
		}
		$css = ".woocommerce-photo-reviews-form-container.woocommerce-photo-reviews-form-popup .woocommerce-photo-reviews-form-button-add-review-container{text-align: {$arr['button_position']};}";
		wp_add_inline_style( 'woocommerce-photo-reviews-form', $css );
		$return= wc_get_template_html(
			'viwcpr-review-form-html.php',$arr,
			'woocommerce-photo-reviews' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
			WOOCOMMERCE_PHOTO_REVIEWS_TEMPLATES
        );
		$return           = str_replace( '<form', '<form enctype="multipart/form-data"', $return );
		$wcpr_review_form = false;
		return $return;
	}
}
