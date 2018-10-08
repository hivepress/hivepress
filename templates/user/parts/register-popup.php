<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) :
	?>
	<div id="hp-user-register" class="hp-popup">
		<h3 class="hp-popup__title"><?php esc_html_e( 'Register', 'hivepress' ); ?></h3>
		<?php echo hivepress()->template->render_part( 'user/parts/register-form' ); ?>
	</div>
	<?php
endif;
