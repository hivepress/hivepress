<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-vendor__image">
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'vendor_view_page', [ 'vendor_id' => $vendor->get_id() ] ) ); ?>">
		<?php if ( $vendor->get_image__url( 'hp_square_small' ) ) : ?>
			<img src="<?php echo esc_url( $vendor->get_image__url( 'hp_square_small' ) ); ?>" alt="<?php echo esc_attr( $vendor->get_name() ); ?>" loading="lazy">
		<?php else : ?>
			<img src="<?php echo esc_url( hivepress()->get_url() . '/assets/images/placeholders/user-square.svg' ); ?>" alt="<?php echo esc_attr( $vendor->get_name() ); ?>" loading="lazy">
		<?php endif; ?>
	</a>
</div>
