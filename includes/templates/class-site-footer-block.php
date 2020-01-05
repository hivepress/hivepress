<?php
/**
 * Site footer block template.
 *
 * @template site_footer_block
 * @description Site footer block.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Site footer block template class.
 *
 * @class Site_Footer_Block
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
				'blocks' => [],
			],
			$args
		);

		parent::__construct( $args );
	}
}
