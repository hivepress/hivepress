<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( hivepress()->admin->get_option( 'listing_enable_submission' ) ) :
	?>
	<button type="button" class="hp-menu__item hp-menu__item--listing-submit button" data-component="link" data-url="<?php echo esc_url( hivepress()->router->get_url( 'listing_submit_page' ) ); ?>"><span><?php echo esc_html( hivepress()->translator->get_string( 'add_listing' ) ); ?></span></button>
	<?php
endif;
