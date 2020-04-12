<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $vendor->get_description() ) :
	?>
	<div class="hp-vendor__description"><?php echo $vendor->display_description(); ?></div>
	<?php
endif;
