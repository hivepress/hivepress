<?php
/**
 * Post query.
 *
 * @package HivePress\Queries
 */

namespace HivePress\Queries;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Post query class.
 *
 * @class Post
 */
class Post extends Query {

	/**
	 * Query aliases.
	 *
	 * @var array
	 */
	protected static $aliases = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Query arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'aliases' => [
					'limit'  => 'posts_per_page',

					'filter' => [
						'aliases' => [
							'id__in'     => 'post__in',
							'id__not_in' => 'post__not_in',
						],
					],

					'order'  => [
						'aliases' => [
							'random' => 'rand',
							'id'     => 'ID',
							'id__in' => 'post__in',
						],
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Query arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'args' => [
					'post_status'         => 'any',
					'posts_per_page'      => -1,
					'orderby'             => [ 'ID' => 'ASC' ],
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps query properties.
	 */
	protected function bootstrap() {

		// Set post type.
		$this->args['post_type'] = hp\prefix( $this->model );

		parent::bootstrap();
	}

	/**
	 * Sets object filters.
	 *
	 * @param array $criteria Filter criteria.
	 * @return object
	 */
	public function filter( $criteria ) {
		parent::filter( $criteria );

		// Replace aliases.
		$this->args = array_combine(
			array_map(
				function( $name ) {
					return hp\get_array_value(
						[
							'post_author'         => 'author',
							'post_author__in'     => 'author__in',
							'post_author__not_in' => 'author__not_in',
							'post_status__in'     => 'post_status',
							'post_title'          => 'title',
						],
						$name,
						$name
					);
				},
				array_keys( $this->args )
			),
			$this->args
		);

		// Set IDs.
		if ( isset( $this->args['post__in'] ) && empty( $this->args['post__in'] ) ) {
			$this->args['post__in'] = [ 0 ];
		}

		// Set term filters.
		foreach ( $criteria as $name => $value ) {

			// Normalize name.
			$name = strtolower( $name );

			// Get operator.
			$operator = '=';

			if ( strpos( $name, '__' ) !== false ) {
				list($name, $operator) = explode( '__', $name );

				$operator = $this->get_operator( $operator );
			}

			if ( '=' === $operator ) {
				$operator = 'AND';
			}

			if ( in_array( $name, $this->get_model_relations(), true ) && in_array( $operator, [ 'AND', 'IN', 'NOT IN', 'EXISTS', 'NOT EXISTS' ], true ) ) {

				// Set term clause.
				$clause = [
					'taxonomy' => hp\prefix( array_search( $name, $this->get_model_relations(), true ) ),
					'operator' => $operator,
				];

				if ( ! in_array( $operator, [ 'EXISTS', 'NOT EXISTS' ], true ) ) {

					// Set term IDs.
					$clause['terms'] = array_map( 'absint', (array) $value );
				}

				// Set term filter.
				$this->args['tax_query'][] = $clause;

				// Remove meta clause.
				unset( $this->args['meta_query'][ $name . '_clause' ] );
			}
		}

		return $this;
	}

	/**
	 * Gets object count.
	 *
	 * @return int
	 */
	final public function get_count() {
		return count( $this->get_ids() );
	}

	/**
	 * Gets WordPress objects.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	final protected function get_objects( $args ) {
		return get_posts( $args );
	}
}
