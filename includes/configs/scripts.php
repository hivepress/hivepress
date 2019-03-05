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
	'knockout'         => [
		'handle' => 'knockout',
		'src'    => HP_CORE_URL . '/assets/js/knockout.js',
	],

	'iframe_transport' => [
		'handle' => 'iframe-transport',
		'src'    => HP_CORE_URL . '/assets/js/jquery.iframe-transport.min.js',
	],

	'file_upload'      => [
		'handle' => 'fileupload',
		'src'    => HP_CORE_URL . '/assets/js/jquery.fileupload.min.js',
		'deps'   => [ 'jquery-ui-widget', 'iframe-transport' ],
	],

	'fancybox'         => [
		'handle' => 'fancybox',
		'src'    => HP_CORE_URL . '/assets/js/jquery.fancybox.min.js',
	],

	'slick'            => [
		'handle' => 'slick',
		'src'    => HP_CORE_URL . '/assets/js/slick.min.js',
	],

	'sticky_sidebar'   => [
		'handle' => 'sticky-sidebar',
		'src'    => HP_CORE_URL . '/assets/js/jquery.sticky-sidebar.min.js',
	],

	'core_frontend'    => [
		'handle' => 'hp-core-frontend',
		'src'    => HP_CORE_URL . '/assets/js/frontend.min.js',
		'deps'   => [ 'jquery', 'jquery-ui-sortable', 'fileupload', 'fancybox', 'slick', 'sticky-sidebar' ],
		'data'   => [
			'apiURL'   => hp\get_rest_url(),
			'apiNonce' => wp_create_nonce( 'wp_rest' ),
		],
	],

	'core_backend'     => [
		'handle' => 'hp-core-backend',
		'src'    => HP_CORE_URL . '/assets/js/backend.min.js',
		'deps'   => [ 'jquery' ],
		'admin'  => true,
	],
];
