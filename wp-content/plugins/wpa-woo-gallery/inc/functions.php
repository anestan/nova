<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}  // if direct access


if ( ! function_exists( 'wpawg_body_classes' ) ) {

    function wpawg_body_classes( $classes ) {
        $get_theme = wp_get_theme();

        $current_theme = strtolower($get_theme->Name);
        $classes[] = 'wpawg-'.$current_theme;

        return $classes;
    }
    add_filter( 'body_class', 'wpawg_body_classes' );
}



/**
 * Remove default WooCommerce gallery zoom, Lightbox and slider
 */
if (! function_exists('wpawg_remove_woocommerce_default_support') ) {
    function wpawg_remove_woocommerce_default_support(){
        remove_theme_support( 'wc-product-gallery-zoom' );
        remove_theme_support( 'wc-product-gallery-slider' );
        remove_theme_support( 'wc-product-gallery-lightbox' );

        remove_theme_support( 'avia-wc-30-product-gallery-feature' );
    }
    add_action( 'after_setup_theme', 'wpawg_remove_woocommerce_default_support', 999 );
}


/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 *
 * @access public
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 */
if ( ! function_exists('wpa_wg_get_template') ) {

    function wpa_wg_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
        if ( ! empty( $args ) && is_array( $args ) ) {
            extract( $args ); // @codingStandardsIgnoreLine
        }

        $located = wpa_wg_locate_template( $template_name, $template_path, $default_path );

        if ( ! file_exists( $located ) ) {
            /* translators: %s template */
            wc_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( '%s does not exist.', 'wpa-gallery' ), '<code>' . $located . '</code>' ), '2.1' );
            return;
        }

        // Allow 3rd party plugin filter template file from their plugin.
        $located = apply_filters( 'wpa_wg_get_template', $located, $template_name, $args, $template_path, $default_path );

        do_action( 'wpa_wg_before_template_part', $template_name, $template_path, $located, $args );

        include $located;

        do_action( 'wpa_wg_after_template_part', $template_name, $template_path, $located, $args );
    }
}



/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @access public
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 * @return string
 */
    if (! function_exists('wpa_wg_locate_template') ) {
    function wpa_wg_locate_template( $template_name, $template_path = '', $default_path = '' ) {
        if ( ! $template_path ) {
            $template_path = WC()->template_path();
        }

        if ( ! $default_path ) {
            $default_path = WPA_WG_PATH . 'templates/';
        }

        // Look within passed path within the theme - this is priority.
        $template = locate_template(
            array(
                trailingslashit( $template_path ) . $template_name,
                $template_name,
            )
        );

        // Get default template/.
        if ( ! $template || WC_TEMPLATE_DEBUG_MODE ) {
            $template = $default_path . $template_name;
        }

        // Return what we found.
        return apply_filters( 'wpa_wg_locate_template', $template, $template_name, $template_path );
    }
}


/**
 * template redirect
 */

