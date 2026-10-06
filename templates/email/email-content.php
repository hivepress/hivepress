<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( is_rtl() ) :
	?>
	<div dir="auto"><?php echo $email->get_body(); ?></div>
	<?php
else :
	echo $email->get_body();
endif;
