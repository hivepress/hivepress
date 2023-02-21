<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$display = get_option( 'hp_user_enable_display' );

if ( $display ) : ?>
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'user_view_page', [ 'username' => $user->get_username() ] ) ); ?>">
	<?php
endif;

echo esc_html( $user->get_display_name() );

if ( $display ) :
	?>
	</a>
	<?php
endif;
