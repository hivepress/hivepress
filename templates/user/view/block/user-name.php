<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$display = get_option( 'hp_user_enable_display' );

if ( get_option( 'hp_user_online_status' ) ) :
    $last_seen = $user->get_last_seen();
    ?>
    <span class="hp-activity-badge <?php echo $last_seen > time() ? 'hp-activity-badge--online' : ''; ?>" <?php echo $last_seen && $last_seen < time() ? 'title="' . sprintf( esc_html__( 'Last seen %s ago', 'hivepress' ), human_time_diff( time(), $last_seen ) ) . '"' : ''; ?>></span>
    <?php
endif;

if ( $display ) : ?>
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'user_view_page', [ 'username' => $user->get_username() ] ) ); ?>">
	<?php
endif;

echo esc_html( $user->get_display_name() );

if ( $display ) : ?>
	</a>
	<?php
endif;
