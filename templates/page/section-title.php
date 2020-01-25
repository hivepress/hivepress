<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $section_title ) :
	?>
	<h2 class="hp-section__title"><?php echo esc_html( $section_title ); ?></h2>
	<?php
endif;
