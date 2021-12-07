<?php
/*
  Plugin Name: WooSwatches - Woocommerce Color or Image Variation Swatches
  Plugin URI: http://woomatrix.com
  Description: Convert variable select box into color or image select.
  Version: 3.3.11
  Author: WooMatrix
  Author URI: http://woomatrix.com
  Requires at least: 3.3
  Tested up to: 5.8.0
  WC requires at least: 3.0.0
  WC tested up to: 5.5.1
  Text Domain: wcva
  Domain Path: /languages
  
*/
    /**
     * Global Variable wcva_PLUGIN_URL
     */
    if( !defined( 'wcva_PLUGIN_URL' ) )
          define( 'wcva_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	  
    /**
     * Global Variable wcva_base_url
     */
    if( !defined( 'wcva_base_url' ) )
          define( 'wcva_base_url', plugin_basename(__FILE__) );
	  
	
	/*
	 * localization
	 */
    load_plugin_textdomain( 'wcva', false, basename( dirname(__FILE__) ).'/languages' );


    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    
	/**
	 * Check if quick view plugin is enabled
	 */
   	if (is_plugin_active( 'woocommerce-better-quick-view/woocommerce-better-quick-view.php' ) ) {
	   
	   define( 'wcva_quick_view_mode', 'on' );
	
	} else {
		
	   define( 'wcva_quick_view_mode', 'off' );
	   
	}
	
   /**
    * check weather woocommerce is active or not
    */

    if (is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
 
          
      require 'classes/class_create_variations_metabox.php';
		  require 'classes/class_override_woocommerce_variable_tamplate.php';
		  require 'classes/class_wcva_register_scripts_styles.php';
		  require 'classes/class_attribute_global_values.php';
		  require 'classes/class_shop_page_swatchs.php';
		  require 'includes/wcva_common_functions.php';
		  require 'includes/wcva_swatch_form_fields.php';
		  require 'includes/wcva_direct_variation_link.php';
		  require 'includes/wcva_add_layered_navigation_widget.php';
		  require 'includes/admin/class_add_plugin_settings_field.php';
		  
		  
 
    } else {
    
    /**
	 * Display Notice if woocommerce is not installed
	 */
     
     function wcva_installation_notice() {
         echo '<div class="updated" style="padding:15px; position:relative;"><a href="http://wordpress.org/plugins/woocommerce/">'.esc_html__('Woocommerce','wcva').'</a>  must be installed and activated before using this plugin. </div>';
       }

        add_action('admin_notices', 'wcva_installation_notice');
       return;

    }
	
	

	 
    /*
	 * Gets absolute path for plugin
	 */
    function wcva_plugin_path() {
  
       return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }
    
	/*
	 * Get woocommerce version 
	 */
	function wcva_get_woo_version_number() {
       
	   if ( ! function_exists( 'get_plugins' ) )
		 require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	
       
	   $plugin_folder = get_plugins( '/' . 'woocommerce' );
	   $plugin_file = 'woocommerce.php';
	
	
	   if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
		  return $plugin_folder[$plugin_file]['Version'];

	   } else {
	
		return NULL;
	   }
    }
	





register_activation_hook( __FILE__, 'wcva_subscriber_check_activation_hook' );
function wcva_subscriber_check_activation_hook() {
    set_transient( 'wcva-admin-notice-activation', true, 5 );
}

add_action( 'admin_notices', 'wcva_subscriber_check_activation_notice' );
function wcva_subscriber_check_activation_notice(){
     if( get_transient( 'wcva-admin-notice-activation' ) ){
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html__( 'Thanks for purchasing WooSwatches.To enable dashboard updates ', 'wcva' ); ?> <a href="https://woomatrix.com/knowledge-base/how-to-update-woocommerce-color-or-image-variation-swatches-plugin/"><?php echo esc_html__( 'Follow this', 'wcva' ); ?></a>.</p>
        </div>
        <?php
        delete_transient( 'wcva-admin-notice-activation' );
    }
}



function wcva_plugin_add_settings_link( $links ) {
    $settings_link1 = '<a href="https://woomatrix.com/knowledge-base/how-to-update-woocommerce-color-or-image-variation-swatches-plugin/">' . esc_html__( 'Enable dashboad updates','wcva' ) . '</a>';
    
    array_push( $links, $settings_link1 );
    
  	return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'wcva_plugin_add_settings_link' );



 
function wcva_plugin_row_meta( $links, $file ) {    
    if ( plugin_basename( __FILE__ ) == $file ) {
        $row_meta = array(
          'docs'    => '<a href="' . esc_url( 'https://woomatrix.com/knowledge-base/category/woocommerce-color-or-image-variation-swatches/' ) . '" target="_blank" aria-label="' . esc_html__( 'Docs', 'wcva' ) . '" style="color:green;">' . esc_html__( 'Docs', 'wcva' ) . '</a>',
          'support'    => '<a href="' . esc_url( 'https://woomatrix.com/support/' ) . '" target="_blank" aria-label="' . esc_html__( 'Support', 'wcva' ) . '" style="color:green;">' . esc_html__( 'Support', 'wcva' ) . '</a>'
        );

 
        return array_merge( $links, $row_meta );
    }
    return (array) $links;
}

add_filter( 'plugin_row_meta', 'wcva_plugin_row_meta', 10, 2 );

?>