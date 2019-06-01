<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo hivepress()->form->render_form(
	'user__request_password',
	[
		'before'        => '<div class="hp-form__header">' . esc_html__( 'Please enter your username or email address, you will receive a link to create a new password via email.', 'hivepress' ) . '</div>',
		'attributes'    => [
			'data-type' => 'ajax',
		],
		'submit_button' => [
			'attributes' => [
				'class' => 'alt',
			],
		],
	]
);
