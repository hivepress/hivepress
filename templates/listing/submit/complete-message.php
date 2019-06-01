<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<p><?php printf( esc_html__( 'Thank you! Your listing "%s" has been submitted and will be reviewed as soon as possible.', 'hivepress' ), $listing->get_title() ); ?></p>
<button type="button" data-component="link" data-url="<?php echo esc_url( hivepress()->router->get_url( 'user/account' ) ); ?>"><?php esc_html_e( 'Return to My Account', 'hivepress' ); ?></button>
