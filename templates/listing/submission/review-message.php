<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-row">
	<div class="hp-col-sm-8 hp-col-xs-12">
		<p><?php printf( esc_html__( 'Thank you! Your listing "%s" has been submitted and will be reviewed as soon as possible.', 'hivepress' ), get_the_title( get_query_var( 'hp_listing_submission_review' ) ) ); ?></p>
		<button type="button" class="hp-js-link" data-url="<?php echo esc_url( hivepress()->template->get_url( 'user__account' ) ); ?>"><?php esc_html_e( 'Return to My Account', 'hivepress' ); ?></button>
	</div>
</div>
