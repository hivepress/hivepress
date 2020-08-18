<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-listing-category__image">
	<a href="<?php echo esc_url( $listing_category_url ); ?>">
		<?php if ( $listing_category->get_image__url( 'hp_landscape_small' ) ) : ?>
			<img src="<?php echo esc_url( $listing_category->get_image__url( 'hp_landscape_small' ) ); ?>" alt="<?php echo esc_attr( $listing_category->get_name() ); ?>" loading="lazy">
		<?php else : ?>
			<img src="<?php echo esc_url( hivepress()->get_url() . '/assets/images/placeholders/image-landscape.svg' ); ?>" alt="<?php echo esc_attr( $listing_category->get_name() ); ?>" loading="lazy">
		<?php endif; ?>
	</a>
</div>
