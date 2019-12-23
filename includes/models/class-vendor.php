<?php
/**
 * Vendor model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor model class.
 *
 * @class Vendor
 */
class Vendor extends Post {

	/**
	 * Model meta.
	 *
	 * @var array
	 */
	protected static $meta;

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields'  => [
					'name'        => [
						'label'      => esc_html__( 'Name', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 128,
						'required'   => true,
					],

					'description' => [
						'label'      => esc_html__( 'Description', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 10240,
					],

					'status'      => [
						'type'       => 'text',
						'max_length' => 128,
					],

					'user_id'     => [
						'type'      => 'number',
						'min_value' => 1,
						'required'  => true,
					],
				],

				'aliases' => [
					'post_title'   => 'name',
					'post_content' => 'description',
					'post_status'  => 'status',
					'post_author'  => 'user_id',
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
