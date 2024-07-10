<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<span class="hp-activity-badge <?php echo in_array( $vendor->get_user__id(), array_keys( (array) hivepress()->cache->get_cache( 'online_users' ) ) ) ? 'hp-activity-badge--online' : ''; ?>"></span>
<span><?php echo esc_html( $vendor->get_name() ); ?></span>
