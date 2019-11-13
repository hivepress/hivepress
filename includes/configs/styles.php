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
		'src'    => HP_CORE_URL . '/assets/css/fontawesome.min.css',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'fontawesome_solid' => [
		'handle' => 'fontawesome-solid',
		'src'    => HP_CORE_URL . '/assets/css/fontawesome-solid.min.css',
		'scope'  => [ 'frontend', 'backend' ],
	],

	'fancybox'          => [
		'handle' => 'fancybox',
		'src'    => HP_CORE_URL . '/assets/css/fancybox.min.css',
	],

	'slick'             => [
		'handle' => 'slick',
		'src'    => HP_CORE_URL . '/assets/css/slick.min.css',
	],

	'jquery_ui'         => [
		'handle' => 'jquery-ui',
		'src'    => HP_CORE_URL . '/assets/css/jquery-ui.min.css',
	],

	'grid'              => [
		'handle' => 'hp-grid',
		'src'    => HP_CORE_URL . '/assets/css/grid.min.css',
		'scope'  => [ 'frontend', 'editor' ],
	],

	'core_frontend'     => [
		'handle' => 'hp-core-frontend',
		'src'    => HP_CORE_URL . '/assets/css/frontend.min.css',
		'scope'  => [ 'frontend', 'editor' ],
	],

	'core_backend'      => [
		'handle' => 'hp-core-backend',
		'src'    => HP_CORE_URL . '/assets/css/backend.min.css',
		'scope'  => 'backend',
	],
];
