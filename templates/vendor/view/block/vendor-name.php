<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$last_seen = $vendor->get_user__last_seen();
?>
<span class="hp-activity-badge <?php echo $last_seen > time() ? 'hp-activity-badge--online' : ''; ?>" <?php echo $last_seen && $last_seen < time() ? 'title="' . sprintf( esc_html__( 'Last seen %s ago', 'hivepress' ), human_time_diff( time(), $last_seen ) ) . '"' : ''; ?>></span>
<a href="<?php echo esc_url( hivepress()->router->get_url( 'vendor_view_page', [ 'vendor_id' => $vendor->get_id() ] ) ); ?>"><?php echo esc_html( $vendor->get_name() ); ?></a>
