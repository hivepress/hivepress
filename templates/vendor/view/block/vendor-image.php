<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-vendor__image">
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'vendor_view_page', [ 'vendor_id' => $vendor->get_id() ] ) ); ?>">
		<?php
		if ( has_post_thumbnail() ) :
			the_post_thumbnail( 'hp_square_small' );
		else :
			?>
			<img src="<?php echo esc_url( HP_CORE_URL . '/assets/images/placeholders/user-square.svg' ); ?>" alt="<?php echo esc_attr( $vendor->get_name() ); ?>">
		<?php endif; ?>
	</a>
</div>
