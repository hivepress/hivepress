<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $user->is_verified() ) :
	?>
	<i class="hp-vendor__verified-badge hp-icon fas fa-check-circle" title="<?php echo esc_attr_x( 'Verified', 'user', 'hivepress' ); ?>"></i>
	<?php
endif;
