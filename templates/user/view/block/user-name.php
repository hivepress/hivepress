<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$display = get_option( 'hp_user_enable_display' );
$online_users = (array) get_transient( 'online_users' );
?>

<span class="hp-vendor__online-status <?php echo in_array( $user->get_id(), array_keys( $online_users ) ) && $online_users[ $user->get_id() ] > time() - 60 ? 'online' : 'offline'; ?>"></span>

<?php
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
