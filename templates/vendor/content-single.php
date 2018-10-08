<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-vendor hp-vendor--single">
	<div class="hp-row">
		<div class="hp-vendor__sidebar hp-sidebar sidebar widget-area hp-js-sticky hp-col-sm-4 hp-col-xs-12"><?php echo hivepress()->template->render_area( 'single_vendor__sidebar' ); ?></div>
		<div class="hp-vendor__content hp-col-sm-8 hp-col-xs-12"><?php echo hivepress()->template->render_area( 'single_vendor__content' ); ?></div>
	</div>
</div>
