<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-listing-category__item-count hp-listing-category__count"><?php printf( esc_html( _n( '%s Listing', '%s Listings', $listing_category->get_item_count(), 'hivepress' ) ), $listing_category->display_item_count() ); ?></div>
