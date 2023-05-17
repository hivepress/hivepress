<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$image_count = count( (array) $listing->get_images__id() );
?>
<div class="hp-listing__image" data-component="carousel-slider" data-preview="false" data-url="<?php echo esc_url( hivepress()->router->get_url( 'listing_view_page', [ 'listing_id' => $listing->get_id() ] ) ); ?>">
	<?php
	if ( get_option( 'hp_listing_enable_image_preview' ) && $image_count > 1 ) :
		foreach ( $listing->get_images() as $image ) :
			if ( strpos( $image->get_mime_type(), 'video' ) === 0 ) :
				?>
				<video controls>
					<source src="<?php echo esc_url( $image->get_url() ); ?>#t=0.001" type="<?php echo esc_attr( $image->get_mime_type() ); ?>">
				</video>
			<?php else : ?>
				<img src="<?php echo esc_url( $image->get_url( 'hp_landscape_small' ) ); ?>" alt="<?php echo esc_attr( $listing->get_title() ); ?>" loading="lazy">
				<?php
			endif;
		endforeach;
	else :
		?>
		<a href="<?php echo esc_url( hivepress()->router->get_url( 'listing_view_page', [ 'listing_id' => $listing->get_id() ] ) ); ?>">
			<?php if ( $image_count >= 1 ) : ?>
				<img src="<?php echo esc_url( $listing->get_image__url( 'hp_landscape_small' ) ); ?>" alt="<?php echo esc_attr( $listing->get_title() ); ?>" loading="lazy">
			<?php else : ?>
				<img src="<?php echo esc_url( hivepress()->get_url() . '/assets/images/placeholders/image-landscape.svg' ); ?>" alt="<?php echo esc_attr( $listing->get_title() ); ?>" loading="lazy">
			<?php endif; ?>
		</a>
	<?php endif; ?>
</div>
