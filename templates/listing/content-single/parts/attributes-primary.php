<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo hivepress()->listing->render_attributes(
	'single__primary',
	[
		'before'           => '<div class="hp-listing__attributes">',
		'after'            => '</div>',
		'before_attribute' => '<div class="hp-listing__attribute">',
		'after_attribute'  => '</div>',
	]
);
