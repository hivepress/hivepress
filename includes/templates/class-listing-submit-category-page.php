<?php
/**
 * Listing submit category page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing submission page (category selection step).
 *
 * @deprecated since version 1.6.4.
 */
class Listing_Submit_Category_Page extends Listing_Submit_Page {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => hivepress()->translator->get_string( 'add_listing' ) . ' (' . hivepress()->translator->get_string( 'categories' ) . ')',
			],
			$meta
		);

		parent::init( $meta );
	}

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
						'blocks' => [
							'listing_categories' => [
								'type'      => 'listing_categories',
								'mode'      => 'submit',
								'columns'   => 3,
								'_label'    => true,
								'_settings' => [ 'columns', 'order' ],
								'_order'    => 10,
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
