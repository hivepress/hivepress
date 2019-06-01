<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo hivepress()->listing->render_gallery(
	[
		'before' => '<div class="hp-listing__gallery hp-js-slider" data-type="gallery">',
		'after'  => '</div>',
	]
);
