<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_image_ids() ) :
	?>
	<div class="hp-listing__images" data-component="slider">
		<?php foreach ( $listing->get_image_urls( 'hp_landscape_large' ) as $image_url ) : ?>
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $listing->get_title() ); ?>">
		<?php endforeach; ?>
	</div>
	<?php
endif;
