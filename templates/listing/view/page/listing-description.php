<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_description() ) :
	?>
	<div class="hp-listing__description"><?php the_content(); ?></div>
	<?php
endif;
