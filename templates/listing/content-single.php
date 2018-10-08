<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

the_post();
?>
<div class="hp-listing hp-listing--single">
	<div class="hp-row">
		<div class="hp-listing__content hp-col-sm-8 hp-col-xs-12"><?php echo hivepress()->template->render_area( 'single_listing__content' ); ?></div>
		<div class="hp-listing__sidebar hp-sidebar sidebar widget-area hp-js-sticky hp-col-sm-4 hp-col-xs-12"><?php echo hivepress()->template->render_area( 'single_listing__sidebar' ); ?></div>
	</div>
</div>
