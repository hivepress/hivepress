<?php
/**
 * Listing search form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing search form class.
 *
 * @class Listing_Search
 */
class Listing_Search extends Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields' => [
					's'         => [
						'label'      => esc_html__( 'Keywords', 'hivepress' ),
						'type'       => 'search',
						'max_length' => 256,
						'order'      => 10,
					],

					'post_type' => [
						'type'    => 'hidden',
						'default' => 'hp_listing',
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'action' => home_url( '/' ),
				'method' => 'GET',

				'button' => [
					'label' => esc_html__( 'Search', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
