<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_categories__id() ) :
	?>
	<a href="<?php echo esc_url( hivepress()->router->get_url( 'listing_submit_category_page' ) ); ?>" class="hp-form__action hp-form__action--listing-category-change hp-link"><i class="hp-icon fas fa-arrow-left"></i><span><?php esc_html_e( 'Change Category', 'hivepress' ); ?></span></a>
	<?php
endif;
