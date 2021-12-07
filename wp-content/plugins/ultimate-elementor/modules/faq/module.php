<?php
/**
 * UAEL FAQ widget
 *
 * @package UAEL
 */

namespace UltimateElementor\Modules\FAQ;

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
	 * @since 1.22.0
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
	 * @var all_sections
	 */
	private static $all_sections = array();

	/**
	 * FAQ Widgets.
	 *
	 * @var all_faq_widgets
	 */
	private static $all_faq_widgets = array();

	/**
	 * Get Module Name.
	 *
	 * @since 1.22.0
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'uael-faq';
	}

	/**
	 * Get Widgets.
	 *
	 * @since 1.22.0
	 * @access public
	 *
	 * @return array Widgets.
	 */
	public function get_widgets() {
		return array(
			'FAQ',
		);
	}

	/**
	 * Constructor.
	 */
	public function __construct() { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		parent::__construct();

		if ( UAEL_Helper::is_widget_active( 'FAQ' ) ) {

			add_action(
				'elementor/frontend/before_render',
				function( $obj ) {

					$current_widget = $obj->get_data();

					if ( isset( $current_widget['elType'] ) && 'section' === $current_widget['elType'] ) {

						array_push( self::$all_sections, $current_widget );
					}

					if ( isset( $current_widget['widgetType'] ) && 'uael-faq' === $current_widget['widgetType'] ) {
						if ( ! in_array( $current_widget['id'], self::$all_faq_widgets, true ) ) {
							array_push( self::$all_faq_widgets, $current_widget['id'] );
						}
					}
				}
			);
			add_action( 'wp_footer', array( $this, 'render_faq_schema' ) );
		}
	}

	/**
	 * Render the FAQ schema.
	 *
	 * @since 1.29.0
	 *
	 * @access public
	 */
	public function render_faq_schema() {

		if ( ! empty( self::$all_faq_widgets ) ) {

			$elementor   = \Elementor\Plugin::$instance;
			$data        = self::$all_sections;
			$widget_ids  = self::$all_faq_widgets;
			$object_data = array();

			foreach ( $widget_ids as $widget_id ) {

				$widget_data            = $this->find_element_recursive( $data, $widget_id );
				$widget                 = $elementor->elements_manager->create_element_instance( $widget_data );
				$settings               = $widget->get_settings();
				$content_schema_warning = 0;
				$enable_schema          = $settings['schema_support'];

				foreach ( $settings['tabs'] as $key ) {
					if ( 'content' !== $key['faq_content_type'] ) {
						$content_schema_warning = 1;
					}
				}

				if ( 'yes' === $enable_schema && ( 0 === $content_schema_warning ) ) {
					foreach ( $settings['tabs'] as $faqs ) {
						if ( '' !== $faqs['question'] && '' !== $faqs['answer'] ) {
							$new_data = array(
								'@type'          => 'Question',
								'name'           => $faqs['question'],
								'acceptedAnswer' =>
								array(
									'@type' => 'Answer',
									'text'  => $faqs['answer'],
								),
							);
							array_push( $object_data, $new_data );
						}
					}
				}
			}

			if ( $object_data ) {

				$schema_data = array(
					'@context'   => 'https://schema.org',
					'@type'      => 'FAQPage',
					'mainEntity' => $object_data,
				);

				$encoded_data = wp_json_encode( $schema_data );
				?>
				<script type="application/ld+json">
					<?php print_r( $encoded_data ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r ?>
				</script>
				<?php
			}
		}
	}

	/**
	 * Get Widget Setting data.
	 *
	 * @since 1.26.3
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
