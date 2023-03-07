<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( isset( $is_resend ) ) :?>
<p><?php esc_html_e( 'Please check your email to activate your account. If you have not received email then please fill in the form below to resend the verification email.', 'hivepress' ); ?></p>
<?php else : ?>
<p><?php esc_html_e( 'Thank you! Your email address has been verified and you can start using your account.', 'hivepress' ); ?></p>
<button type="button" class="button" data-component="link" data-url="<?php echo esc_url( hivepress()->router->get_url( 'user_account_page' ) ); ?>"><?php echo esc_html( hivepress()->translator->get_string( 'return_to_my_account' ) ); ?></button>
<?php endif; ?>
