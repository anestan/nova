<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$prefix_class = $is_shortcode ? 'shortcode-' : '';
?>
<div class="<?php echo esc_attr($prefix_class); ?>wcpr-pagination">
	<?php
	if ( $paged - 1 > 1 && $pre) {
		?>
        <a href="<?php echo esc_url( add_query_arg( array( 'wcpr_page' => ( $paged - 1 ) ), $page_url ) ); ?>" class="wcpr-page-numbers wcpr-page-numbers-nav wcpr-page-numbers-pre"> <?php echo wp_kses_post($pre) ?> </a>
		<?php
	}
	if ( $paged > 2 ) {
		?>
		<a href="<?php echo esc_url( add_query_arg( array( 'wcpr_page' => 1 ), $page_url ) ); ?>" class="wcpr-page-numbers">1</a>
		<?php
		if ( $paged - 2 > 1 ) {
			?>
			<span class="wcpr-page-numbers wcpr-no-page">...</span>
			<?php
		}
	}
	if ( $paged - 1 > 0 ) {
		?>
		<a href="<?php echo esc_url( add_query_arg( array( 'wcpr_page' => ( $paged - 1 ) ), $page_url ) ); ?>" class="wcpr-page-numbers"><?php echo esc_html( $paged - 1 ); ?></a>
		<?php
	}
	echo sprintf(' <span class="wcpr-page-numbers wcpr-current">%s</span>',$paged);
	if ( $paged + 1 < $max_num_pages ) {
		?>
		<a href="<?php echo esc_url( add_query_arg( array( 'wcpr_page' => ( $paged + 1 ) ), $page_url ) ); ?>" class="wcpr-page-numbers"><?php echo esc_html( $paged + 1 ); ?></a>
		<?php
	}
	if ( $paged < $max_num_pages ) {
		if ( $paged < $max_num_pages - 2 ) {
			?>
			<span class="wcpr-page-numbers wcpr-no-page">...</span>
			<?php
		}
		?>
		<a href="<?php echo esc_url( add_query_arg( array( 'wcpr_page' => $max_num_pages ), $page_url ) ); ?>" class="wcpr-page-numbers"><?php echo esc_html($max_num_pages); ?></a>
		<?php
	}
	if ( $paged + 1 < $max_num_pages && $next ) {
		?>
        <a href="<?php echo esc_url( add_query_arg( array( 'wcpr_page' => ( $paged + 1 ) ), $page_url ) ); ?>" class="wcpr-page-numbers wcpr-page-numbers-nav wcpr-page-numbers-next"> <?php echo wp_kses_post($next) ?> </a>
		<?php
	}
	?>
</div>
