<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<h4 class="hp-listing-category__name"><a href="<?php echo esc_url( $listing_category->get_url() ); ?>"><?php echo esc_html( $listing_category->get_name() ); ?></a></h4>
