<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-listing hp-listing--archive">
	<div class="hp-listing__header"><?php echo hivepress()->template->render_area( 'archive_listing__header' ); ?></div>
	<div class="hp-listing__content"><?php echo hivepress()->template->render_area( 'archive_listing__content' ); ?></div>
	<div class="hp-listing__footer"><?php echo hivepress()->template->render_area( 'archive_listing__footer' ); ?></div>
</div>
