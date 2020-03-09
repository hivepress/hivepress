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
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields' => [
					'name'            => [
						'label'      => esc_html__( 'Name', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 256,
						'required'   => true,
						'_alias'     => 'post_title',
					],

					'description'     => [
						'label'      => esc_html__( 'Description', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 10240,
						'_alias'     => 'post_content',
					],

					'slug'            => [
						'type'       => 'text',
						'max_length' => 256,
						'_alias'     => 'post_name',
					],

					'status'          => [
						'type'       => 'text',
						'max_length' => 128,
						'_alias'     => 'post_status',
					],

					'registered_date' => [
						'type'   => 'date',
						'format' => 'Y-m-d H:i:s',
						'_alias' => 'post_date',
					],

					'user'            => [
						'type'      => 'number',
						'min_value' => 1,
						'required'  => true,
						'_alias'    => 'post_author',
						'_model'    => 'user',
					],

					'categories'      => [
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_vendor_category' ],
						'multiple'    => true,
						'_model'      => 'vendor_category',
						'_relation'   => 'many_to_many',
					],

					'image'           => [
						'type'      => 'number',
						'min_value' => 1,
						'_alias'    => '_thumbnail_id',
						'_model'    => 'attachment',
						'_external' => true,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
