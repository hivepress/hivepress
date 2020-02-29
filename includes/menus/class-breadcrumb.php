<?php
/**
 * Breadcrumb menu.
 *
 * @package HivePress\Menus
 */

namespace HivePress\Menus;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Breadcrumb menu class.
 *
 * @class Breadcrumb
 */
class Breadcrumb extends Menu {

	/**
	 * Class constructor.
	 *
	 * @param array $args Menu arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'items' => [],
			],
			$args
		);

		parent::__construct( $args );
	}
}
