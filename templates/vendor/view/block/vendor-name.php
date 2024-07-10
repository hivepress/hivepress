<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<span class="hp-activity-badge <?php echo in_array( $vendor->get_user__id(), array_keys( (array) hivepress()->cache->get_cache( 'online_users' ) ) ) ? 'hp-activity-badge--online' : ''; ?>"></span>
<a href="<?php echo esc_url( hivepress()->router->get_url( 'vendor_view_page', [ 'vendor_id' => $vendor->get_id() ] ) ); ?>"><?php echo esc_html( $vendor->get_name() ); ?></a>
