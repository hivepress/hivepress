<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( get_post_meta( get_the_ID(), 'hp_verified', true ) ) :
	?>
	<i class="hp-listing__verified hp-icon fas fa-check-circle" title="<?php esc_attr_e( 'Verified', 'hivepress' ); ?>"></i>
	<?php
endif;
