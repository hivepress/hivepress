<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->is_featured() ) :
	?>
	<div class="hp-listing__featured-badge hp-listing__featured" title="<?php echo esc_attr_x( 'Featured', 'listing', 'hivepress' ); ?>">
		<i class="hp-icon fas fa-star"></i>
	</div>
	<?php
endif;
