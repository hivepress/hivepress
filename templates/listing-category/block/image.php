<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-listing-category__image">
	<a href="<?php echo esc_url( $category->get_url() ); ?>">
		<?php if ( $category->get_image_id() ) : ?>
			<img src="<?php echo esc_url( $category->get_image_url( 'hp_landscape_small' ) ); ?>" alt="<?php echo esc_attr( $category->get_name() ); ?>">
		<?php else : ?>
			<img src="<?php echo esc_url( HP_CORE_URL . '/assets/images/placeholder.png' ); ?>" alt="<?php echo esc_attr( $category->get_name() ); ?>">
		<?php endif; ?>
	</a>
</div>
