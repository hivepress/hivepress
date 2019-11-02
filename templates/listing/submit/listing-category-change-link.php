<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( wp_count_terms( 'hp_listing_category' ) > 0 ) :
	?>
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'listing/submit_category' ) ); ?>" class="hp-form__action hp-link"><i class="hp-icon fas fa-arrow-left"></i><span><?php esc_html_e( 'Change Category', 'hivepress' ); ?></span></a>
	<?php
endif;
