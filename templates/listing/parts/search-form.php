<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo hivepress()->form->render_form(
	'listing__search',
	[
		'attributes'    => [
			'class' => 'hp-content hp-block hp-block--listing-search',
		],
		'submit_button' => [
			'attributes' => [
				'class' => 'alt',
			],
		],
	]
);
