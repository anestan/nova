<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WCPR_Elementor_Reviews_Widget extends Elementor\Widget_Base {

	public static $slug = 'wcpr-elementor-reviews-widget';

	public function get_name() {
		return 'woocommerce-photo-reviews';
	}

	public function get_title() {
		return esc_html__( 'Photo Reviews', 'woocommerce-photo-reviews' );
	}

	public function get_icon() {
		return 'fas fa-star';
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

		$this->add_control(
			'mobile',
			[
				'label'        => esc_html__( 'Display On Mobile', 'woocommerce-photo-reviews' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'on',
				'label_on'     => esc_html__( 'Yes', 'woocommerce-photo-reviews' ),
				'label_off'    => esc_html__( 'No', 'woocommerce-photo-reviews' ),
				'return_value' => 'on',
			]
		);
		$this->add_control(
			'use_single_product',
			[
				'label'        => esc_html__( 'For Single Product', 'woocommerce-photo-reviews' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => '',
				'description'  => esc_html__( 'If enabled, shortcode will show reviews of main product on single product page.', 'woocommerce-photo-reviews' ),
				'label_on'     => esc_html__( 'Yes', 'woocommerce-photo-reviews' ),
				'label_off'    => esc_html__( 'No', 'woocommerce-photo-reviews' ),
				'return_value' => 'on',
			]
		);
		if ( is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
			$this->add_control(
				'products',
				[
					'label'        => esc_html__( 'Products', 'woocommerce-photo-reviews' ),
					'type'         => 'query',
					'description'  => esc_html__( 'Display reviews of which products?', 'woocommerce-photo-reviews' ),
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
					'description' => esc_html__( 'Display reviews of which products?', 'woocommerce-photo-reviews' ),
					'options'     => $options,
					'label_block' => true,
					'multiple'    => true,
				]
			);
		}
		$categories = get_terms( 'product_cat' );

		$options = [];
		foreach ( $categories as $category ) {
			$options[ $category->term_id ] = $category->name;
		}
		$this->add_control(
			'product_cat',
			[
				'label'       => esc_html__( 'Product Categories', 'woocommerce-photo-reviews' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'description' => esc_html__( 'Display reviews of products from which categories?', 'woocommerce-photo-reviews' ),
				'options'     => $options,
				'label_block' => true,
				'multiple'    => true,
			]
		);
		$this->add_control(
			'products_status',
			[
				'label'       => esc_html__( 'Product Status', 'woocommerce-photo-reviews' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'default'     => [ 'publish' ],
				'options'     => [
					'publish' => esc_html__( 'Publish', 'woocommerce-photo-reviews' ),
					'private' => esc_html__( 'Private', 'woocommerce-photo-reviews' ),
					'draft'   => esc_html__( 'Draft', 'woocommerce-photo-reviews' ),
					'pending' => esc_html__( 'Pending', 'woocommerce-photo-reviews' ),
				],
				'label_block' => true,
				'multiple'    => true,
			]
		);
		$this->add_control(
			'ratings',
			[
				'label'       => esc_html__( 'Show Ratings', 'woocommerce-photo-reviews' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'description' => esc_html__( 'Which ratings should be displayed?', 'woocommerce-photo-reviews' ),
				'default'     => [ '1', '2', '3', '4', '5' ],
				'options'     => [
					'5' => esc_html__( '5 Stars', 'woocommerce-photo-reviews' ),
					'4' => esc_html__( '4 Stars', 'woocommerce-photo-reviews' ),
					'3' => esc_html__( '3 Stars', 'woocommerce-photo-reviews' ),
					'2' => esc_html__( '2 Stars', 'woocommerce-photo-reviews' ),
					'1' => esc_html__( '1 Star', 'woocommerce-photo-reviews' ),
				],
				'label_block' => true,
				'multiple'    => true,
			]
		);
		$this->add_control(
			'only_images',
			[
				'label'        => esc_html__( 'Only Reviews With Images', 'woocommerce-photo-reviews' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => esc_html__( 'Yes', 'woocommerce-photo-reviews' ),
				'label_off'    => esc_html__( 'No', 'woocommerce-photo-reviews' ),
				'return_value' => 'on',
			]
		);
		$this->add_control(
			'orderby',
			[
				'label'       => esc_html__( 'Order By', 'woocommerce-photo-reviews' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'default'     => [ 'comment_date' ],
				'options'     => [
					'comment_date'    => esc_html__( 'Date', 'woocommerce-photo-reviews' ),
					'comment_post_ID' => esc_html__( 'Product', 'woocommerce-photo-reviews' ),
					'comment_ID'      => esc_html__( 'Review ID', 'woocommerce-photo-reviews' ),
					'comment_author'  => esc_html__( 'Author', 'woocommerce-photo-reviews' ),
				],
				'label_block' => true,
				'multiple'    => true,
			]
		);
		$this->add_control(
			'order',
			[
				'label'   => esc_html__( 'Order', 'woocommerce-photo-reviews' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => [
					'ASC'  => esc_html__( 'Ascending', 'woocommerce-photo-reviews' ),
					'DESC' => esc_html__( 'Descending', 'woocommerce-photo-reviews' ),
				],
			]
		);

		$this->add_control(
			'conditional_tag',
			[
				'label'       => esc_html__( 'Conditional Tag', 'woocommerce-photo-reviews' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'description' => __( 'Which page should this shortcode work on? More at <a href="http://codex.wordpress.org/Conditional_Tags" target="_blank">WP\'s conditional tags</a>', 'woocommerce-photo-reviews' ),
				'label_block' => true,
				'dynamic'     => [
					'active' => false,
				],
			]
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'pagination_section',
			[
				'label' => esc_html__( 'Pagination', 'woocommerce-photo-reviews' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'pagination',
			[
				'label'        => esc_html__( 'Enable Pagination', 'woocommerce-photo-reviews' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'on',
				'label_on'     => esc_html__( 'Yes', 'woocommerce-photo-reviews' ),
				'label_off'    => esc_html__( 'No', 'woocommerce-photo-reviews' ),
				'return_value' => 'on',
			]
		);
		$this->add_control(
			'comments_per_page',
			[
				'label'   => esc_html__( 'Reviews Per Page', 'woocommerce-photo-reviews' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 0,
				'step'    => 1,
				'default' => 12,
			]
		);
		$this->add_control(
			'pagination_ajax',
			[
				'label'        => esc_html__( 'Use Ajax Pagination', 'woocommerce-photo-reviews' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'on',
				'description'  => esc_html__( 'Do not reload page when customers select other reviews page or select a filter', 'woocommerce-photo-reviews' ),
				'label_on'     => esc_html__( 'Yes', 'woocommerce-photo-reviews' ),
				'label_off'    => esc_html__( 'No', 'woocommerce-photo-reviews' ),
				'return_value' => 'on',
			]
		);
		$this->add_control(
			'loadmore_button',
			[
				'label'        => esc_html__( 'Loadmore Button', 'woocommerce-photo-reviews' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => esc_html__( 'Yes', 'woocommerce-photo-reviews' ),
				'label_off'    => esc_html__( 'No', 'woocommerce-photo-reviews' ),
				'return_value' => 'on',
				'condition'    => [
					'pagination_ajax' => 'on',
				]
			]
		);
		$this->add_control(
			'pagination_next',
			[
				'label'        => esc_html__( 'Pagination Next', 'woocommerce-photo-reviews' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'placeholder' => esc_html__( 'Next', 'sales-countdown-timer' ),
				'condition'    => [
					'loadmore_button' => '',
				]
			]
		);
		$this->add_control(
			'pagination_pre',
			[
				'label'        => esc_html__( 'Pagination Pre', 'woocommerce-photo-reviews' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'placeholder' => esc_html__( 'Pre', 'sales-countdown-timer' ),
				'condition'    => [
					'loadmore_button' => '',
				]
			]
		);
		$this->add_control(
			'pagination_position',
			[
				'label'     => esc_html__( 'Pagination Position', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => 'left',
				'options'   => [
					'left'   => esc_html__( 'Left', 'woocommerce-photo-reviews' ),
					'center' => esc_html__( 'Center', 'woocommerce-photo-reviews' ),
					'right'  => esc_html__( 'Right', 'woocommerce-photo-reviews' ),
				],
				'selectors' => [
					"{{WRAPPER}} .shortcode-wcpr-pagination" => 'text-align: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_section();
		$this->start_controls_section(
			'filter_section',
			[
				'label' => esc_html__( 'Filter', 'woocommerce-photo-reviews' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'filter_enable',
			[
				'label'        => esc_html__( 'Enable Filter', 'woocommerce-photo-reviews' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'on',
				'label_on'     => esc_html__( 'Yes', 'woocommerce-photo-reviews' ),
				'label_off'    => esc_html__( 'No', 'woocommerce-photo-reviews' ),
				'return_value' => 'on',
			]
		);
		$this->add_control(
			'filter_default_image',
			[
				'label'        => esc_html__( 'Select Images Filter By Default', 'woocommerce-photo-reviews' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => esc_html__( 'Yes', 'woocommerce-photo-reviews' ),
				'label_off'    => esc_html__( 'No', 'woocommerce-photo-reviews' ),
				'return_value' => 'on',
				'condition'    => [
					'pagination_ajax' => 'on',
					'filter_enable'   => 'on',
				]
			]
		);
		$this->add_control(
			'filter_default_verified',
			[
				'label'        => esc_html__( 'Select Verified Filter By Default', 'woocommerce-photo-reviews' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => esc_html__( 'Yes', 'woocommerce-photo-reviews' ),
				'label_off'    => esc_html__( 'No', 'woocommerce-photo-reviews' ),
				'return_value' => 'on',
				'condition'    => [
					'pagination_ajax' => 'on',
					'filter_enable'   => 'on',
				]
			]
		);
		$this->add_control(
			'filter_default_rating',
			[
				'label'       => esc_html__( 'Select Rating Filter By Default', 'woocommerce-photo-reviews' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => '',
				'options'     => [
					''  => esc_html__( 'All Stars', 'woocommerce-photo-reviews' ),
					'1' => esc_html__( '1 Star', 'woocommerce-photo-reviews' ),
					'2' => esc_html__( '2 Stars', 'woocommerce-photo-reviews' ),
					'5' => esc_html__( '5 Stars', 'woocommerce-photo-reviews' ),
					'4' => esc_html__( '4 Stars', 'woocommerce-photo-reviews' ),
					'3' => esc_html__( '3 Stars', 'woocommerce-photo-reviews' ),
				],
				'label_block' => false,
				'condition'   => [
					'pagination_ajax' => 'on',
					'filter_enable'   => 'on',
				]
			]
		);
		$filter_settings = $reviews_settings->get_params( 'photo', 'filter' );
		$this->add_control(
			'area_border_color',
			[
				'label'     => esc_html__( 'Filter Area Border Color', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $filter_settings['area_border_color'],
				'selectors' => [
					"{{WRAPPER}} .shortcode-wcpr-filter-container" => 'border: 1px solid {{VALUE}} !important;',
				],
				'condition' => [
					'filter_enable' => 'on'
				],
				'dynamic'   => [
					'active' => false,
				],
			]
		);
		$this->add_control(
			'area_bg_color',
			[
				'label'     => esc_html__( 'Filter Area Background Color', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $filter_settings['area_bg_color'],
				'selectors' => [
					"{{WRAPPER}} .shortcode-wcpr-filter-container" => 'background-color: {{VALUE}} !important;',
				],
				'condition' => [
					'filter_enable' => 'on'
				],
				'dynamic'   => [
					'active' => false,
				],
			]
		);
		$this->add_control(
			'button_color',
			[
				'label'     => esc_html__( 'Filter Button Color', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $filter_settings['button_color'],
				'selectors' => [
					"{{WRAPPER}} .shortcode-wcpr-filter-container .shortcode-wcpr-filter-button" => 'color: {{VALUE}} !important;',
				],
				'condition' => [
					'filter_enable' => 'on'
				],
				'dynamic'   => [
					'active' => false,
				],
			]
		);
		$this->add_control(
			'button_bg_color',
			[
				'label'     => esc_html__( 'Filter Button Background Color', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $filter_settings['button_bg_color'],
				'selectors' => [
					"{{WRAPPER}} .shortcode-wcpr-filter-container .shortcode-wcpr-filter-button" => 'background-color: {{VALUE}} !important;',
				],
				'condition' => [
					'filter_enable' => 'on'
				],
				'dynamic'   => [
					'active' => false,
				],
			]
		);
		$this->add_control(
			'button_border_color',
			[
				'label'     => esc_html__( 'Filter Button Border Color', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $filter_settings['button_border_color'],
				'selectors' => [
					"{{WRAPPER}} .shortcode-wcpr-filter-container .shortcode-wcpr-filter-button" => 'border: 1px solid {{VALUE}} !important;',
				],
				'condition' => [
					'filter_enable' => 'on'
				],
				'dynamic'   => [
					'active' => false,
				],
			]
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'rating_counts',
			[
				'label' => esc_html__( 'Rating count', 'woocommerce-photo-reviews' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'rating_count',
			[
				'label'        => esc_html__( 'Enable Rating Count', 'woocommerce-photo-reviews' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'on',
				'label_on'     => esc_html__( 'Yes', 'woocommerce-photo-reviews' ),
				'label_off'    => esc_html__( 'No', 'woocommerce-photo-reviews' ),
				'return_value' => 'on',
			]
		);
		$this->add_control(
			'overall_rating',
			[
				'label'        => esc_html__( 'Enable Overall Rating', 'woocommerce-photo-reviews' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'on',
				'label_on'     => esc_html__( 'Yes', 'woocommerce-photo-reviews' ),
				'label_off'    => esc_html__( 'No', 'woocommerce-photo-reviews' ),
				'return_value' => 'on',
			]
		);
		$this->add_control(
			'rating_count_bar_color',
			[
				'label'     => esc_html__( 'Rating Count Bar Color', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $reviews_settings->get_params( 'photo', 'rating_count_bar_color' ),
				'selectors' => [
					"{{WRAPPER}} .rate-percent-bg .rate-percent" => 'background-color: {{VALUE}} !important;',
				],

				'dynamic'   => [
					'active' => false,
				],
			]
		);
		$this->end_controls_section();
		$this->start_controls_section(
			'design',
			[
				'label' => esc_html__( 'Design', 'woocommerce-photo-reviews' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'style',
			[
				'label'   => esc_html__( 'Style', 'woocommerce-photo-reviews' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => $reviews_settings->get_params( 'photo', 'display' ) == 1 ? 'masonry' : 'normal',
				'options' => [
					'masonry' => esc_html__( 'Masonry', 'woocommerce-photo-reviews' ),
					'normal'  => esc_html__( 'Normal', 'woocommerce-photo-reviews' ),
				],
			]
		);
		$this->add_control(
			'masonry_popup',
			[
				'label'     => esc_html__( 'Popup Type', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => $reviews_settings->get_params( 'photo', 'masonry_popup' ),
				'options'   => [
					'review' => esc_html__( 'Whole Review', 'woocommerce-photo-reviews' ),
					'image'  => esc_html__( 'Only Image', 'woocommerce-photo-reviews' ),
					'off'    => esc_html__( 'Disable', 'woocommerce-photo-reviews' ),
				],
				'condition' => [
					'style' => 'masonry',
				]
			]
		);
		$this->add_control(
			'image_popup',
			[
				'label'     => esc_html__( 'Image Popup', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'default'   => $reviews_settings->get_params( 'photo', 'image_popup' ),
				'options'   => [
					'below_thumb' => esc_html__( 'Below Thumb', 'woocommerce-photo-reviews' ),
					'lightbox'    => esc_html__( 'Lightbox', 'woocommerce-photo-reviews' ),
				],
				'condition' => [
					'style' => 'normal',
				]
			]
		);
		$this->add_responsive_control(
			'cols',
			[
				'label'           => esc_html__( 'cols', 'plugin-name' ),
				'type'            => \Elementor\Controls_Manager::NUMBER,
				'min'             => 1,
				'max'             => 5,
				'step'            => 1,
				'devices'         => [ 'desktop', 'mobile' ],
				'desktop_default' => $reviews_settings->get_params( 'photo', 'col_num' ),
				'mobile_default'  => $reviews_settings->get_params( 'photo', 'col_num_mobile' ),
				'condition'       => [
					'style' => 'masonry'
				],
				'selectors'       => [
					"{{WRAPPER}} .shortcode-wcpr-grid" => 'grid-template-columns: repeat({{VALUE}}, 1fr) !important; column-count: {{VALUE}} !important;',
				]
			]
		);
		$this->add_control(
			'cols_gap',
			[
				'label'     => esc_html__( 'Column Gap(PX)', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 50,
				'step'      => 1,
				'default'   => 15,
				'condition' => [
					'style' => 'masonry'
				],
				'selectors' => [
					"{{WRAPPER}} .shortcode-wcpr-grid" => 'grid-gap: {{VALUE}}px !important;',
				],
			]
		);
		$this->add_control(
			'grid_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $reviews_settings->get_params( 'photo', 'grid_bg' ),
				'selectors' => [
					"{{WRAPPER}} .shortcode-wcpr-grid" => 'background-color: {{VALUE}} !important;',
				],
				'condition' => [
					'style' => 'masonry'
				],
				'dynamic'   => [
					'active' => false,
				],
			]
		);
		$this->add_control(
			'grid_item_bg_color',
			[
				'label'     => esc_html__( 'Review Background Color', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $reviews_settings->get_params( 'photo', 'grid_item_bg' ),
				'selectors' => [
					"{{WRAPPER}} .shortcode-wcpr-grid-item" => 'background-color: {{VALUE}} !important;',
				],
				'condition' => [
					'style' => 'masonry'
				],
				'dynamic'   => [
					'active' => false,
				],
			]
		);
		$this->add_control(
			'grid_item_border_color',
			[
				'label'     => esc_html__( 'Review Border Color', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $reviews_settings->get_params( 'photo', 'grid_item_border_color' ),
				'selectors' => [
					"{{WRAPPER}} .shortcode-wcpr-grid-item" => 'border: 1px solid {{VALUE}} !important;',
				],
				'condition' => [
					'style' => 'masonry'
				],
				'dynamic'   => [
					'active' => false,
				],
			]
		);
		$this->add_control(
			'text_color',
			[
				'label'     => esc_html__( 'Review Text Color', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $reviews_settings->get_params( 'photo', 'comment_text_color' ),
				'selectors' => [
					"{{WRAPPER}} .shortcode-wcpr-grid-item" => 'color: {{VALUE}} !important;',
				],
				'condition' => [
					'style' => 'masonry'
				],
				'dynamic'   => [
					'active' => false,
				],
			]
		);
		$this->add_control(
			'star_color',
			[
				'label'     => esc_html__( 'Star Color', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $reviews_settings->get_params( 'photo', 'star_color' ),
				'selectors' => [
					"{{WRAPPER}} .woocommerce-photo-reviews-shortcode .shortcode-wcpr-comments .star-rating:before,
					 {{WRAPPER}} .woocommerce-photo-reviews-shortcode .shortcode-wcpr-comments .star-rating span:before,
					 {{WRAPPER}} .woocommerce-photo-reviews-shortcode .shortcode-wcpr-stars-count .shortcode-wcpr-row .shortcode-wcpr-col-star .star-rating:before,
					 {{WRAPPER}} .woocommerce-photo-reviews-shortcode .shortcode-wcpr-stars-count .shortcode-wcpr-row .shortcode-wcpr-col-star .star-rating span:before,
					 {{WRAPPER}} .woocommerce-photo-reviews-shortcode .shortcode-wcpr-overall-rating-right .shortcode-wcpr-overall-rating-right-star .star-rating:before,
					 {{WRAPPER}} .woocommerce-photo-reviews-shortcode .shortcode-wcpr-overall-rating-right .shortcode-wcpr-overall-rating-right-star .star-rating span:before" => 'color: {{VALUE}} !important;',
				],
				'dynamic'   => [
					'active' => false,
				],
			]
		);
		$this->add_control(
			'verified_color',
			[
				'label'     => esc_html__( 'Verified Badge/Text Color', 'woocommerce-photo-reviews' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'default'   => $reviews_settings->get_params( 'photo', 'verified_color' ),
				'selectors' => [
					"{{WRAPPER}} .woocommerce-photo-reviews-shortcode .woocommerce-review__verified" => 'color: {{VALUE}} !important;',
				],
				'dynamic'   => [
					'active' => false,
				],
			]
		);
		$this->add_control(
			'show_product',
			[
				'label'        => esc_html__( 'Show Product', 'woocommerce-photo-reviews' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => 'on',
				'label_on'     => esc_html__( 'Yes', 'woocommerce-photo-reviews' ),
				'label_off'    => esc_html__( 'No', 'woocommerce-photo-reviews' ),
				'return_value' => 'on',
				'condition'    => [
					'style'         => 'masonry',
					'masonry_popup' => 'review',
				]
			]
		);
		$this->add_control(
			'enable_box_shadow',
			[
				'label'        => esc_html__( 'Enable Box Shadow', 'woocommerce-photo-reviews' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => $reviews_settings->get_params( 'photo', 'enable_box_shadow' ) ? 'on' : '',
				'label_on'     => esc_html__( 'Yes', 'woocommerce-photo-reviews' ),
				'label_off'    => esc_html__( 'No', 'woocommerce-photo-reviews' ),
				'return_value' => 'on',
				'condition'    => [
					'style' => 'masonry',
				]
			]
		);
		$this->add_control(
			'full_screen_mobile',
			[
				'label'        => esc_html__( 'Full Screen On Mobile', 'woocommerce-photo-reviews' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'default'      => $reviews_settings->get_params( 'photo', 'full_screen_mobile' ) ? 'on' : '',
				'label_on'     => esc_html__( 'Yes', 'woocommerce-photo-reviews' ),
				'label_off'    => esc_html__( 'No', 'woocommerce-photo-reviews' ),
				'return_value' => 'on',
				'condition'    => [
					'mobile'        => 'on',
					'style'         => 'masonry',
					'masonry_popup' => 'review',
				]
			]
		);
		$this->add_control(
			'custom_css',
			[
				'label'    => esc_html__( 'Custom CSS', 'woocommerce-photo-reviews' ),
				'type'     => \Elementor\Controls_Manager::CODE,
				'language' => 'css',
				'rows'     => 20,
			]
		);
		$this->end_controls_section();

	}

	public function get_shortcode_text() {
		$settings        = $this->get_settings_for_display();
		$products        = $settings['products'] ? implode( ',', $settings['products'] ) : '';
		$products_status = $settings['products_status'] ? implode( ',', $settings['products_status'] ) : '';
		$product_cat     = $settings['product_cat'] ? implode( ',', $settings['product_cat'] ) : '';
		$orderby         = $settings['orderby'] ? implode( ',', $settings['orderby'] ) : '';
		$ratings         = $settings['ratings'] ? implode( ',', $settings['ratings'] ) : '';

		return "[wc_photo_reviews_shortcode is_elementor='yes' comments_per_page='{$settings['comments_per_page']}' cols='{$settings['cols']}' cols_mobile='{$settings['cols_mobile']}' cols_gap='{$settings['cols_gap']}' use_single_product='{$settings['use_single_product']}' products='{$products}' products_status='{$products_status}' grid_bg_color='{$settings['grid_bg_color']}' grid_item_bg_color='{$settings['grid_item_bg_color']}' grid_item_border_color='{$settings['grid_item_border_color']}' text_color='{$settings['text_color']}' star_color='{$settings['star_color']}' product_cat='{$product_cat}' order='{$settings['order']}' orderby='{$orderby}' show_product='{$settings['show_product']}' filter='{$settings['filter_enable']}' pagination='{$settings['pagination']}' pagination_ajax='{$settings['pagination_ajax']}' loadmore_button='{$settings['loadmore_button']}' pagination_next='{$settings['pagination_next']}' pagination_pre='{$settings['pagination_pre']}' filter_default_image='{$settings['filter_default_image']}' filter_default_verified='{$settings['filter_default_verified']}' filter_default_rating='{$settings['filter_default_rating']}' pagination_position='{$settings['pagination_position']}' conditional_tag='{$settings['conditional_tag']}' custom_css='{$settings['custom_css']}' masonry_popup='{$settings['masonry_popup']}' image_popup='{$settings['image_popup']}' ratings='{$ratings}' mobile='{$settings['mobile']}' style='{$settings['style']}' enable_box_shadow='{$settings['enable_box_shadow']}' full_screen_mobile='{$settings['full_screen_mobile']}' overall_rating='{$settings['overall_rating']}' rating_count='{$settings['rating_count']}' only_images='{$settings['only_images']}' area_border_color='{$settings['area_border_color']}' area_bg_color='{$settings['area_bg_color']}' button_color='{$settings['button_color']}' button_bg_color='{$settings['button_bg_color']}' button_border_color='{$settings['button_border_color']}' rating_count_bar_color='{$settings['rating_count_bar_color']}' verified_color='{$settings['verified_color']}']";
	}

	protected function render() {
		echo do_shortcode( $this->get_shortcode_text() );
	}

	public function render_plain_content() {
		echo $this->get_shortcode_text();
	}
}