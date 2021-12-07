<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( (! $product_id && !$is_shortcode)  || empty( $settings ) ) {
	return;
}
$prefix_class     = $is_shortcode ? 'shortcode-' : '';
$image_class      = array(
	$prefix_class . 'wcpr-filter-button',
	$prefix_class . 'wcpr-filter-button-images',
);
$image_class[]    = $query_image ? $prefix_class . 'wcpr-active' : '';
$verified_class   = array( $prefix_class . 'wcpr-filter-button' );
$verified_class[] = $settings->get_params( 'photo', 'verified' ) == 'badge' ? $settings->get_params( 'photo', 'verified_badge' ) : $prefix_class . 'wcpr-filter-button-verified';
$verified_class[] = $query_verified ? $prefix_class . 'wcpr-active' : '';
if ( $is_shortcode ) {
	$image_link =( $query_image ? esc_url( remove_query_arg( array( 'wcpr_image' ), $product_link ) ) :
        esc_url( add_query_arg( array( 'wcpr_image' => true ), remove_query_arg( array( 'wcpr_page' ), $product_link ) ) ) );
	$verified_link = ( $query_verified ? esc_url( remove_query_arg( array( 'wcpr_verified' ), $product_link ) ) :
        esc_url( add_query_arg( array( 'wcpr_verified' => true ), remove_query_arg( array( 'wcpr_page' ), $product_link ) ) ) );
	$all_stars_url = esc_url( remove_query_arg( array( 'wcpr_rating' ), remove_query_arg( array( 'wcpr_page' ), $product_link ) ) );
} else {
	$image_link    = ( $query_image ? remove_query_arg( array( 'image', 'offset', 'cpage' ), $product_link1 ) :
			add_query_arg( array( 'image' => true ), remove_query_arg( array( 'page', 'offset', 'cpage' ), $product_link1 ) ) ) . $anchor_link;
	$verified_link = ( $query_verified ? remove_query_arg( array( 'verified', 'offset', 'cpage' ), $product_link1 ) :
			add_query_arg( array( 'verified' => true ), remove_query_arg( array( 'page', 'offset', 'cpage' ), $product_link1 ) ) ) . $anchor_link;
	$all_stars_url = $query_rating ? $product_link1 : $product_link;
	$all_stars_url = remove_query_arg( array( 'rating' ), remove_query_arg( array( 'page' ), $all_stars_url ) ) . $anchor_link;
}
$rating_wrap_class = array(
	$prefix_class . 'wcpr-filter-button-wrap',
	$prefix_class . 'wcpr-filter-button',
	$prefix_class . 'wcpr-active',
);
?>
<div class="<?php echo esc_attr( $prefix_class ); ?>wcpr-filter-container" <?php echo wp_kses_post( $prefix_class ?'': 'style="display: none;"' ); ?>>
    <a data-filter_type="image" class="<?php echo esc_attr( trim( implode( ' ', $image_class ) ) ); ?>" rel="nofollow" href="<?php echo esc_url( $image_link ); ?>">
		<?php esc_html_e( 'With images', 'woocommerce-photo-reviews' ); ?>
        (<span class="<?php echo esc_attr( $prefix_class ); ?>wcpr-filter-button-count"><?php echo esc_html( $count_images ); ?></span>)
    </a>
    <a data-filter_type="verified" class="<?php echo esc_attr( trim( implode( ' ', $verified_class ) ) ); ?>" rel="nofollow" href="<?php echo esc_url( $verified_link ); ?>">
		<?php esc_html_e( 'Verified', 'woocommerce-photo-reviews' ); ?>
        (<span class="<?php echo esc_attr( $prefix_class ); ?>wcpr-filter-button-count"><?php echo esc_html( $count_verified ); ?></span>)
    </a>
    <div class="<?php echo esc_attr( trim( implode( ' ', $rating_wrap_class ) ) ); ?>">
		<span class="<?php echo esc_attr( $prefix_class ); ?>wcpr-filter-rating-placeholder">
            <?php
            if ( $query_rating > 0 && $query_rating < 6 ) {
	            echo sprintf( _n( '%s star', '%s stars', $query_rating, 'woocommerce-photo-reviews' ), $query_rating );
	            echo sprintf( '(<span class="%swcpr-filter-button-count">%s</span>)',
		            $prefix_class,$star_counts[$query_rating] ??  VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend::stars_count( $query_rating, $product_id ) );
            } else {
	            esc_html_e( 'All stars', 'woocommerce-photo-reviews' );
	            echo sprintf( '(<span class="%swcpr-filter-button-count">%s</span>)', $prefix_class, $count_reviews );
            }
            ?>
		</span>
        <ul class="<?php echo esc_attr( $prefix_class ); ?>wcpr-filter-button-ul">
            <li class="<?php echo esc_attr( $prefix_class ); ?>wcpr-filter-button-li">
                <?php
                $all_star_class = array($prefix_class . 'wcpr-filter-button') ;
                $all_star_class[] = $query_rating ? '' : $prefix_class . 'wcpr-active' ;
                $all_star_class =implode(' ', $all_star_class );
                ?>
                <a data-filter_type="all" class="<?php echo esc_attr( trim( $all_star_class) ); ?>"
                   href="<?php echo esc_url( $all_stars_url ) ?>">
					<?php
					esc_html_e( 'All stars', 'woocommerce-photo-reviews' );
					echo sprintf( '(<span class="%swcpr-filter-button-count">%s</span>)', $prefix_class, $count_reviews );
					?>
                </a>
				<?php
				for ( $i = 5; $i > 0; $i -- ) {
				    $new_star_class=array( $prefix_class . 'wcpr-filter-button');
				    $new_star_class[]=( $query_rating && $query_rating == $i ) ? $prefix_class . 'wcpr-active' : '';
					$filter_rating_url = $query_rating && !empty($product_link1) ? $product_link1 : $product_link;
					echo sprintf( '<li class="%swcpr-filter-button-li"><a data-filter_type="%s" class="%s" rel="nofollow" href="%s">%s(<span class="%swcpr-filter-button-count">%s</span>)</a></li>',
						$prefix_class, $i,
						esc_attr( trim(  implode(' ',$new_star_class) ) ),
						$is_shortcode?( ( $query_rating && $query_rating == $i ) ? esc_url( remove_query_arg( array( 'wcpr_rating' ), $filter_rating_url ) ) :
                            esc_url( add_query_arg( array( 'wcpr_rating' => $i ), remove_query_arg( array( 'wcpr_page' ), $filter_rating_url ) ) ) ):( ( ( $query_rating && $query_rating == $i ) ?
                                esc_url( remove_query_arg( array( 'rating', 'offset', 'cpage' ), $filter_rating_url ) ) :
                                add_query_arg( array( 'rating' => $i ), remove_query_arg( array( 'page', 'offset', 'cpage' ), $filter_rating_url ) ) )  . $anchor_link ),
						sprintf( _n( '%s star', '%s stars', $i, 'woocommerce-photo-reviews' ), $i ),$prefix_class,
						$star_counts[$i] ?? VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend::stars_count( $i, $product_id )
					);
				}
				?>
            </li>
        </ul>
    </div>
    <?php
    if (!$is_shortcode){
        echo sprintf('<div class="wcpr-filter-overlay"></div>');
    }
    ?>
</div>
