<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class WCPR_Elementor_Overall_Rating_Widget extends Elementor\Widget_Base {
	public static $slug = 'wcpr-elementor-overall-rating-widget';
	public function get_name() {
		return 'woocommerce-photo-reviews-overall-rating';
	}
	public function get_title() {
		return esc_html__( 'Overall Rating', 'woocommerce-photo-reviews' );
	}

	public function get_icon() {
		return 'fas fa-star-half-alt';
	}

	public function get_categories() {
		return [ 'woocommerce-elements' ];
	}
	protected function _register_controls() {
		$reviews_settings = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
		$this->start_controls_section(
			'general',
			[
				'label' => esc_html__( 'General', 'woocommerce-photo-reviews' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		if ( is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
			$this->add_control(
				'products',
				[
					'label'        => esc_html__( 'Products', 'woocommerce-photo-reviews' ),
					'type'         => 'query',
					'description'  => esc_html__( 'Display overall rating of which products?', 'woocommerce-photo-reviews' ),
					'options'      => [],
					'label_block'  => true,
					'multiple'     => true,
					'autocomplete' => [
						'object' => 'post',
					],
				]
			);
		} else {
			$products = get_posts( array(
				'post_type'   => 'product',
				'post_status' => VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::search_product_statuses(),
				'numberposts' => - 1,
			) );

			$options = [];
			foreach ( $products as $product ) {
				$options[ $product->ID ] = $product->post_title;
			}
			$this->add_control(
				'products',
				[
					'label'       => esc_html__( 'Products', 'woocommerce-photo-reviews' ),
					'type'        => \Elementor\Controls_Manager::SELECT2,
					'description' => esc_html__( 'Display overall rating of which products?', 'woocommerce-photo-reviews' ),
					'options'     => $options,
					'label_block' => true,
					'multiple'    => true,
				]
			);
		}
		$this->add_control(
			'style',
			[
				'label'   => esc_html__( 'Display Type', 'woocommerce-photo-reviews' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''  => esc_html__( 'Both', 'woocommerce-photo-reviews' ),
					'1' => esc_html__( 'Only Overall Rating', 'woocommerce-photo-reviews' ),
					'2' => esc_html__( 'Only Rating Count', 'woocommerce-photo-reviews' ),
				],
			]
		);

		$this->end_controls_section();

	}
	public function get_shortcode_text() {
		$settings        = $this->get_settings_for_display();
		$products        = $settings['products'] ? implode( ',', $settings['products'] ) : '';
		$style = $settings['style']??'';
		switch ($style){
			case '1':
				$overall_rating_enable = 'on';
				$rating_count_enable = 'off';
				break;
			case '2':
			$overall_rating_enable = 'off';
			$rating_count_enable = 'on';
				break;
			default:
				$overall_rating_enable = 'on';
				$rating_count_enable = 'on';
		}
		return "[wc_photo_reviews_overall_rating_html product_id='{$products}' overall_rating_enable='{$overall_rating_enable}' rating_count_enable='{$rating_count_enable}' is_shortcode='true']";
	}
	protected function render() {
		echo do_shortcode( $this->get_shortcode_text() );
	}

	public function render_plain_content() {
		echo $this->get_shortcode_text();
	}
}