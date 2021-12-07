<?php if ( ! defined( 'ABSPATH' ) ) { exit; }  // if direct access

/**
 * Scripts and styles Class
 */
class WPA_WG_Scripts{

	/**
	 * @var null
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * @return WPA_WG_Scripts
	 * @since 1.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ) );
	}

	/**
	 * Plugin Scripts and Styles
	 *
	 */
	function front_scripts() {
		// CSS Files
		wp_register_style( 'wpawg-flaticon', WPA_WG_URL . 'assets/font/flaticon.css', array(), WPA_WG_VERSION );
		wp_register_style( 'slick', WPA_WG_URL . 'assets/css/slick.css', array(), WPA_WG_VERSION );
		wp_register_style( 'slick-theme', WPA_WG_URL . 'assets/css/slick-theme.css', array(), WPA_WG_VERSION );
		wp_register_style( 'magnific-popup', WPA_WG_URL . 'assets/css/magnific-popup.css', array(), WPA_WG_VERSION );
		wp_enqueue_style( 'wpawg-style', WPA_WG_URL . 'assets/css/style.css', array(), WPA_WG_VERSION );

		//JS Files
		wp_register_script( 'slick', WPA_WG_URL . 'assets/js/slick.min.js', array( 'jquery' ), WPA_WG_VERSION, true );
		wp_register_script( 'magnific-popup', WPA_WG_URL . 'assets/js/jquery.magnific-popup.min.js', array( 'jquery' ), WPA_WG_VERSION, true );
		wp_register_script( 'wpawg-zoom', WPA_WG_URL . 'assets/js/jquery.zoom.js', array( 'jquery' ), WPA_WG_VERSION, true );
		wp_enqueue_script( 'wpawg-scripts', WPA_WG_URL . 'assets/js/scripts.js', array( 'jquery' ), WPA_WG_VERSION, true );
	}
}
new WPA_WG_Scripts();