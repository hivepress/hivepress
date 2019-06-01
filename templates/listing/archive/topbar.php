<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( have_posts() ) :
	?>
	<div class="hp-page__topbar"><?php echo hivepress()->template->render_area( 'listing_archive__topbar' ); ?></div>
	<?php
endif;
