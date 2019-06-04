<?php
/**
 * Page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Page template class.
 *
 * @class Page
 */
class Page extends Template {

	/**
	 * Template name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Template blocks.
	 *
	 * @var array
	 */
	protected static $blocks = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Template arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'site_header'    => [
						'type'     => 'element',
						'filepath' => 'page/header',
						'order'    => 10,
					],

					'page_container' => [
						'type'   => 'page_container',
						'order'  => 20,

						'blocks' => [],
					],

					'site_footer'    => [
						'type'     => 'element',
						'filepath' => 'page/footer',
						'order'    => 30,
					],
				],
			],
			$args,
			'blocks'
		);

		parent::init( $args );
	}
}
