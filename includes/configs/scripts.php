<?php
/**
 * Scripts configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'jquery_ui_touch_punch'  => [
		'handle' => 'jquery-ui-touch-punch',
		'src'    => hivepress()->get_url() . '/node_modules/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js',
		'deps'   => [ 'jquery-ui-mouse' ],
		'scope'  => [ 'frontend', 'backend' ],
	],

	'fileupload'             => [
		'handle' => 'fileupload',
		'src'    => hivepress()->get_url() . '/node_modules/blueimp-file-upload/js/jquery.fileupload.js',
		'deps'   => [ 'jquery-ui-widget' ],
		'scope'  => [ 'frontend', 'backend' ],
	],

	'fancybox'               => [
		'handle' => 'fancybox',
		'src'    => hivepress()->get_url() . '/node_modules/@fancyapps/fancybox/dist/jquery.fancybox.min.js',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'slick'                  => [
		'handle' => 'slick',
		'src'    => hivepress()->get_url() . '/node_modules/slick-carousel/slick/slick.min.js',
	],

	'sticky_sidebar'         => [
		'handle' => 'sticky-sidebar',
		'src'    => hivepress()->get_url() . '/node_modules/sticky-sidebar/dist/jquery.sticky-sidebar.min.js',
	],

	'php_date_formatter'     => [
		'handle' => 'php-date-formatter',
		'src'    => hivepress()->get_url() . '/node_modules/php-date-formatter/js/php-date-formatter.min.js',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'flatpickr'              => [
		'handle' => 'flatpickr',
		'src'    => hivepress()->get_url() . '/node_modules/flatpickr/dist/flatpickr.min.js',
		'deps'   => [ 'php-date-formatter' ],
		'scope'  => [ 'frontend', 'backend' ],
	],

	'select2'                => [
		'handle' => 'select2-full',
		'src'    => hivepress()->get_url() . '/node_modules/select2/dist/js/select2.full.min.js',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'intl_tel_input'         => [
		'handle' => 'intl-tel-input',
		'src'    => hivepress()->get_url() . '/node_modules/intl-tel-input/build/js/intlTelInput.min.js',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'chartjs'                => [
		'handle' => 'chartjs',
		'src'    => hivepress()->get_url() . '/node_modules/chart.js/dist/chart.min.js',
		'scope'  => [ 'backend' ],
	],

	'chartjs_adapter_moment' => [
		'handle' => 'chartjs-adapter-moment',
		'src'    => hivepress()->get_url() . '/node_modules/chartjs-adapter-moment/dist/chartjs-adapter-moment.min.js',
		'deps'   => [ 'chartjs', 'moment' ],
		'scope'  => [ 'frontend', 'backend' ],
	],

	'core'                   => [
		'handle' => 'hivepress-core',
		'src'    => hivepress()->get_url() . '/assets/js/common.min.js',
		'deps'   => [ 'jquery', 'flatpickr', 'select2-full', 'jquery-ui-touch-punch', 'jquery-ui-sortable', 'fileupload', 'fancybox' ],
		'scope'  => [ 'frontend', 'backend' ],

		'data'   => [
			'apiURL'   => get_rest_url( null, 'hivepress/v1' ),
			'apiNonce' => wp_create_nonce( 'wp_rest' ),
			'language' => hivepress()->translator->get_language(),
		],
	],

	'core_frontend'          => [
		'handle' => 'hivepress-core-frontend',
		'src'    => hivepress()->get_url() . '/assets/js/frontend.min.js',
		'deps'   => [ 'hivepress-core', 'jquery-ui-slider', 'imagesloaded', 'slick', 'sticky-sidebar' ],
	],

	'core_backend'           => [
		'handle' => 'hivepress-core-backend',
		'src'    => hivepress()->get_url() . '/assets/js/backend.min.js',
		'deps'   => [ 'hivepress-core' ],
		'scope'  => 'backend',
	],
];
