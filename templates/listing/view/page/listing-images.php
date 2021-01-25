<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_images__url( 'hp_landscape_large' ) ) :
	$image_urls = [];

	if ( get_option( 'hp_listing_enable_image_zoom' ) ) :
		$image_urls = $listing->get_images__url( 'large' );
	endif;
	?>
	<div class="hp-listing__images" data-component="carousel-slider">
		<?php foreach ( $listing->get_images__url( 'hp_landscape_large' ) as $image_index => $image_url ) : ?>
			<img src="<?php echo esc_url( $image_url ); ?>" data-src="<?php echo esc_url( hivepress()->helper->get_array_value( $image_urls, $image_index ) ); ?>" alt="<?php echo esc_attr( $listing->get_title() ); ?>" loading="lazy">
		<?php endforeach; ?>
	</div>
	<?php
endif;
