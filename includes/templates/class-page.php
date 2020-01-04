<?php
/**
 * Abstract page template.
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
abstract class Page extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_container' => [
						'type'   => 'page',
						'_order' => 10,

						'blocks' => [],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
