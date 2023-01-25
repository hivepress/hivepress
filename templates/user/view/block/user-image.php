<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-vendor__image">
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'user_view_page', [ 'username' => $user->get_username() ] ) ); ?>">
		<?php echo get_avatar( $user->get_id(), 400 ); ?>
	</a>
</div>
