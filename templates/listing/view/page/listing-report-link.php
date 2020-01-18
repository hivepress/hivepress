<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<a href="#<?php if ( is_user_logged_in() ) : ?>listing_report_modal_<?php echo esc_attr( $listing->get_id() ); else : ?>user_login_modal<?php endif; ?>" class="hp-listing__action hp-listing__action--report hp-link"><i class="hp-icon fas fa-flag"></i><span><?php echo esc_html( hivepress()->translator->get_string( 'report_listing' ) ); ?></span></a>
