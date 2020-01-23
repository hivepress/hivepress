<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->is_verified() ) :
	?>
	<i class="hp-listing__verified-badge hp-listing__verified hp-icon fas fa-check-circle" title="<?php echo esc_attr_x( 'Verified', 'listing', 'hivepress' ); ?>"></i>
	<?php
endif;
