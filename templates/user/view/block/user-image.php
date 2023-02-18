<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$display = get_option( 'hp_user_enable_display' );
?>
<div class="hp-vendor__image">
	<?php if ( $display ) : ?>
		<a href="<?php echo esc_url( hivepress()->router->get_url( 'user_view_page', [ 'username' => $user->get_username() ] ) ); ?>">
		<?php
	endif;

	echo get_avatar( $user->get_id(), 400 );

	if ( $display ) :
		?>
		</a>
	<?php endif; ?>
</div>
