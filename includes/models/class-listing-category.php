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
	 * Model fields.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Model aliases.
	 *
	 * @var array
	 */
	protected static $aliases = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Model arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields'  => [
					'name'          => [
						'label'      => esc_html__( 'Name', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 128,
						'required'   => true,
					],

					'description'   => [
						'label'      => esc_html__( 'Description', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 2048,
					],

					'listing_count' => [
						'type'      => 'number',
						'min_value' => 0,
					],

					'image_id'      => [
						'type'      => 'number',
						'min_value' => 1,
					],

					'parent_id'     => [
						'type'      => 'number',
						'min_value' => 1,
					],
				],

				'aliases' => [
					'name'        => 'name',
					'description' => 'description',
					'count'       => 'listing_count',
					'parent'      => 'parent_id',
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Gets URL.
	 *
	 * @return string
	 */
	final public function get_url() {
		return get_term_link( $this->id );
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

			if ( false !== $urls ) {
				return reset( $urls );
			}
		}
	}

	/**
	 * Gets listing count.
	 *
	 * @return int
	 */
	final public function get_listing_count() {

		// Set query arguments.
		$query_args = [
			'post_status' => 'publish',
		];

		// Get cached count.
		$count = hivepress()->cache->get_term_cache( $this->id, array_merge( $query_args, [ 'function' => 'count' ] ), 'listing' );

		if ( is_null( $count ) ) {

			// Get count.
			$count = count(
				get_posts(
					array_merge(
						$query_args,
						[
							'post_type'      => 'hp_listing',
							'posts_per_page' => -1,
							'fields'         => 'ids',
							'tax_query'      => [
								[
									'taxonomy' => hp\prefix( static::get_name() ),
									'terms'    => [ absint( $this->id ) ],
								],
							],
						]
					)
				)
			);

			// Cache count.
			hivepress()->cache->set_term_cache( $this->id, array_merge( $query_args, [ 'function' => 'count' ] ), 'listing', $count );
		}

		return absint( $count );
	}
}