if (! function_exists('wpa_wg_product_gallery_template_redirect')) {
    function wpa_wg_product_gallery_template_redirect(){
        $get_theme = wp_get_theme();
        $current_theme = strtolower($get_theme->Name);

        remove_action ( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
        remove_action ( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
        remove_action( 'woocommerce_product_thumbnails', 'tonda_select_single_product_show_product_thumbnails', 19 );

        if ($current_theme == 'enfold') {
            remove_action( 'woocommerce_before_single_product_summary', 'avia_add_image_div', 2);
            remove_action( 'woocommerce_before_single_product_summary',  'avia_close_image_div', 20);
            remove_action('woocommerce_product_thumbnails', 'avia_product_gallery_thumbnail_opener', 19);
            remove_action('woocommerce_product_thumbnails',  'avia_close_div', 21);
            remove_action( 'woocommerce_init', 'avia_woocommerce_product_gallery_support_setup', 10);
        }
        
        add_action ( 'woocommerce_before_single_product_summary', 'wpa_wg_template_product_images', 20 );
        add_action ( 'woocommerce_product_thumbnails', 'wpa_wg_show_product_thumbnails', 20 );
    }

    add_action ( 'template_redirect', 'wpa_wg_product_gallery_template_redirect' );
}


/**
 * Change product-single.php template
 */

if (! function_exists('wpa_wg_template_product_images')) {
    function wpa_wg_template_product_images() {

        wpa_wg_get_template( 'product-image.php', array(), '', WPA_WG_PATH . 'templates/' );
    }
}


/**
 * Change product-thumbnails.php template
 */
if (! function_exists('wpa_wg_show_product_thumbnails') ) {
    function wpa_wg_show_product_thumbnails() {
        wpa_wg_get_template( 'product-thumbnails.php', array(), '', WPA_WG_PATH . 'templates/' );
    }
}


/**
 * overrides gallery image html function
 */
if (! function_exists('wpa_wg_get_gallery_image_html') ) {
    function wpa_wg_get_gallery_image_html( $attachment_id, $main_image = false ) {
        $wpawg_image_popup_color = get_option( 'wpawg_image_popup_color' );
        $wpawg_video_popup_color = get_option( 'wpawg_video_popup_color' );

        $gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );
        $thumbnail_size    = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
        $image_size        = apply_filters( 'woocommerce_gallery_image_size', $main_image ? 'woocommerce_single' : $thumbnail_size );
        $full_size         = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
        $thumbnail_src     = wp_get_attachment_image_src( $attachment_id, $thumbnail_size );
        $full_src          = wp_get_attachment_image_src( $attachment_id, $full_size );
        $image             = wp_get_attachment_image( $attachment_id, $image_size, false, array(
            'title'                   => get_post_field( 'post_title', $attachment_id ),
            'data-caption'            => get_post_field( 'post_excerpt', $attachment_id ),
            'data-src'                => $full_src[0],
            'data-large_image'        => $full_src[0],
            'data-large_image_width'  => $full_src[1],
            'data-large_image_height' => $full_src[2],
            'class'                   => $main_image ? 'wp-post-image' : '',
        ) );

        $video_url = get_post_meta($attachment_id, 'wpa_gallery_video', true);
        $popup_video = $popup_image = '';

        $wpawg_image_popup = (get_option( 'wpawg_image_popup' ) ? get_option( 'wpawg_image_popup' ) : 'yes');
        $wpawg_video_popup = (get_option( 'wpawg_video_popup' ) ? get_option( 'wpawg_video_popup' ) : 'yes');

        if ($video_url && $wpawg_video_popup == 'yes') {
            $popup_video = '<span class="wpawg-video-popup" data-mfp-src="'. esc_url($video_url) .'" href="#" style="color: '.esc_attr($wpawg_video_popup_color).'"><i class="flaticon-play-button"></i></span>';
        }

        if ($wpawg_image_popup == 'yes') {
            $popup_image = '<span class="wpawg-image-popup" data-mfp-src="'. esc_url($full_src[0]) .'" href="#" style="color: '.esc_attr($wpawg_image_popup_color).'"><i class="flaticon-add"></i></span>';
        }
        
        return '<div data-thumb="' . esc_url( $thumbnail_src[0] ) . '" class="wpa-woocommerce-product-gallery__image">' . $image . ' ' . $popup_video . $popup_image .'</div>';
    }
}


/**
 * Image sizes
 */

if ( ! function_exists( 'wpa_wg_shop_single_image_size' ) ):
    function wpa_wg_shop_single_image_size( $size ) {
        $hardcrop = 1;
        $default_width = (get_option('wpawg_large_image_width') ? get_option('wpawg_large_image_width') : 600);
        $default_height = (get_option('wpawg_large_image_height') ? get_option('wpawg_large_image_height') : 600);
        $hardcrop_option = (get_option('wpawg_large_hardcrop') ? get_option('wpawg_large_hardcrop') : 'yes');

        if ($hardcrop_option == 'no') {
            $hardcrop = 0;
        }

        $size[ 'width' ]  = $default_width;
        $size[ 'height' ] = $default_height;
        $size[ 'crop' ]   = $hardcrop;

        return $size;
    }
    add_filter( 'woocommerce_get_image_size_single', 'wpa_wg_shop_single_image_size' );
endif;


if ( ! function_exists( 'wpa_wg_shop_gallery_thumbnail_size' ) ):
    function wpa_wg_shop_gallery_thumbnail_size( $size ) {
        $hardcrop = 1;
        $default_width = (get_option('wpawg_thumbnail_image_width') ? get_option('wpawg_thumbnail_image_width') : 110);
        $default_height = (get_option('wpawg_thumbnail_image_height') ? get_option('wpawg_thumbnail_image_height') : 110);
        $hardcrop_option = (get_option('wpawg_thumbnail_hardcrop') ? get_option('wpawg_thumbnail_hardcrop') : 'yes');

        if ($hardcrop_option == 'no') {
            $hardcrop = 0;
        }

        $size[ 'width' ]  = $default_width;
        $size[ 'height' ] = $default_height;
        $size[ 'crop' ]   = $hardcrop;

        return $size;
    }
    add_filter( 'woocommerce_get_image_size_gallery_thumbnail', 'wpa_wg_shop_gallery_thumbnail_size' );
endif;