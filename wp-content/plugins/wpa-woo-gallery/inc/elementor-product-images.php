<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Elementor\Widget_Base;

class WPA_WooSingleProductImages extends Widget_Base {

	public function get_name() {
		return 'wpa-woocommerce-product-images';
	}

	public function get_title() {
		return __( 'Product Images by WPAddons', 'wpa-gallery' );
	}

	public function get_icon() {
		return 'eicon-product-images';
	}

	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'image', 'product', 'gallery', 'lightbox' ];
	}

	public function get_categories() {
		return [ 'woocommerce-elements-single' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'section_product_gallery_style',
			[
				'label' => __( 'Image Style', 'wpa-gallery' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_responsive_control(
				'image_padding',
				[
					'label' => __( 'Padding', 'wpa-gallery' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .wpa-product-gallery .wpa-woocommerce-product-gallery__image, 
						{{WRAPPER}} .wpa-product-single-image .wpa-woocommerce-product-gallery__image' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Background::get_type(),
				[
					'name' => 'image_background',
					'label' => __( 'Image Background', 'wpa-gallery' ),
					'types' => [ 'classic', 'gradient' ],
					'selector' => '{{WRAPPER}} .wpa-product-gallery .wpa-woocommerce-product-gallery__image, 
					{{WRAPPER}} .wpa-product-single-image .wpa-woocommerce-product-gallery__image',
					'separator' => 'before',
				]
			);


			$this->add_control(
				'heading_border_style',
				[
					'label' => __( 'Image Border', 'wpa-gallery' ),
					'type' => \Elementor\Controls_Manager::HEADING,
					'separator' => 'before',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'image_border',
					'selector' => '{{WRAPPER}} .wpa-product-gallery .wpa-woocommerce-product-gallery__image img, 
					{{WRAPPER}} .wpa-product-single-image .wpa-woocommerce-product-gallery__image img',
				]
			);

			$this->add_responsive_control(
				'image_border_radius',
				[
					'label' => __( 'Border Radius', 'wpa-gallery' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .wpa-product-gallery .wpa-woocommerce-product-gallery__image, 
						{{WRAPPER}} .wpa-product-gallery .wpa-woocommerce-product-gallery__image img, 
						{{WRAPPER}} .wpa-product-single-image .wpa-woocommerce-product-gallery__image,
						{{WRAPPER}} .wpa-product-single-image .wpa-woocommerce-product-gallery__image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					],
				]
			);
		$this->end_controls_section();


		$this->start_controls_section(
			'section_product_thumb_style',
			[
				'label' => __( 'Thumbnail Style', 'wpa-gallery' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

			$this->add_responsive_control(
				'thumbnail_padding',
				[
					'label' => __( 'Padding', 'wpa-gallery' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .wpa-product-gallery-thumbs .wpa-woocommerce-product-gallery__image' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Background::get_type(),
				[
					'name' => 'thumbnail_background',
					'label' => __( 'Thumbnail Background', 'wpa-gallery' ),
					'types' => [ 'classic', 'gradient' ],
					'selector' => '{{WRAPPER}} .wpa-product-gallery-thumbs .wpa-woocommerce-product-gallery__image',
					'separator' => 'before',
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				[
					'name' => 'thumbs_border',
					'selector' => '{{WRAPPER}} .wpa-product-gallery-thumbs .wpa-woocommerce-product-gallery__image img',
				]
			);

			$this->add_responsive_control(
				'thumbs_border_radius',
				[
					'label' => __( 'Border Radius', 'wpa-gallery' ),
					'type' => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .wpa-product-gallery-thumbs .wpa-woocommerce-product-gallery__image, {{WRAPPER}} .wpa-product-gallery-thumbs .wpa-woocommerce-product-gallery__image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
					],
				]
			);

		$this->end_controls_section();
	}

	public function render() {
		$settings = $this->get_settings_for_display();
		global $product;

		$product = wc_get_product();

		if ( empty( $product ) ) {
			return;
		}

		wpa_wg_get_template( 'product-image.php', array(), '', WPA_WG_PATH . 'templates/' );

		if ( wp_doing_ajax() ) {
			?>
			<script>
				jQuery( '.woocommerce-product-gallery' ).each( function() {
					jQuery( this ).wc_product_gallery();
				} );
			</script>
			<?php
		}
	}
}
