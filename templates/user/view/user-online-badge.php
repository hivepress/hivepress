<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<span class="hp-vendor__online-badge <?php if ( $user_online ) : ?>hp-vendor__online-badge--active<?php endif; ?>" title="<?php echo esc_attr( $user_online_status ); ?>"></span>
