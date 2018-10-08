<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo hivepress()->form->render_form(
	'user__register',
	[
		'after'         => '<div class="hp-form__footer">' . esc_html__( 'Already have an account?', 'hivepress' ) . ' <a href="#hp-user-login" class="hp-js-link" data-type="popup">' . esc_html__( 'Sign In', 'hivepress' ) . '</a></div>',
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
