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
						'max_length' => 2048,
					],

					'count'       => [
						'type'      => 'number',
						'min_value' => 0,
					],

					'parent_id'   => [
						'type'      => 'number',
						'min_value' => 1,
					],

					'image_id'    => [
						'type'      => 'number',
						'min_value' => 1,
					],
				],

				'aliases' => [
					'name'        => 'name',
					'description' => 'description',
					'parent'      => 'parent_id',
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Gets image URL.
	 *
	 * @param string $size Image size.
	 * @return mixed
	 */
	final public function get_image_url( $size = 'thumbnail' ) {
		if ( $this->get_image_id() ) {
			$urls = wp_get_attachment_image_src( $this->get_image_id(), $size );

			if ( $urls ) {
				return reset( $urls );
			}
		}
	}

	// todo get_child_ids.
}
