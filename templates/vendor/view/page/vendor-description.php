<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $vendor->get_description() ) :
	?>
	<div class="hp-vendor__description"><?php the_content(); ?></div>
	<?php
endif;
