<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-listing__image">
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'listing_view_page', [ 'listing_id' => $listing->get_id() ] ) ); ?>">
		<?php if ( $listing->get_image__url( 'hp_landscape_small' ) ) : ?>
			<img src="<?php echo esc_url( $listing->get_image__url( 'hp_landscape_small' ) ); ?>" alt="<?php echo esc_attr( $listing->get_title() ); ?>" loading="lazy">
		<?php else : ?>
			<img src="<?php echo esc_url( hivepress()->get_url() . '/assets/images/placeholders/image-landscape.svg' ); ?>" alt="<?php echo esc_attr( $listing->get_title() ); ?>" loading="lazy">
		<?php endif; ?>
	</a>
</div>
