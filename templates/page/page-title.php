<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $page_title ) :
	?>
	<h1 class="hp-page__title"><?php echo esc_html( $page_title ); ?></h1>
	<?php
endif;
