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
		'src'    => hivepress()->get_url() . '/assets/js/jquery.ui.touch-punch.min.js',
		'deps'   => [ 'jquery-ui-mouse' ],
	],

	'serializejson'         => [
		'handle' => 'serializejson',
		'src'    => hivepress()->get_url() . '/assets/js/jquery.serializejson.min.js',
	],

	'iframe_transport'      => [
		'handle' => 'iframe-transport',
		'src'    => hivepress()->get_url() . '/assets/js/jquery.iframe-transport.min.js',
	],

	'fileupload'            => [
		'handle' => 'fileupload',
		'src'    => hivepress()->get_url() . '/assets/js/jquery.fileupload.min.js',
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
		'src'    => hivepress()->get_url() . '/assets/js/flatpickr.min.js',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'core_common'           => [
		'handle' => 'hp-core-common',
		'src'    => hivepress()->get_url() . '/assets/js/common.min.js',
		'deps'   => [ 'jquery', 'flatpickr' ],
		'scope'  => [ 'frontend', 'backend' ],

		'data'   => [
			'apiURL'   => get_rest_url( null, 'hivepress/v1' ),
			'apiNonce' => wp_create_nonce( 'wp_rest' ),
			'language' => hivepress()->translator->get_language(),
		],
	],

	'core_backend'          => [
		'handle' => 'hp-core-backend',
		'src'    => hivepress()->get_url() . '/assets/js/backend.min.js',
		'deps'   => [ 'hp-core-common' ],
		'scope'  => 'backend',
	],

	'core_frontend'         => [
		'handle' => 'hp-core-frontend',
		'src'    => hivepress()->get_url() . '/assets/js/frontend.min.js',
		'deps'   => [ 'hp-core-common', 'jquery-ui-touch-punch', 'jquery-ui-sortable', 'jquery-ui-slider', 'serializejson', 'fileupload', 'fancybox', 'slick', 'sticky-sidebar' ],
	],
];
