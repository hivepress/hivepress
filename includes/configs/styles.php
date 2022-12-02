<?php
/**
 * Styles configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'fontawesome'       => [
		'handle' => 'fontawesome',
		'src'    => hivepress()->get_url() . '/assets/css/fontawesome/fontawesome.min.css',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'fontawesome_solid' => [
		'handle' => 'fontawesome-solid',
		'src'    => hivepress()->get_url() . '/assets/css/fontawesome/solid.min.css',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'fancybox'          => [
		'handle' => 'fancybox',
		'src'    => hivepress()->get_url() . '/node_modules/@fancyapps/fancybox/dist/jquery.fancybox.min.css',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'slick'             => [
		'handle' => 'slick',
		'src'    => hivepress()->get_url() . '/node_modules/slick-carousel/slick/slick.css',
	],

	'flatpickr'         => [
		'handle' => 'flatpickr',
		'src'    => hivepress()->get_url() . '/node_modules/flatpickr/dist/flatpickr.min.css',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'select2'           => [
		'handle' => 'select2',
		'src'    => hivepress()->get_url() . '/node_modules/select2/dist/css/select2.min.css',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'intl_tel_input'    => [
		'handle' => 'intl-tel-input',
		'src'    => hivepress()->get_url() . '/node_modules/intl-tel-input/build/css/intlTelInput.min.css',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'jquery_ui'         => [
		'handle' => 'jquery-ui',
		'src'    => hivepress()->get_url() . '/assets/css/jquery-ui.min.css',
	],

	'grid'              => [
		'handle' => 'hivepress-grid',
		'src'    => hivepress()->get_url() . '/assets/css/grid.min.css',
		'scope'  => [ 'frontend', 'backend', 'editor' ],
	],

	'core_common'       => [
		'handle' => 'hivepress-core-common',
		'src'    => hivepress()->get_url() . '/assets/css/common.min.css',
		'scope'  => [ 'frontend', 'backend', 'editor' ],
	],

	'core_frontend'     => [
		'handle' => 'hivepress-core-frontend',
		'src'    => hivepress()->get_url() . '/assets/css/frontend.min.css',
		'scope'  => [ 'frontend', 'editor' ],
	],

	'core_backend'      => [
		'handle' => 'hivepress-core-backend',
		'src'    => hivepress()->get_url() . '/assets/css/backend.min.css',
		'scope'  => 'backend',
	],
];
