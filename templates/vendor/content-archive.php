<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-vendor hp-vendor--archive">
	<div class="hp-vendor__header"><?php echo hivepress()->template->render_area( 'archive_vendor__header' ); ?></div>
	<div class="hp-vendor__content"><?php echo hivepress()->template->render_area( 'archive_vendor__content' ); ?></div>
	<div class="hp-vendor__footer"><?php echo hivepress()->template->render_area( 'archive_vendor__footer' ); ?></div>
</div>
