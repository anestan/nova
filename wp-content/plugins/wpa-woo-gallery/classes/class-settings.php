<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WPA_WOO_Gallery_Settings' ) ):
	class WPA_WOO_Gallery_Settings {

		public function __construct() {
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
			add_action( 'woocommerce_settings_tabs_wpa-wg-gallery', array( $this, 'settings_tab' ) );
			add_action( 'woocommerce_update_options_wpa-wg-gallery', array( $this, 'update_settings' ) );
		}
		
		public function add_settings_tab( $settings_tabs ) {
			$settings_tabs[ 'wpa-wg-gallery' ] = esc_html__( 'WooCommerce Gallery', 'wpa-gallery' );
			
			return $settings_tabs;
		}
		
		public function settings_tab() {
			woocommerce_admin_fields( $this->get_settings() );
		}
		
		public function update_settings() {
			woocommerce_update_options( $this->get_settings() );
		}
		
		public function get_settings() {
			
			$settings = array(
				
				array(
					'title' => esc_html__( 'General Settings', 'wpa-gallery' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'wpawg_general_settings'
				),

				array(
					'title'    => esc_html__( 'Gallery Layout', 'wpa-gallery' ),
					'desc'     => esc_html__( 'This is gallery layout option', 'wpa-gallery' ),
					'id'       => 'wpawg_gallery_layout',
					'class'    => 'wc-enhanced-select',
					'default'  => 'wpa-gallery-default',
					'type'     => 'select',
					'options'  => array(
						'wpa-gallery-default'        	=> esc_html__( 'Horizontal (Default)', 'wpa-gallery' ),
						'wpa-gallery-position-left' 	=> esc_html__( 'Vertical (Gallery thumbnail left)', 'wpa-gallery' ),
						'wpa-gallery-position-right' 	=> esc_html__( 'Vertical (Gallery thumbnail right)', 'wpa-gallery' )
					),
					'desc_tip' => true
				),

				array(
					'title'    => esc_html__( 'Image Zoom', 'wpa-gallery' ),
					'id'       => 'wpawg_image_zoom',
					'default'  => 'yes',
					'type'     => 'checkbox',
					'desc' => esc_html__( 'Enable or disable image zoom, default is enable.', 'wpa-gallery' ),
				),

				array(
					'title'    => esc_html__( 'Image Popup', 'wpa-gallery' ),
					'id'       => 'wpawg_image_popup',
					'default'  => 'yes',
					'type'     => 'checkbox',
					'desc' => esc_html__( 'Enable or disable image Popup, default is enable.', 'wpa-gallery' )
				),

				array(
					'title'    => esc_html__( 'Video Popup', 'wpa-gallery' ),
					'id'       => 'wpawg_video_popup',
					'default'  => 'yes',
					'type'     => 'checkbox',
					'desc' => esc_html__( 'Enable or disable video Popup, default is enable.', 'wpa-gallery' )
				),

				array(
					'type' => 'sectionend',
					'id'   => 'wpawg_general_settings_end',
				),

				array(
					'title' => esc_html__( 'Gallery Settings', 'wpa-gallery' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'wpawg_carousel_settings'
				),

				array(
					'title'    => esc_html__( 'Gallery Type', 'wpa-gallery' ),
					'desc'     => esc_html__( 'Make gallery with featued image or only gallery image', 'wpa-gallery' ),
					'id'       => 'wpawg_gallery_type',
					'class'    => 'wc-enhanced-select',
					'default'  => 'wpawg_gallery_featured',
					'type'     => 'select',
					'options'  => array(
						'wpawg_gallery_featured'  => esc_html__( 'Featured image and Gallery Image', 'wpa-gallery' ),
						'wpawg_only_gallery'  => esc_html__( 'Gallery image only', 'wpa-gallery' )
					),
					'desc_tip' => true
				),

				array(
					'title'    => esc_html__( 'Gallery Thumbnails', 'wpa-gallery' ),
					'desc'     => esc_html__( 'The number of display gallery thumbnails', 'wpa-gallery' ),
					'id'       => 'wpawg_thumbnails',
					'class'    => 'wc-enhanced-select',
					'default'  => '3',
					'type'     => 'select',
					'options'  => array(
						'3'        	=> esc_html__( '3 Thumbnails', 'wpa-gallery' ),
						'4'        	=> esc_html__( '4 Thumbnails', 'wpa-gallery' ),
						'5'        	=> esc_html__( '5 Thumbnails', 'wpa-gallery' ),
						'6'        	=> esc_html__( '6 Thumbnails', 'wpa-gallery' )
					),
					'desc_tip' => true
				),

				array(
					'title'    => esc_html__( 'Navigation', 'wpa-gallery' ),
					'id'       => 'wpawg_navigation',
					'default'  => 'yes',
					'type'     => 'checkbox',
					'desc' 	   => esc_html__( 'Enable or disable gallery navigation, default is enable.', 'wpa-gallery' )
				),

				array(
					'title'    => esc_html__( 'Auto Play', 'wpa-gallery' ),
					'id'       => 'wpawg_autoplay',
					'default'  => 'no',
					'type'     => 'checkbox',
					'desc' 	   => esc_html__( 'Enable or disable gallery autoplay, default is disable.', 'wpa-gallery' )
				),

				array(
					'title'    => esc_html__( 'Auto Play Speed', 'wpa-gallery' ),
					'id'       => 'wpawg_autoplay_speed',
					'default'  => '2000',
					'type'     => 'text',
					'desc_tip' => esc_html__( 'Enter auto play speed in milliseconds, default is 2000(2 second).', 'wpa-gallery' )
				),

				array(
					'title'    => esc_html__( 'Center Mode', 'wpa-gallery' ),
					'id'       => 'wpawg_centermode',
					'default'  => 'yes',
					'type'     => 'checkbox',
					'desc' 	   => esc_html__( 'Enable or disable gallery center mode, default is enable.', 'wpa-gallery' )
				),

				array(
					'title'    => esc_html__( 'Infinite Loop', 'wpa-gallery' ),
					'id'       => 'wpawg_infinite_loop',
					'default'  => 'no',
					'type'     => 'checkbox',
					'desc' 	   => esc_html__( 'Enable or disable infinite loop, default is enable.', 'wpa-gallery' )
				),

				array(
					'title'    => esc_html__( 'Auto Height gallery', 'wpa-gallery' ),
					'id'       => 'wpawg_adaptive_height',
					'default'  => 'yes',
					'type'     => 'checkbox',
					'desc' 	   => esc_html__( 'Enable or disable gallery auto height mode, default is enable.', 'wpa-gallery' )
				),

				array(
					'type' => 'sectionend',
					'id'   => 'wpawg_gallery_settings_end',
				),

				array(
					'title' => esc_html__( 'Gallery Image Sizes', 'wpa-gallery' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'wpawg_size_settings'
				),

				array(
					'title'    => esc_html__( 'Width (Large Image)', 'wpa-gallery' ),
					'id'       => 'wpawg_large_image_width',
					'default'  => 600,
					'type'     => 'number',
					'desc_tip' => esc_html__( 'Enter large image width', 'wpa-gallery' )
				),

				array(
					'title'    => esc_html__( 'Height (Large Image)', 'wpa-gallery' ),
					'id'       => 'wpawg_large_image_height',
					'default'  => 600,
					'type'     => 'number',
					'desc_tip' => esc_html__( 'Enter large image height', 'wpa-gallery' )
				),

				array(
					'title'    => esc_html__( 'Hard Crop (Large Image)', 'wpa-gallery' ),
					'id'       => 'wpawg_large_hardcrop',
					'default'  => 'yes',
					'type'     => 'checkbox',
					'desc' 	   => esc_html__( 'Images will be hardcrop with the above ratio', 'wpa-gallery' )
				),

				array(
					'title'    => esc_html__( 'Width (Thumbnail)', 'wpa-gallery' ),
					'id'       => 'wpawg_thumbnail_image_width',
					'default'  => 110,
					'type'     => 'number',
					'desc_tip' => esc_html__( 'Enter thumbnail image width', 'wpa-gallery' )
				),

				array(
					'title'    => esc_html__( 'Height (Thumbnail)', 'wpa-gallery' ),
					'id'       => 'wpawg_thumbnail_image_height',
					'default'  => 110,
					'type'     => 'number',
					'desc_tip' => esc_html__( 'Enter thumbnail image height', 'wpa-gallery' )
				),

				array(
					'title'    => esc_html__( 'Hard Crop (Thumbnail)', 'wpa-gallery' ),
					'id'       => 'wpawg_thumbnail_hardcrop',
					'default'  => 'yes',
					'type'     => 'checkbox',
					'desc' 	   => esc_html__( 'Images will be hardcrop with the above ratio', 'wpa-gallery' )
				),

				array(
					'type' => 'sectionend',
					'id'   => 'wpawg_size_settings_end',
				),

				array(
					'title' => esc_html__( 'Color Settings', 'wpa-gallery' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'wpawg_color_settings'
				),

				array(
					'title'    => esc_html__( 'Navigation color', 'wpa-gallery' ),
					'id'       => 'wpawg_navigation_color',
					'type'     => 'color',
					'default'  => '#212121',
					'desc_tip' => esc_html__( 'Navigation color option.', 'wpa-gallery' )
				),

				array(
					'title'    => esc_html__( 'Image popup icon color', 'wpa-gallery' ),
					'id'       => 'wpawg_image_popup_color',
					'type'     => 'color',
					'default'  => '#212121',
					'desc_tip' => esc_html__( 'Image popup icon color option.', 'wpa-gallery' )
				),

				array(
					'title'    => esc_html__( 'Video popup icon color', 'wpa-gallery' ),
					'id'       => 'wpawg_video_popup_color',
					'type'     => 'color',
					'default'  => '#de0000',
					'desc_tip' => esc_html__( 'Video popup icon color option.', 'wpa-gallery' )
				),
				
				array(
					'type' => 'sectionend',
					'id'   => 'wpawg_color_settings_end'
				)
			);
			
			return apply_filters( 'wpa_wg_gallery_settings', $settings );
		}
	}

	new WPA_WOO_Gallery_Settings();
endif;