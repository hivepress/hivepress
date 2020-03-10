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
	'serializejson'         => [
		'handle' => 'serializejson',
		'src'    => hivepress()->get_url() . '/assets/js/jquery.serializejson.min.js',
	],

	'jquery_ui_touch_punch' => [
		'handle' => 'jquery-ui-touch-punch',
		'src'    => hivepress()->get_url() . '/assets/js/jquery.ui.touch-punch.min.js',
		'deps'   => [ 'jquery-ui-mouse' ],
	],

	'iframe_transport'      => [
		'handle' => 'iframe-transport',
		'src'    => hivepress()->get_url() . '/assets/js/fileupload/jquery.iframe-transport.min.js',
	],

	'fileupload'            => [
		'handle' => 'fileupload',
		'src'    => hivepress()->get_url() . '/assets/js/fileupload/jquery.fileupload.min.js',
		'deps'   => [ 'jquery-ui-widget', 'iframe-transport' ],
	],

	'fancybox'              => [
		'handle' => 'fancybox',
		'src'    => hivepress()->get_url() . '/assets/js/jquery.fancybox.min.js',
	],

	'slick'                 => [
		'handle' => 'slick',
		'src'    => hivepress()->get_url() . '/assets/js/slick.min.js',
	],

	'sticky_sidebar'        => [
		'handle' => 'sticky-sidebar',
		'src'    => hivepress()->get_url() . '/assets/js/jquery.sticky-sidebar.min.js',
	],

	'flatpickr'             => [
		'handle' => 'flatpickr',
		'src'    => hivepress()->get_url() . '/assets/js/flatpickr/flatpickr.min.js',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'select2'               => [
		'handle' => 'select2',
		'src'    => hivepress()->get_url() . '/assets/js/select2/select2.min.js',
	],

	'core'                  => [
		'handle' => 'hivepress-core',
		'src'    => hivepress()->get_url() . '/assets/js/common.min.js',
		'deps'   => [ 'jquery', 'flatpickr' ],
		'scope'  => [ 'frontend', 'backend' ],

		'data'   => [
			'apiURL'   => get_rest_url( null, 'hivepress/v1' ),
			'apiNonce' => wp_create_nonce( 'wp_rest' ),
			'language' => hivepress()->translator->get_language(),
		],
	],

	'core_frontend'         => [
		'handle' => 'hivepress-core-frontend',
		'src'    => hivepress()->get_url() . '/assets/js/frontend.min.js',
		'deps'   => [ 'hivepress-core', 'jquery-ui-touch-punch', 'jquery-ui-sortable', 'jquery-ui-slider', 'serializejson', 'fileupload', 'fancybox', 'slick', 'sticky-sidebar', 'select2' ],
	],

	'core_backend'          => [
		'handle' => 'hivepress-core-backend',
		'src'    => hivepress()->get_url() . '/assets/js/backend.min.js',
		'deps'   => [ 'hivepress-core' ],
		'scope'  => 'backend',
	],
];
