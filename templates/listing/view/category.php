<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-listing__category"><?php the_terms( $listing->get_id(), 'hp_listing_category' ); ?></div>
