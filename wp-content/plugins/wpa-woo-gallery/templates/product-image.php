<?php
/**
 * Single Product Image
 *
 */

defined( 'ABSPATH' ) || exit;

// Note: `wpa_wg_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wpa_wg_get_gallery_image_html' ) ) {
	return;
}

global $product;

$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
$post_thumbnail_id = $product->get_image_id();
$attachment_ids = $product->get_gallery_image_ids();

$slider_class = $vartical_gallery = $gallery_position_left = $gallery_position_right = $wpa_gallery_layout = '';

$wpawg_vartical_layout = 'false';

$wpawg_layout = (get_option( 'wpawg_gallery_layout' ) ? get_option( 'wpawg_gallery_layout' ) : 'wpa-gallery-default' );
$wpawg_zoom = (get_option( 'wpawg_image_zoom' ) ? get_option( 'wpawg_image_zoom' ) : 'yes');
$wpawg_thumbnails = (get_option( 'wpawg_thumbnails' ) ? get_option( 'wpawg_thumbnails' ) : '3');
$wpawg_navigation = (get_option( 'wpawg_navigation' ) ? get_option( 'wpawg_navigation' ) : 'yes');
$wpawg_autoplay = (get_option( 'wpawg_autoplay' ) ? get_option( 'wpawg_autoplay' ) : 'no');
$wpawg_autoplay_speed = (get_option( 'wpawg_autoplay_speed' ) ? get_option( 'wpawg_autoplay_speed' ) : '2000');
$wpawg_centermode = (get_option( 'wpawg_centermode' ) ? get_option( 'wpawg_centermode' ) : 'yes');
$wpawg_adaptive_height = (get_option( 'wpawg_adaptive_height' ) ? get_option( 'wpawg_adaptive_height' ) : 'yes');
$wpawg_navigation_color = get_option( 'wpawg_navigation_color' );


// product layout for specific product
$get_meta = get_post_meta( get_the_ID(), 'wpawg', true );
$unserialize_data = WPA_Woo_Gallery::unserialize_and_decode24($get_meta);
$meta_options = is_array($unserialize_data) ? $unserialize_data : array();
extract($meta_options);

if ($attachment_ids) {
	$slider_class = 'wpa-product-gallery';

	if ($wpa_gallery_layout == 'wpa_gallery_default_settings' || empty($wpa_gallery_layout)) {
		if ($wpawg_layout == 'wpa-gallery-position-left') {
			$vartical_gallery = 'wpa-vertical-gallery';
			$gallery_position_left = 'wpa-gallery-position-left';
		} elseif($wpawg_layout == 'wpa-gallery-position-right') {
			$vartical_gallery = 'wpa-vertical-gallery';
			$gallery_position_right = 'wpa-gallery-position-right';
		}
	} elseif($wpa_gallery_layout == 'wpa_gallery_position_left'){
		$vartical_gallery = 'wpa-vertical-gallery';
		$gallery_position_left = 'wpa-gallery-position-left';
	} elseif($wpa_gallery_layout == 'wpa_gallery_position_right'){
		$vartical_gallery = 'wpa-vertical-gallery';
		$gallery_position_right = 'wpa-gallery-position-right';
	}
	
} else {
	$slider_class = 'wpa-product-single-image';
}

$wrapper_classes   = apply_filters( 'woocommerce_single_product_image_gallery_classes', array(
	'wpa-woocommerce-product-gallery',
	'wpa-woocommerce-product-gallery--' . ( has_post_thumbnail() ? 'with-images' : 'without-images' ),
	'wpa-woocommerce-product-gallery--columns-' . absint( $columns ),
	'images',
	$vartical_gallery,
	$gallery_position_left,
	$gallery_position_right
) );

wp_enqueue_style('slick' );
wp_enqueue_style('slick-theme' );
wp_enqueue_style('magnific-popup' );
wp_enqueue_script('magnific-popup' );
wp_enqueue_script('slick' );


if ($wpawg_zoom == 'yes') {
	wp_enqueue_script('wpawg-zoom' );
}

wp_enqueue_style('wpawg-flaticon' );


// thumbnails position
if ($wpa_gallery_layout == 'wpa_gallery_default_settings' || empty($wpa_gallery_layout)) {
	if ($wpawg_layout == 'wpa-gallery-position-left' || $wpawg_layout == 'wpa-gallery-position-right') {
		$wpawg_vartical_layout = 'true';
	} else {
		$wpawg_vartical_layout = 'false';
	}
} elseif($wpa_gallery_layout == 'wpa_gallery_position_left' || $wpa_gallery_layout == 'wpa_gallery_position_right'){
	$wpawg_vartical_layout = 'true';
} else {
	$wpawg_vartical_layout = 'false';
}


$wpawg_gallery_type = (get_option( 'wpawg_gallery_type' ) ? get_option( 'wpawg_gallery_type' ) : 'wpawg_gallery_featured');

?>
<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>" style="opacity: 0; transition: opacity .25s ease-in-out;" 
	data-vertical-layout="<?php echo esc_attr($wpawg_vartical_layout); ?>"
	data-zoom="<?php echo esc_attr($wpawg_zoom); ?>"
	data-thumbnails="<?php echo esc_attr($wpawg_thumbnails); ?>"
	data-wpa-navigation="<?php echo esc_attr($wpawg_navigation); ?>"
	data-autoplay="<?php echo esc_attr($wpawg_autoplay); ?>"
	data-autoplay-speed="<?php echo esc_attr($wpawg_autoplay_speed); ?>"
	data-centermode="<?php echo esc_attr($wpawg_centermode); ?>"
	data-adaptive-height="<?php echo esc_attr($wpawg_adaptive_height); ?>"
	data-navigation-color="<?php echo esc_attr($wpawg_navigation_color); ?>"
	>

	<figure class="wpa-woocommerce-product-gallery__wrapper <?php echo esc_attr($slider_class); ?>">
		<?php
		if ( has_post_thumbnail() ) {
			$html  = wpa_wg_get_gallery_image_html( $post_thumbnail_id, true );
		} else {
			$html  = '<div class="woocommerce-product-gallery__image--placeholder">';
			$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src() ), esc_html__( 'Awaiting product image', 'wpa-gallery' ) );
			$html .= '</div>';
		}

		if ($wpawg_gallery_type == 'wpawg_gallery_featured' && $attachment_ids) {
			echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id );
		} elseif(! $attachment_ids) {
			echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id );
		}
		
		if ( $attachment_ids && has_post_thumbnail() ) {
			foreach ( $attachment_ids as $attachment_id ) {
				echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', wpa_wg_get_gallery_image_html( $attachment_id, true  ), $attachment_id );
			}
		} ?>
	</figure>

	<?php if ($attachment_ids): ?>
		<figure class="wpa-woocommerce-product-gallery__wrapper wpa-product-gallery-thumbs">
			
			<?php if ($wpawg_gallery_type == 'wpawg_gallery_featured') :
				if ( has_post_thumbnail() ) {
					$html  = wpa_wg_get_gallery_image_html( $post_thumbnail_id, false );
				} else {
					$html  = '<div class="woocommerce-product-gallery__image--placeholder">';
					$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src() ), esc_html__( 'Awaiting product image', 'wpa-gallery' ) );
					$html .= '</div>';
				}
				echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $attachment_ids );
			endif;

			do_action( 'woocommerce_product_thumbnails' ); ?>
		</figure>
	<?php endif; ?>
</div>