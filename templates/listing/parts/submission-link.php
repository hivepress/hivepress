<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<button type="button" class="hp-menu__link hp-js-link" data-url="<?php echo esc_url( hivepress()->template->get_url( 'listing__submission' ) ); ?>"><?php esc_html_e( 'Submit Listing', 'hivepress' ); ?></button>
