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
 * Listing category model class.
 *
 * @class Listing_Category
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
						'label'      => esc_html__( 'Name', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 256,
						'required'   => true,
						'_alias'     => 'name',
					],

					'description' => [
						'label'      => esc_html__( 'Description', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 2048,
						'_alias'     => 'description',
					],

					'count'       => [
						'type'      => 'number',
						'min_value' => 0,
						'_alias'    => 'count',
					],

					'parent'      => [
						'type'      => 'number',
						'min_value' => 1,
						'_alias'    => 'parent',
						'_model'    => 'listing_category',
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
						'type'      => 'number',
						'min_value' => 1,
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

			// Get term IDs.
			$term_ids = get_terms(
				[
					'taxonomy'   => static::_get_meta( 'alias' ),
					'parent'     => $this->id,
					'hide_empty' => false,
					'fields'     => 'ids',
				]
			);

			// Set field value.
			$this->set_children( $term_ids );
			$this->values['children__id'] = $term_ids;
		}

		return $this->fields['children']->get_value();
	}

	/**
	 * Gets image URL.
	 *
	 * @param string $size Image size.
	 * @return string
	 */
	final public function get_image__url( $size = 'thumbnail' ) {

		// Get field name.
		$name = 'image__url__' . $size;

		if ( ! isset( $this->values[ $name ] ) ) {
			$this->values[ $name ] = '';

			// Get image URL.
			if ( $this->get_image__id() ) {
				$urls = wp_get_attachment_image_src( $this->get_image__id(), $size );

				if ( $urls ) {
					$this->values[ $name ] = reset( $urls );
				}
			}
		}

		return $this->values[ $name ];
	}
}
