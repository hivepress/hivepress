<?php
/**
 * Vendor search form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;
use HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Searches vendors.
 */
class Vendor_Search extends Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'model' => 'vendor',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'action' => home_url(),
				'method' => 'GET',

				'fields' => [
					's'         => [
						'placeholder'  => hivepress()->translator->get_string( 'keywords' ),
						'type'         => 'text',
						'display_type' => 'search',
						'max_length'   => 256,
						'_order'       => 10,
					],

					'_category' => [
						'placeholder'  => hivepress()->translator->get_string( 'all_categories' ),
						'type'         => 'select',
						'display_type' => 'hidden',
						'options'      => 'terms',
						'option_args'  => [ 'taxonomy' => 'hp_vendor_category' ],
						'_order'       => 5,
					],

					'post_type' => [
						'type'    => 'hidden',
						'default' => 'hp_vendor',
					],
				],

				'button' => [
					'label' => hivepress()->translator->get_string( 'search' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
