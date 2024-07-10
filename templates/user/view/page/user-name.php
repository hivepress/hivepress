<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<span class="hp-activity-badge <?php echo in_array( $user->get_id(), array_keys( (array) hivepress()->cache->get_cache( 'online_users' ) ) ) ? 'hp-activity-badge--online' : ''; ?>"></span>
<span><?php echo esc_html( $user->get_display_name() ); ?></span>
