<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( get_option( 'hp_listing_enable_reporting', true ) ) :
	?>
	<a href="#<?php if ( is_user_logged_in() ) : ?>listing_report<?php else : ?>user_login<?php endif; ?>_modal" class="hp-listing__action hp-listing__action--report hp-link"><i class="hp-icon fas fa-flag"></i><span><?php echo esc_html( hivepress()->translator->get_string( 'report_listing' ) ); ?></span></a>
	<?php
endif;
