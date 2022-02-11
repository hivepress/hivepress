<?php
/**
 * Site footer block template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Site footer block (for modals).
 */
class Site_Footer_Block extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'modals' => [
						'type'   => 'container',
						'tag'    => false,
						'blocks' => [],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
