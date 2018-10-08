<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-page hp-page--listing-archive">
	<div class="hp-page__header"><?php echo hivepress()->template->render_area( 'listing_archive__header' ); ?></div>
	<div class="hp-row">
		<div class="hp-page__sidebar sidebar widget-area hp-sidebar hp-js-sticky hp-col-sm-4 hp-col-xs-12"><?php echo hivepress()->template->render_area( 'listing_archive__sidebar' ); ?></div>
		<div class="hp-page__content hp-col-sm-8 hp-col-xs-12">
			<?php echo hivepress()->template->render_area( 'listing_archive__content' ); ?>
		</div>
	</div>
</div>
