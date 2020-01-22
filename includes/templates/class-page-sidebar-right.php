<?php
/**
 * Abstract right sidebar page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Right sidebar page template class.
 *
 * @class Page_Sidebar_Right
 */
abstract class Page_Sidebar_Right extends Page_Sidebar_Left {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_content' => [
						'_order' => 10,
					],

					'page_sidebar' => [
						'_order' => 20,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
