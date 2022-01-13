<?php
/**
 * Admin tools page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Admin tools page template class.
 *
 * @class Admin_Tools_Page
 */
class Admin_Tools_Page extends Admin_Page {

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
						'type'       => 'container',
						'_order'     => 10,

						'blocks' => [
							'status' => [
								'type' => 'part',
								'path' => 'admin/tools/status',
								'_order' => 10,
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
