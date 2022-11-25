<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
	<div class="hp-listing__images" data-component="carousel-slider">
		<?php foreach ( $listing->get_images__object() as $image ) :
			if ( strpos( $image->get_mime_type(), 'image' ) !== false ) : ?>
				<img src="<?php echo esc_url( $image->get_url( 'hp_landscape_large' ) ); ?>" data-src="<?php echo get_option( 'hp_listing_enable_image_zoom' ) ? esc_url( $image->get_url( 'large' ) ) : null; ?>" alt="<?php echo esc_attr( $listing->get_title() ); ?>" loading="lazy">
		<?php elseif ( strpos( $image->get_mime_type(), 'video' ) !== false ) : ?>
			<video data-src="<?php echo get_option( 'hp_listing_enable_image_zoom' ) ? esc_url( $image->get_url() ) : null; ?>" controls><source src="<?php echo esc_url( $image->get_url() ); ?>" type="<?php echo esc_html( $image->get_mime_type() ); ?>"></video>
			<?php endif; endforeach; ?>
	</div>
