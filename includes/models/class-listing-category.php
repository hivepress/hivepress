<?php
/**
 * Listing category model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing category.
 */
class Listing_Category extends Term {

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
						'_model' => 'listing_category',
					],

					'children'    => [
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_listing_category' ],
						'multiple'    => true,
						'_model'      => 'listing_category',
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

	/**
	 * Gets image URL.
	 *
	 * @param string $size Image size.
	 * @return string
	 * @deprecated Since version 1.3.0
	 */
	final public function get_image_url( $size ) {
		return $this->get_image__url( $size );
	}

	/**
	 * Gets image ID.
	 *
	 * @return int
	 * @deprecated Since version 1.3.0
	 */
	final public function get_image_id() {
		return $this->get_image__id();
	}

	/**
	 * Gets URL.
	 *
	 * @return string
	 * @deprecated Since version 1.3.0
	 */
	final public function get_url() {
		$url = null;

		if ( hivepress()->request->get_param( 'route' ) === 'listing_submit_category_page' ) {
			$url = hivepress()->router->get_url( 'listing_submit_category_page', [ 'listing_category_id' => $this->get_id() ] );
		} else {
			$url = hivepress()->router->get_url( 'listing_category_view_page', [ 'listing_category_id' => $this->get_id() ] );
		}

		return $url;
	}
}
