<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo hivepress()->listing->render_attributes(
	'archive__secondary',
	[
		'before'           => '<div class="hp-listing__attributes"><div class="hp-row">',
		'after'            => '</div></div>',
		'before_attribute' => '<div class="hp-col-lg-6 hp-col-xs-12"><div class="hp-listing__attribute"><strong>%name%:</strong> <span>',
		'after_attribute'  => '</span></div></div>',
	]
);
