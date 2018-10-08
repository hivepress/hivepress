<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $password_reset_key_valid ) :
	echo hivepress()->form->render_form(
		'user__reset_password',
		[
			'before'        => '<div class="hp-form__header">' . esc_html__( 'Please enter new password below.', 'hivepress' ) . '</div>',
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
else :
	?>
	<div class="hp-no-results">
		<p><?php esc_html_e( 'Password reset link is expired or invalid.', 'hivepress' ); ?></p>
	</div>
	<?php
endif;
