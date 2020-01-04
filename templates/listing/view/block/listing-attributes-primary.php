<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-listing__attributes hp-listing__attributes--primary">
	<?php foreach ( $listing->_get_fields( 'view_block_primary' ) as $field ) : ?>
		<div class="hp-listing__attribute"><?php echo $field->display(); ?></div>
	<?php endforeach; ?>
</div>
