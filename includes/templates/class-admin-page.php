<?php
/**
 * Admin page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Admin page template class.
 *
 * @class Admin_Page
 */
abstract class Admin_Page extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_container' => [],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
