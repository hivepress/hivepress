<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( get_the_content() ) :
	?>
	<div class="hp-listing__description"><?php the_content(); ?></div>
	<?php
endif;
