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
class Admin_Page extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'admin_container' => [
						'type'       => 'container',
						'_order'     => 10,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
