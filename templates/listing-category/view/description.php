<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing_category->get_description() ) :
	?>
	<div class="hp-listing-category__description"><?php echo esc_html( $listing_category->get_description() ); ?></div>
	<?php
endif;
