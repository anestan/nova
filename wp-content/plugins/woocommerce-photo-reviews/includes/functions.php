<?php
/**
 * Function include all files in folder
 *
 * @param $path   Directory address
 * @param $ext    array file extension what will include
 * @param $prefix string Class prefix
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! function_exists( 'vi_include_folder' ) ) {
	function vi_include_folder( $path, $prefix = '', $ext = array( 'php' ) ) {

		/*Include all files in payment folder*/
		if ( ! is_array( $ext ) ) {
			$ext = explode( ',', $ext );
			$ext = array_map( 'trim', $ext );
		}
		$sfiles = scandir( $path );
		foreach ( $sfiles as $sfile ) {
			if ( $sfile != '.' && $sfile != '..' ) {
				if ( is_file( $path . "/" . $sfile ) ) {
					$ext_file  = pathinfo( $path . "/" . $sfile );
					$file_name = $ext_file['filename'];
					if ( $ext_file['extension'] ) {
						if ( in_array( $ext_file['extension'], $ext ) ) {
							$class = preg_replace( '/\W/i', '_', $prefix . ucfirst( $file_name ) );

							if ( ! class_exists( $class ) ) {
								require_once $path . $sfile;
								if ( class_exists( $class ) ) {
									new $class;
								}
							}
						}
					}
				}
			}
		}
	}
}
if ( ! function_exists( 'vi_wcpr_handle_sideload' ) ) {
	/**
	 * @param $url
	 * @param $comment_id
	 * @param $post_id
	 *
	 * @return int|string|WP_Error
	 */
	function vi_wcpr_handle_sideload( $url, $comment_id, $post_id ) {
		//add product image:
		add_filter( 'big_image_size_threshold', '__return_false',999 );
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
		}
		$parse_url = wp_parse_url( $url );
		$scheme    = empty( $parse_url['scheme'] ) ? 'http' : $parse_url['scheme'];
		$url       = "{$scheme}://{$parse_url['host']}{$parse_url['path']}";
		// Download file to temp location
		$tmp = download_url( $url );
		// Set variables for storage
		// fix file name for query strings
		preg_match( '/[^\?]+\.(jpg|JPG|jpeg|JPEG|jpe|JPE|gif|GIF|png|PNG)/', $url, $matches );
		$file_array['name']     = apply_filters( 'woocommerce_photo_reviews_image_file_name', basename( $matches[0] ), $comment_id, $post_id, true );
		$file_array['tmp_name'] = $tmp;
		// If error storing temporarily, unlink
		if ( is_wp_error( $tmp ) ) {
			@unlink( $file_array['tmp_name'] );

			return $tmp;
		}
		//use media_handle_sideload to upload img:
		$thumbid = media_handle_sideload( $file_array, '' );
		// If error storing permanently, unlink
		if ( is_wp_error( $thumbid ) ) {
			@unlink( $file_array['tmp_name'] );
		}

		return $thumbid;
	}
}
if ( ! function_exists( 'woocommerce_version_check' ) ) {
	function woocommerce_version_check( $version = '3.0' ) {
		global $woocommerce;

		if ( version_compare( $woocommerce->version, $version, ">=" ) ) {
			return true;
		}

		return false;
	}
}
if ( ! function_exists( '_sort_priority_callback' ) ) {
	/**
	 * Sort Priority Callback Function
	 *
	 * @param array $a Comparison A.
	 * @param array $b Comparison B.
	 *
	 * @return bool
	 */
	function _sort_priority_callback( $a, $b ) {
		if ( ! isset( $a['priority'], $b['priority'] ) || $a['priority'] === $b['priority'] ) {
			return 0;
		}

		return ( $a['priority'] < $b['priority'] ) ? - 1 : 1;
	}
}