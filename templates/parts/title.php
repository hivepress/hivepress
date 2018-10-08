<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo hivepress()->template->render_title(
	[
		'before' => '<h1 class="hp-page__title">',
		'after'  => '</h1>',
	]
);
