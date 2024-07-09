<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$online_users = (array) get_transient( 'online_users' );
?>
<span class="hp-vendor__online-status <?php echo in_array( $user->get_id(), array_keys( $online_users ) ) && $online_users[ $user->get_id() ] > time() - 60 ? 'online' : 'offline'; ?>"></span>
<span><?php echo esc_html( $user->get_display_name() ); ?></span>
