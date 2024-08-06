<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<span class="hp-activity-badge <?php echo hivepress()->cache->get_user_cache( $user->get_id(), 'last_seen' ) > time() ? 'hp-activity-badge--online' : ''; ?>"></span>
<span><?php echo esc_html( $user->get_display_name() ); ?></span>
