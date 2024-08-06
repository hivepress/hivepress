<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$display = get_option( 'hp_user_enable_display' );
?>

<span class="hp-activity-badge <?php echo hivepress()->cache->get_user_cache( $user->get_id(), 'last_seen' ) > time() ? 'hp-activity-badge--online' : ''; ?>"></span>

<?php if ( $display ) : ?>
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'user_view_page', [ 'username' => $user->get_username() ] ) ); ?>">
	<?php
endif;

echo esc_html( $user->get_display_name() );

if ( $display ) : ?>
	</a>
	<?php
endif;
