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
	 * Model name.
	 *
	 * @var string
	 */
	protected static $name;

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

					'image_id'    => [
						'type'      => 'number',
						'min_value' => 0,
					],
				],

				'aliases' => [
					'name'        => 'name',
					'description' => 'description',
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

		return null;
	}

	/**
	 * Gets count.
	 *
	 * @return int
	 */
	final public function get_count() {

		// Set query arguments.
		$query_args = [
			'post_type'   => 'hp_listing',
			'post_status' => 'publish',
		];

		// Get cached count.
		$count = hivepress()->cache->get_term_cache( $this->id, array_merge( $query_args, [ 'cache_type' => 'count' ] ), 'post/listing' );

		if ( is_null( $count ) ) {

			// Get category IDs.
			$category_ids = array_merge( [ absint( $this->id ) ], get_term_children( absint( $this->id ), hp\prefix( static::$name ) ) );

			// Get listing IDs.
			$listing_ids = get_posts(
				array_merge(
					$query_args,
					[
						'fields'         => 'ids',
						'posts_per_page' => -1,
						'tax_query'      => [
							[
								'taxonomy' => hp\prefix( static::$name ),
								'terms'    => $category_ids,
							],
						],
					]
				)
			);

			// Cache count.
			hivepress()->cache->set_term_cache( $this->id, array_merge( $query_args, [ 'cache_type' => 'count' ] ), count( $listing_ids ), 'post/listing', DAY_IN_SECONDS );
		}

		return absint( $count );
	}
}
