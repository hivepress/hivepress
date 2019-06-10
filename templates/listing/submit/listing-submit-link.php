<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<button type="button" class="hp-menu__item button" data-component="link" data-url="<?php echo esc_url( hivepress()->router->get_url( 'listing/submit_listing' ) ); ?>"><?php esc_html_e( 'Submit Listing', 'hivepress' ); ?></button>
