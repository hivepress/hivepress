<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo hivepress()->form->render_form(
	'user__update',
	[
		'after_submit'  => ! current_user_can( 'manage_options' ) ? '<a href="#hp-user-delete" class="hp-js-link" data-type="popup"><i class="hp-icon fas fa-times"></i><span>' . esc_html__( 'Delete Account', 'hivepress' ) . '</span></a>' : '',
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

if ( ! current_user_can( 'manage_options' ) ) :
	?>
	<div id="hp-user-delete" class="hp-popup">
		<h3 class="hp-popup__title"><?php esc_html_e( 'Delete Account', 'hivepress' ); ?></h3>
		<?php
		echo hivepress()->form->render_form(
			'user__delete',
			[
				'before'        => '<div class="hp-form__header">' . esc_html__( 'Please enter your password below to permanently delete your account.', 'hivepress' ) . '</div>',
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
		?>
	</div>
	<?php
endif;
