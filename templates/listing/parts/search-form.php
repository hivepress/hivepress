<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo hivepress()->form->render_form(
	'listing__search',
	[
		'submit_button' => [
			'attributes' => [
				'class' => 'alt',
			],
		],
	]
);
