<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-listing__attributes hp-listing__attributes--secondary">
	<div class="hp-row">
		<?php foreach ( $listing->get_attributes( 'todo' ) as $attribute ) : ?>
			<div class="hp-col-lg-6 hp-col-xs-12">
				todo
			</div>
		<?php endforeach; ?>
	</div>
</div>
