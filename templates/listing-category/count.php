<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-listing-category__count"><?php printf( esc_html( _n( '%d Listing', '%d Listings', $category->get_count(), 'hivepress' ) ), number_format_i18n( $category->get_count() ) ); ?></div>
