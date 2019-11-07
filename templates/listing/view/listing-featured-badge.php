<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->is_featured() ) :
	?>
	<div class="hp-listing__featured" title="<?php esc_attr_e( 'Featured', 'hivepress' ); ?>">
		<i class="hp-icon fas fa-star"></i>
	</div>
	<?php
endif;
