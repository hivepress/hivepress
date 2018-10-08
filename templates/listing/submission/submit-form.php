<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-row">
	<div class="hp-col-sm-8 hp-col-xs-12">
		<?php
		echo hivepress()->form->render_form(
			'listing__submit',
			[
				'after_submit'  => hivepress()->listing->get_categories() ? '<a href="' . esc_url( hivepress()->template->get_url( 'listing__submission_category' ) ) . '"><i class="hp-icon fas fa-arrow-left"></i>' . esc_html__( 'Change Category', 'hivepress' ) . '</a>' : '',
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
</div>
