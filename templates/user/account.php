<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-page hp-page--user-account">
	<div class="hp-row">
		<div class="hp-page__sidebar hp-sidebar sidebar widget-area hp-js-sticky hp-col-sm-4 hp-col-xs-12"><?php echo hivepress()->template->render_area( 'user_account__sidebar' ); ?></div>
		<div class="hp-page__content hp-col-sm-8 hp-col-xs-12"><?php echo hivepress()->template->render_area( 'user_account__content' ); ?></div>
	</div>
</div>
