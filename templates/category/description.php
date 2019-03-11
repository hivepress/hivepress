<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $category->get_description() ) :
	?>
	<div class="hp-category__description"><?php echo esc_html( $category->get_description() ); ?></div>
	<?php
endif;
