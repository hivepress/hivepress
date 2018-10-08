<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-pagination">
	<?php
	the_posts_pagination(
		[
			'prev_text' => '<i class="fas fa-chevron-left"></i>',
			'next_text' => '<i class="fas fa-chevron-right"></i>',
		]
	);
	?>
</div>
