<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( hivepress()->request->get_context( 'page_title' ) ) :
	?>
	<h1 class="hp-page__title"><?php echo esc_html( hivepress()->request->get_context( 'page_title' ) ); ?></h1>
	<?php
endif;
