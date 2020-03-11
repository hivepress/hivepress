<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-listing-category__item-count hp-listing-category__count"><?php printf( esc_html( translate_nooped_plural( hivepress()->translator->get_string( 'n_listings' ), $listing_category->get_item_count(), 'hivepress' ) ), $listing_category->display_item_count() ); ?></div>
