<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-category">
	<div class="hp-category__header"><?php echo hivepress()->template->render_area( 'archive_category__header' ); ?></div>
	<div class="hp-category__content"><?php echo hivepress()->template->render_area( 'archive_category__content' ); ?></div>
</div>
