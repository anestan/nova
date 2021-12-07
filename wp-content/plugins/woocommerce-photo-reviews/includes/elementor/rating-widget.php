<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WCPR_Elementor_Rating_Widget extends Elementor\Widget_Base {

	public static $slug = 'wcpr-elementor-rating-widget';

	public function get_name() {
		return 'woocommerce-photo-reviews-rating';
	}

	public function get_title() {
		return esc_html__( 'Rating', 'woocommerce-photo-reviews' );
	}

	public function get_icon() {
		return 'fas fa-star-half-alt';
	}

	public function get_categories() {
		return [ 'woocommerce-elements' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'general',
			[
				'label' => esc_html__( 'Options', 'woocommerce-photo-reviews' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		if ( is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
			$this->add_control(
				'product_id',
				[
					'label'        => esc_html__( 'Product', 'woocommerce-photo-reviews' ),
					'type'         => 'query',
					'options'      => [],
					'label_block'  => true,
					'multiple'     => false,
					'autocomplete' => [
						'object' => 'post',
					],
				]
			);
		} else {
			$products = get_posts( array(
				'post_type'      => 'product',
				'post_status'    => VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::search_product_statuses(),
				'posts_per_page' => - 1,
			) );

			$options = [];
			foreach ( $products as $product ) {
				$options[ $product->ID ] = $product->post_title;
			}
			$this->add_control(
				'product_id',
				[
					'label'       => esc_html__( 'Product', 'woocommerce-photo-reviews' ),
					'type'        => \Elementor\Controls_Manager::SELECT2,
					'description' => esc_html__( 'Select product that you want to display the star rating for.', 'woocommerce-photo-reviews' ),
					'options'     => $options,
					'label_block' => true,
					'multiple'    => false,
				]
			);
		}
		$this->add_control(
			'review_count',
			[
				'label'                => esc_html__( 'Product Review Count', 'woocommerce-photo-reviews' ),
				'type'                 => \Elementor\Controls_Manager::SWITCHER,
				'default'              => 'on',
				'description'          => esc_html__( 'Show number of reviews of product', 'woocommerce-photo-reviews' ),
				'label_on'             => esc_html__( 'Yes', 'woocommerce-photo-reviews' ),
				'label_off'            => esc_html__( 'No', 'woocommerce-photo-reviews' ),
				'return_value'         => 'on',
				'selectors_dictionary' => [
					'on' => 'block',
					''   => 'none',
				],
				'selectors'            => [
					'{{WRAPPER}} .woocommerce-photo-reviews-review-count-container' => 'display:{{VALUE}};',
				],
			]
		);
		$this->add_control(
			'rating',
			[
				'label'                => esc_html__( 'Rating', 'woocommerce-photo-reviews' ),
				'type'                 => \Elementor\Controls_Manager::SELECT,
				'description'          => esc_html__( 'This option is used if you just only want to display the rating html of specific rating star(s)', 'woocommerce-photo-reviews' ),
				'default'              => '5',
				'options'              => [
					'1' => esc_html__( '1 Star', 'woocommerce-photo-reviews' ),
					'2' => esc_html__( '2 Stars', 'woocommerce-photo-reviews' ),
					'5' => esc_html__( '5 Stars', 'woocommerce-photo-reviews' ),
					'4' => esc_html__( '4 Stars', 'woocommerce-photo-reviews' ),
					'3' => esc_html__( '3 Stars', 'woocommerce-photo-reviews' ),
				],
				'label_block'          => false,
				'selectors_dictionary' => [
					'1' => '20',
					'2' => '40',
					'3' => '60',
					'4' => '80',
					'5' => '100',
				],
				'selectors'            => [
					'{{WRAPPER}} .woocommerce-photo-reviews-rating-html-shortcode .star-rating span' => 'width: {{VALUE}}% !important;',
				],
			]
		);
		$this->end_controls_section();
	}

	public function get_shortcode_text() {
		$settings = $this->get_settings_for_display();

		return "[wc_photo_reviews_rating_html product_id='{$settings['product_id']}' review_count='on' rating='{$settings['rating']}']";
	}

	protected function render() {

		echo do_shortcode( $this->get_shortcode_text() );
	}

	public function render_plain_content() {
		echo $this->get_shortcode_text();
	}
}