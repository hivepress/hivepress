<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<p><?php esc_html_e( 'Password reset link is expired or invalid.', 'hivepress' ); ?></p>
<button type="button" class="button" data-component="link" data-url="#user_password_request_modal"><?php esc_html_e( 'Reset Password', 'hivepress' ); ?></button>
