<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( has_term( '', 'hp_listing_tag' ) ) :
	?>
	<div class="hp-listing__tags tagcloud">
		<?php the_terms( get_the_ID(), 'hp_listing_tag', '', '' ); ?>
	</div>
	<?php
endif;
