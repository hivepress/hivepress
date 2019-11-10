<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->is_verified() ) :
	?>
	<i class="hp-listing__verified hp-icon fas fa-check-circle" title="<?php esc_attr_e( 'Verified', 'hivepress' ); ?>"></i>
	<?php
endif;
