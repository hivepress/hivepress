<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) :
	?>
	<div id="hp-user-request-password" class="hp-popup">
		<h3 class="hp-popup__title"><?php esc_html_e( 'Reset Password', 'hivepress' ); ?></h3>
		<?php echo hivepress()->template->render_part( 'user/parts/request-password-form' ); ?>
	</div>
	<?php
endif;
