<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo hivepress()->form->render_form(
	'listing__update',
	[
		'after_submit'  => '<a href="#hp-listing-delete" class="hp-js-link" data-type="popup"><i class="hp-icon fas fa-times"></i><span>' . esc_html__( 'Delete Listing', 'hivepress' ) . '</span></a>',
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
<div id="hp-listing-delete" class="hp-popup">
	<h3 class="hp-popup__title"><?php esc_html_e( 'Delete Listing', 'hivepress' ); ?></h3>
	<?php
	echo hivepress()->form->render_form(
		'listing__delete',
		[
			'before'        => '<div class="hp-form__header">' . esc_html__( 'Are you sure you want to permanently delete this listing?', 'hivepress' ) . '</div>',
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
