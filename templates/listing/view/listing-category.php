<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( has_term( '', 'hp_listing_category' ) ) :
	?>
	<div class="hp-listing__category"><?php the_terms( $listing->get_id(), 'hp_listing_category', '', '' ); ?></div>
	<?php
endif;
