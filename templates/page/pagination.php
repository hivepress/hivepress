<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<nav class="hp-pagination">
	<?php
	the_posts_pagination(
		[
			'prev_text' => '<i class="hp-icon fas fa-chevron-left"></i>',
			'next_text' => '<i class="hp-icon fas fa-chevron-right"></i>',
		]
	);
	?>
</nav>
