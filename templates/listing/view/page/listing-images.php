<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_images__url( 'hp_landscape_large' ) ) :
	?>
	<div class="hp-listing__images" data-component="carousel-slider">
		<?php foreach ( $listing->get_images__url( 'hp_landscape_large' ) as $image_url ) : ?>
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $listing->get_title() ); ?>" loading="lazy">
		<?php endforeach; ?>
	</div>
	<?php
endif;
