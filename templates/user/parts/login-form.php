<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo hivepress()->form->render_form(
	'user__login',
	[
		'after'         => '<div class="hp-form__footer"><p>' . esc_html__( "Don't have an account?", 'hivepress' ) . '<a href="#hp-user-register" class="hp-js-link" data-type="popup"> ' . esc_html__( 'Register', 'hivepress' ) . '</a></p>
												<small><a href="#hp-user-request-password" class="hp-js-link" data-type="popup">' . esc_html__( 'Forgot password?', 'hivepress' ) . '</a></small></div>',
		'attributes'    => [
			'data - type' => 'ajax',
		],
		'submit_button' => [
			'attributes' => [
				'class' => 'alt',
			],
		],
	]
);
