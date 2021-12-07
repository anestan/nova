<?php
/**
 * The template to display the reviewers meta data (name, verified owner, review date)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/review-meta.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $comment;
$settings = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
if ( '0' === $comment->comment_approved ) { ?>

    <p class="meta">
        <em class="woocommerce-review__awaiting-approval">
			<?php esc_html_e( 'Your review is awaiting approval', 'woocommerce-photo-reviews' ); ?>
        </em>
    </p>

	<?php
} else {
	?>
    <p class="meta">
        <strong class="woocommerce-review__author"><?php comment_author(); ?> </strong>
		<?php
		if ( 'yes' === get_option( 'woocommerce_review_rating_verification_label' ) && 1 == get_comment_meta( $comment->comment_ID, 'verified', true ) ) {
			if ( $settings->get_params( 'photo', 'verified' ) == 'badge' ) {
				echo '<em class="woocommerce-review__verified woocommerce-photo-reviews-verified ' . $settings->get_params( 'photo', 'verified_badge' ) . '"></em> ';
			} elseif ( $settings->get_params( 'photo', 'verified' ) == 'text' ) {
				echo '<em class="woocommerce-review__verified woocommerce-photo-reviews-verified">' . $settings->get_params( 'photo', 'verified_text' ) . '</em> ';
			} else {
				echo '<em class="woocommerce-review__verified woocommerce-photo-reviews-verified wcpr-icon-badge"></em>';
			}
		}
		?>
        <span class="woocommerce-review__dash">&ndash;</span>
        <time class="woocommerce-review__published-date"
              datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>"><?php echo esc_html( get_comment_date( wc_date_format() ) ); ?></time>
    </p>

	<?php
}
