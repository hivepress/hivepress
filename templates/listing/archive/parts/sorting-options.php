<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-sorting-options">
	<?php
	echo hivepress()->form->render_form(
		'listing__sort',
		[
			'attributes' => [
				'data-type' => 'autosubmit',
			],
		]
	);
	?>
</div>
