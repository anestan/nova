<?php
/**
 * UAEL Video Module.
 *
 * @package UAEL
 */

namespace UltimateElementor\Modules\Video;

use UltimateElementor\Base\Module_Base;
use UltimateElementor\Classes\UAEL_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Module.
 */
class Module extends Module_Base {

	/**
	 * Module should load or not.
	 *
	 * @since 0.0.1
	 * @access public
	 *
	 * @return bool true|false.
	 */
	public static function is_enable() {
		return true;
	}

	/**
	 * All sections.
	 *
	 * @since 1.33.1
	 * @var all_sections
	 */
	private static $all_sections = array();

	/**
	 * Video Widgets.
	 *
	 * @since 1.33.1
	 * @var all_video_widgets
	 */
	private static $all_video_widgets = array();

	/**
	 * Get Module Name.
	 *
	 * @since 0.0.1
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'uael-video';
	}

	/**
	 * Get Widgets.
	 *
	 * @since 0.0.1
	 * @access public
	 *
	 * @return array Widgets.
	 */
	public function get_widgets() {
		return array(
			'Video',
		);
	}

	/**
	 * Constructor.
	 */
	public function __construct() { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		parent::__construct();
		if ( UAEL_Helper::is_widget_active( 'Video' ) ) {

			add_action(
				'elementor/frontend/before_render',
				function( $obj ) {

					$current_widget = $obj->get_data();
					// If multiple times widget used on a page add all the video elements on a page in a single array.
					if ( isset( $current_widget['elType'] ) && 'section' === $current_widget['elType'] ) {

						array_push( self::$all_sections, $current_widget );
					}
					// If multiple times widget used on a page add all the main widget elements on a page in a single array.
					if ( isset( $current_widget['widgetType'] ) && 'uael-video' === $current_widget['widgetType'] ) {
						if ( ! in_array( $current_widget['id'], self::$all_video_widgets, true ) ) {
							array_push( self::$all_video_widgets, $current_widget['id'] );
						}
					}
				}
			);
			add_action( 'wp_footer', array( $this, 'render_video_schema' ) );
		}
	}

	/**
	 * Returns the link of the videos.
	 *
	 * @param array $settings Control Settings array.
	 *
	 * @access public
	 * @return mixed|string
	 * @since 1.33.1
	 */
	public function get_video_link( $settings ) {
		$video_type = $settings['video_type'];
		$video_link = '';
		switch ( $video_type ) {
			case 'youtube':
				$video_link = $settings['youtube_link'];
				break;
			case 'vimeo':
				$video_link = $settings['vimeo_link'];
				break;
			case 'wistia':
				$video_link = ( preg_match( '/https?\:\/\/[^\",]+/i', $settings['wistia_link'], $url ) ) ? $url[0] : '';
				break;
			case 'hosted':
				if ( 'hosted' === $video_type && 'yes' !== $settings['insert_link'] ) {
					$video_link = $settings['hosted_link']['url'];
				} elseif ( 'hosted' === $video_type && 'yes' === $settings['insert_link'] ) {
					$video_link = $settings['external_link']['url'];
				}
				break;
			default:
		}
		return $video_link;
	}

	/**
	 * Render the Video schema.
	 *
	 * @since 1.33.1
	 *
	 * @access public
	 */
	public function render_video_schema() {
		if ( ! empty( self::$all_video_widgets ) ) {

			$elementor  = \Elementor\Plugin::$instance;
			$data       = self::$all_sections;
			$widget_ids = self::$all_video_widgets;
			$video_data = array();

			foreach ( $widget_ids as $widget_id ) {

				$widget_data            = $this->find_element_recursive( $data, $widget_id );
				$widget                 = $elementor->elements_manager->create_element_instance( $widget_data );
				$settings               = $widget->get_settings();
				$content_schema_warning = false;
				$enable_schema          = $settings['schema_support'];
				$video_link             = $this->get_video_link( $settings );
				$is_custom_thumbnail    = 'yes' === $settings['show_image_overlay'] ? true : false;
				$custom_thumbnail_url   = isset( $settings['image_overlay']['url'] ) ? $settings['image_overlay']['url'] : '';

				if ( 'yes' === $enable_schema && ( ( '' === $settings['schema_title'] || '' === $settings['schema_description'] || ( ! $is_custom_thumbnail && '' === $settings['schema_thumbnail']['url'] ) || '' === $settings['schema_upload_date'] ) || ( $is_custom_thumbnail && '' === $custom_thumbnail_url ) ) ) {
					$content_schema_warning = true;
				}

				if ( 'yes' === $enable_schema && false === $content_schema_warning ) {
					$video_data = array(
						'@context'     => 'https://schema.org',
						'@type'        => 'VideoObject',
						'name'         => $settings['schema_title'],
						'description'  => $settings['schema_description'],
						'thumbnailUrl' => ( $is_custom_thumbnail ) ? $custom_thumbnail_url : $settings['schema_thumbnail']['url'],
						'uploadDate'   => $settings['schema_upload_date'],
						'contentUrl'   => $video_link,
						'embedUrl'     => $video_link,
					);
				}
			}
			UAEL_Helper::print_json_schema( $video_data );
		}
	}

	/**
	 * Get Widget Setting data.
	 *
	 * @since 1.33.1
	 * @access public
	 * @param array  $elements Element array.
	 * @param string $form_id Element ID.
	 * @return Boolean True/False.
	 */
	public function find_element_recursive( $elements, $form_id ) {

		foreach ( $elements as $element ) {
			if ( $form_id === $element['id'] ) {
				return $element;
			}

			if ( ! empty( $element['elements'] ) ) {
				$element = $this->find_element_recursive( $element['elements'], $form_id );

				if ( $element ) {
					return $element;
				}
			}
		}

		return false;
	}
}
