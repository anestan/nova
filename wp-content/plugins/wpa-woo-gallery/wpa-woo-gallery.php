<?php
/*
Plugin Name: WooCommerce Product Gallery
Plugin URI: https://wpaddons.net/product/v-neck-t-shirt/
Description: The most beautiful image gallery for WooCommerce product, this gallery support image zoom, image popup and video popup.
Author: WPAddons
Version: 1.6
Author URI: https://wpaddons.net
Text Domain: wpa-gallery
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) :
    exit; // Exit if accessed directly
endif;


if (! class_exists( 'WPA_Woo_Gallery' )) {
	class WPA_Woo_Gallery{

		/**
		 * plugin version
		 * @var string
		 */
		public $version = '1.6';


		/**
		 * @var null
		 * @since 1.0
		 */
		protected static $_instance = null;


		/**
		 * @return WPA_Woo_Gallery
		 * @since 1.0
		 */
		public static function instance(){
			if (is_null(self::$_instance)) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * WPA_Woo_Gallery constructor.
		 */
		function __construct(){
			// Define constants
			$this->define_constants();

			// Include required files
			$this->includes();

			// Initialize the hooks
			$this->init_hooks();
		}


		/**
		 * Initialize WordPress hooks
		 *
		 * @return void
		 */
		function init_hooks() {

			// filter hooks
			add_filter( 'plugin_action_links', array( $this, 'add_plugin_action_links' ), 10, 2 );
			add_filter( "plugin_row_meta", array( $this, 'after_post_carousel_row_meta' ), 10, 4 );
			add_filter( 'attachment_fields_to_edit', array( $this, 'add_attachment_video_url_field'), 10, 2 );

			// action hooks
			add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );
			add_action( 'activated_plugin', array( $this, 'redirect_help_page' ));
			add_action( 'edit_attachment', array( $this, 'save_attachment_video' ));
		}


		/**
		 * Define constants
		 *
		 * @since 1.0
		 */
		public function define_constants() {
			$this->define( 'WPA_WG_VERSION', $this->version );
			$this->define( 'WPA_WG_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'WPA_WG_URL', plugin_dir_url( __FILE__ ) );
			$this->define( 'WPA_WG_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'WPA_WG_PLUGIN_TEMPLATE_PATH', trailingslashit( plugin_dir_path( __FILE__ ) . 'templates' ) );
		}

		/**
		 * Define constant if not already set
		 *
		 * @since 1.0
		 *
		 * @param  string $name
		 * @param  string|bool $value
		 */
		public function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Load TextDomain for plugin.
		 *
		 * @since 1.0
		 */
		public function load_text_domain() {

			load_plugin_textdomain( 'wpa-gallery', false, 'wpa-woo-gallery/languages' ); //WPA_WG_BASENAME . '/languages'
		}

		/**
		 * Add plugin action menu
		 *
		 * @param array $links
		 * @param string $file
		 *
		 * @return array
		 */
		public function add_plugin_action_links( $links, $file ) {

			if ( $file == WPA_WG_BASENAME ) {
				$new_links = array(
					sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=wc-settings&tab=wpa-wg-gallery' ), esc_html__( 'Settings', 'wpa-gallery' ) ),
				);

				return array_merge( $new_links, $links );
			}

			return $links;
		}

		/**
		 * Add plugin row meta link
		 *
		 * @since 1.0
		 *
		 * @param $plugin_meta
		 * @param $file
		 *
		 * @return array
		 */

		public function after_post_carousel_row_meta( $plugin_meta, $file ) {
			if ( $file == WPA_WG_BASENAME ) {
				$plugin_meta[] = '<a href="https://wpaddons.net/support" target="_blank">' . esc_html__( 'Support', 'wpa-gallery' ) . '</a>';
			}

			return $plugin_meta;
		}


		/**
		 * Include the required files
		 *
		 * @return void
		 */
		public function includes() {
			include_once WPA_WG_PATH . 'classes/class-settings.php';
			include_once WPA_WG_PATH . 'classes/class-metabox.php';
			include_once WPA_WG_PATH . 'inc/functions.php';
			include_once WPA_WG_PATH . 'inc/scripts.php';
			
			if (function_exists('elementor_pro_load_plugin')) {
				include_once WPA_WG_PATH . 'elementor.php';
			}
		}

		public function template_path( $file ) {
			$file = ltrim( $file, '/' );
			
			return WPA_WG_PLUGIN_TEMPLATE_PATH . $file;
		}


		public function add_attachment_video_url_field( $form_fields, $post ) {
		    $field_value = get_post_meta( $post->ID, 'wpa_gallery_video', true );
		    $form_fields['wpa_gallery_video'] = array(
		        'value' => $field_value ? $field_value : '',
		        'label' => esc_html__( 'Video URL', 'wpa-gallery' ),
		        'helps' => esc_html__( 'Enter a video URL. You can use youtube, vimeo or custom video URL', 'wpa-gallery' )
		    );
		    return $form_fields;
		}


		public function save_attachment_video( $attachment_id ) {
		    if ( isset( $_REQUEST['attachments'][$attachment_id]['wpa_gallery_video'] ) ) {
		        $video_url = $_REQUEST['attachments'][$attachment_id]['wpa_gallery_video'];
		        update_post_meta( $attachment_id, 'wpa_gallery_video', $video_url );
		    }
		}


		/**
		 * Redirect after active
		 * @param $plugin
		 */
		public function redirect_help_page( $plugin ) {
			if ( $plugin == WPA_WG_BASENAME ) {
				exit( wp_redirect( admin_url( 'admin.php?page=wc-settings&tab=wpa-wg-gallery' ) ) );
			}
		}

		/**
	     * It will serialize and then encode the string using base64_encode() and return the encoded data
	     * @param $data
	     * @return string
	     */
	    public static function serialize_and_encode24($data) {
	        return base64_encode(serialize($data));
	    }

	    /**
	     * It will decode the data using base64_decode() and then unserialize the data and return it
	     * @param string $data Encoded strings that should be decoded and then unserialize
	     * @return mixed
	     */
	    public static function unserialize_and_decode24($data){
	        return unserialize(base64_decode($data));
	    }
	}
}


/**
 * Returns the main instance.
 *
 * @since 1.0
 * @return WPA_Woo_Gallery
 */
function wpawg_woo_gallery() {
	return WPA_Woo_Gallery::instance();
}

//WPA_Woo_Gallery instance.
wpawg_woo_gallery();