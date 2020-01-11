<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->is_featured() ) :
	?>
	<div class="hp-listing__featured-badge" title="<?php echo esc_attr_x( 'Featured', 'listing', 'hivepress' ); ?>">
		<i class="hp-icon fas fa-star"></i>
	</div>
	<?php
endif;
