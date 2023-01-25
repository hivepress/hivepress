<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $user->get_description() ) :
	?>
	<div class="hp-vendor__description"><?php echo $user->display_description(); ?></div>
	<?php
endif;
