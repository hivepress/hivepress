<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<a href="#hp-<?php if ( is_user_logged_in() ) : ?>listing-report<?php else : ?>user-login<?php endif; ?>" class="hp-listing__action hp-js-link" data-type="popup"><i class="hp-icon fas fa-flag"></i><?php esc_html_e( 'Report Listing', 'hivepress' ); ?></a>
<?php if ( is_user_logged_in() ) : ?>
	<div id="hp-listing-report" class="hp-popup">
		<h3 class="hp-popup__title"><?php esc_html_e( 'Report Listing', 'hivepress' ); ?></h3>
		<?php
		echo hivepress()->form->render_form(
			'listing__report',
			[
				'attributes'    => [
					'data-type' => 'ajax reset',
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
