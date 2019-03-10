<?php
/**
 * Listing categories block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing categories block class.
 *
 * @class Listing_Categories
 */
class Listing_Categories extends Block {

	/**
	 * Block title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Block settings.
	 *
	 * @var string
	 */
	protected static $settings = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Block arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'    => esc_html__( 'Listing Categories', 'hivepress' ),
				'settings' => [
					'columns' => [
						'label'   => esc_html__( 'Columns', 'hivepress' ),
						'type'    => 'select',
						'default' => 3,
						'order'   => 10,
						'options' => [
							2 => '2',
							3 => '3',
							4 => '4',
						],
					],

					'number'  => [
						'label'     => esc_html__( 'Number', 'hivepress' ),
						'type'      => 'number',
						'min_value' => 1,
						'default'   => 3,
						'order'     => 20,
					],

					'parent'  => [
						'label'    => esc_html__( 'Parent', 'hivepress' ),
						'type'     => 'select',
						'options'  => 'terms',
						'taxonomy' => 'hp_listing_category',
						'default'  => '',
						'order'    => 30,
					],

					'order'   => [
						'label'   => esc_html__( 'Order', 'hivepress' ),
						'type'    => 'select',
						'default' => 'date',
						'order'   => 40,
						'options' => [
							'date'  => esc_html__( 'Date', 'hivepress' ),
							'name'  => esc_html__( 'Name', 'hivepress' ),
							'count' => esc_html__( 'Count', 'hivepress' ),
						],
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		// todo.
		return '';
	}
}
