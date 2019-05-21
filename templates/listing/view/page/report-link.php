<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<a href="#<?php if ( is_user_logged_in() ) : ?>listing_report_<?php else : ?>user_login<?php endif; ?>_modal" class="hp-listing__action hp-link"><i class="hp-icon fas fa-flag"></i><span><?php esc_html_e( 'Report Listing', 'hivepress' ); ?></span></a>
