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
	'jquery_ui_touch_punch' => [
		'handle' => 'jquery-ui-touch-punch',
		'src'    => HP_CORE_URL . '/assets/js/jquery.ui.touch-punch.min.js',
		'deps'   => [ 'jquery-ui-mouse' ],
	],

	'serializejson'         => [
		'handle' => 'serializejson',
		'src'    => HP_CORE_URL . '/assets/js/jquery.serializejson.min.js',
	],

	'iframe_transport'      => [
		'handle' => 'iframe-transport',
		'src'    => HP_CORE_URL . '/assets/js/jquery.iframe-transport.min.js',
	],

	'fileupload'            => [
		'handle' => 'fileupload',
		'src'    => HP_CORE_URL . '/assets/js/jquery.fileupload.min.js',
		'deps'   => [ 'jquery-ui-widget', 'iframe-transport' ],
	],

	'fancybox'              => [
		'handle' => 'fancybox',
		'src'    => HP_CORE_URL . '/assets/js/jquery.fancybox.min.js',
	],

	'slick'                 => [
		'handle' => 'slick',
		'src'    => HP_CORE_URL . '/assets/js/slick.min.js',
	],

	'sticky_sidebar'        => [
		'handle' => 'sticky-sidebar',
		'src'    => HP_CORE_URL . '/assets/js/jquery.sticky-sidebar.min.js',
	],

	'select2'               => [
		'handle' => 'select2',
		'src'    => HP_CORE_URL . '/assets/js/select2.min.js',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'flatpickr'             => [
		'handle' => 'flatpickr',
		'src'    => HP_CORE_URL . '/assets/js/flatpickr.min.js',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'core_frontend'         => [
		'handle' => 'hp-core-frontend',
		'src'    => HP_CORE_URL . '/assets/js/frontend.min.js',
		'deps'   => [ 'jquery', 'jquery-ui-touch-punch', 'jquery-ui-sortable', 'jquery-ui-slider', 'serializejson', 'fileupload', 'fancybox', 'slick', 'sticky-sidebar', 'select2', 'flatpickr' ],

		'data'   => [
			'apiURL'   => hp\get_rest_url(),
			'apiNonce' => wp_create_nonce( 'wp_rest' ),
		],
	],

	'core_backend'          => [
		'handle' => 'hp-core-backend',
		'src'    => HP_CORE_URL . '/assets/js/backend.min.js',
		'deps'   => [ 'jquery', 'select2', 'flatpickr' ],
		'scope'  => 'backend',

		'data'   => [
			'apiURL'   => hp\get_rest_url(),
			'apiNonce' => wp_create_nonce( 'wp_rest' ),
		],
	],
];
