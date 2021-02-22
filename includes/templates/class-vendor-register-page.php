<?php
/**
 * Vendor register page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor register page template class.
 *
 * @class Vendor_Register_Page
 */
abstract class Vendor_Register_Page extends Page_Wide {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_content' => [],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
