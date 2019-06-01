<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo hivepress()->listing->render_category(
	[
		'before' => '<div class="hp-listing__category">',
		'after'  => '</div>',
	]
);
