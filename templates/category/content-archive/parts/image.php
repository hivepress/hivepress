<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-category__image">
	<a href="<?php echo esc_url( get_term_link( $category->term_id ) ); ?>"><?php echo hivepress()->listing->render_category_image( $category->term_id ); ?></a>
</div>
