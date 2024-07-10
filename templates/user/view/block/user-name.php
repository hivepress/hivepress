<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$display = get_option( 'hp_user_enable_display' );
?>

<span class="hp-activity-badge <?php echo in_array( $user->get_id(), array_keys( (array) hivepress()->cache->get_cache( 'online_users' ) ) ) ? 'hp-activity-badge--online' : ''; ?>"></span>

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
