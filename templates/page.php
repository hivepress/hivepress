<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

echo hivepress()->template->render_area( 'page__content' );

get_footer();
