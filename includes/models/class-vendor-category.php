<?php
/**
 * Vendor category model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor category.
 */
class Vendor_Category extends Term {

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields' => [
					'name'        => [
						'label'      => hivepress()->translator->get_string( 'name' ),
						'type'       => 'text',
						'max_length' => 256,
						'required'   => true,
						'_alias'     => 'name',
					],

					'description' => [
						'label'      => hivepress()->translator->get_string( 'description' ),
						'type'       => 'textarea',
						'max_length' => 2048,
						'html'       => true,
						'_alias'     => 'description',
					],

					'item_count'  => [
						'type'      => 'number',
						'min_value' => 0,
						'_alias'    => 'count',
					],

					'sort_order'  => [
						'type'      => 'number',
						'min_value' => 0,
						'_external' => true,
					],

					'parent'      => [
						'type'   => 'id',
						'_alias' => 'parent',
						'_model' => 'vendor_category',
					],

					'children'    => [
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_vendor_category' ],
						'multiple'    => true,
						'_model'      => 'vendor_category',
						'_relation'   => 'one_to_many',
					],

					'image'       => [
						'type'      => 'id',
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
	 * Gets children IDs.
	 *
	 * @return array
	 */
	final public function get_children__id() {
		if ( ! isset( $this->values['children__id'] ) ) {

			// Get children IDs.
			$children_ids = [];

			if ( $this->id ) {
				$children_ids = static::query()->filter(
					[
						'parent' => $this->id,
					]
				)->get_ids();
			}

			// Set field value.
			$this->set_children( $children_ids );
			$this->values['children__id'] = $children_ids;
		}

		return $this->fields['children']->get_value();
	}
}
