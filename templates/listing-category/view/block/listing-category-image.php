<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-listing-category__image">
	<a href="<?php echo esc_url( $listing_category->get_url() ); ?>">
		<?php if ( $listing_category->get_image__url() ) : ?>
			<img src="<?php echo esc_url( $listing_category->get_image__url( 'hp_landscape_small' ) ); ?>" alt="<?php echo esc_attr( $listing_category->get_name() ); ?>">
		<?php else : ?>
			<img src="<?php echo esc_url( HP_CORE_URL . '/assets/images/placeholders/image-landscape.svg' ); ?>" alt="<?php echo esc_attr( $listing_category->get_name() ); ?>">
		<?php endif; ?>
	</a>
</div>
