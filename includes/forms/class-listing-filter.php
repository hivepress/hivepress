<?php
/**
 * Listing filter form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing filter form class.
 *
 * @class Listing_Filter
 */
class Listing_Filter extends Form {

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

				'fields' => [
					'category'  => [
						'label'   => esc_html__( 'Category', 'hivepress' ),
						'type'    => 'radio',
						'options' => [],
						'order'   => 10,
					],

					's'         => [
						'type' => 'hidden',
					],

					'sort'      => [
						'type' => 'hidden',
					],

					'post_type' => [
						'type'    => 'hidden',
						'default' => 'hp_listing',
					],
				],

				'button' => [
					'label' => esc_html__( 'Filter', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
