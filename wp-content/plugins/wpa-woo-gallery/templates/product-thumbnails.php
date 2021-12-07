<?php
/**
 * Single Product Thumbnails
 *
 */

defined( 'ABSPATH' ) || exit;

// Note: `wpa_wg_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wpa_wg_get_gallery_image_html' ) ) {
	return;
}

global $product;

$attachment_ids = $product->get_gallery_image_ids();

if ( $attachment_ids && has_post_thumbnail() ) {
	if ( $attachment_ids && has_post_thumbnail() ) {
		foreach ( $attachment_ids as $attachment_id ) {
			echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', wpa_wg_get_gallery_image_html( $attachment_id, false  ), $attachment_id );
		}
	}
}