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
 * Vendor.
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
						'label'      => hivepress()->translator->get_string( 'name' ),
						'type'       => 'text',
						'max_length' => 256,
						'required'   => true,
						'_alias'     => 'post_title',
					],

					'slug'            => [
						'type'       => 'text',
						'max_length' => 256,
						'_alias'     => 'post_name',
					],

					'description'     => [
						'label'      => hivepress()->translator->get_string( 'description' ),
						'type'       => 'textarea',
						'max_length' => 10240,
						'html'       => true,
						'_alias'     => 'post_content',
					],

					'status'          => [
						'type'       => 'text',
						'max_length' => 128,
						'_alias'     => 'post_status',
					],

					'verified'        => [
						'type'      => 'checkbox',
						'_external' => true,
					],

					'registered_date' => [
						'type'   => 'date',
						'format' => 'Y-m-d H:i:s',
						'_alias' => 'post_date',
					],

					'user'            => [
						'type'     => 'id',
						'required' => true,
						'_alias'   => 'post_author',
						'_model'   => 'user',
					],

					'categories'      => [
						'label'       => hivepress()->translator->get_string( 'category' ),
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_vendor_category' ],
						'multiple'    => true,
						'required'    => true,
						'_indexable'  => true,
						'_model'      => 'vendor_category',
						'_relation'   => 'many_to_many',
					],

					'image'           => [
						'type'      => 'id',
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

	/**
	 * Gets model fields.
	 *
	 * @param string $area Display area.
	 * @return array
	 */
	final public function _get_fields( $area = null ) {
		return array_filter(
			$this->fields,
			function( $field ) use ( $area ) {
				return empty( $area ) || in_array( $area, (array) $field->get_arg( '_display_areas' ), true );
			}
		);
	}
}
