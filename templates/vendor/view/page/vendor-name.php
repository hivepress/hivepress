<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$online_users = (array) get_transient( 'online_users' );
?>
<span class="hp-vendor__online-status <?php echo in_array( $vendor->get_user__id(), array_keys( $online_users ) ) && $online_users[ $vendor->get_user__id() ] > time() - 60 ? 'online' : 'offline'; ?>"></span>
<span><?php echo esc_html( $vendor->get_name() ); ?></span>
