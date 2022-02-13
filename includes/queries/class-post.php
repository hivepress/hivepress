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
 * Queries posts.
 */
class Post extends Query {

	/**
	 * Class constructor.
	 *
	 * @param array $args Query arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'aliases' => [
					'search' => 's',
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

				'args'    => [
					'post_status'         => 'any',
					'posts_per_page'      => -1,
					'orderby'             => [ 'ID' => 'ASC' ],
					'ignore_sticky_posts' => true,
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps query properties.
	 */
	protected function boot() {

		// Set post type.
		$model = $this->model;

		$this->args['post_type'] = $model::_get_meta( 'alias' );

		parent::boot();
	}

	/**
	 * Sets query filters.
	 *
	 * @param array $criteria Filter criteria.
	 * @return object
	 */
	public function filter( $criteria ) {
		parent::filter( $criteria );

		// Replace field aliases.
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

		// Set post IDs.
		if ( isset( $this->args['post__in'] ) && empty( $this->args['post__in'] ) ) {
			$this->args['post__in'] = [ 0 ];
		}

		// Set term filters.
		foreach ( $criteria as $name => $value ) {

			// Normalize name.
			$name = strtolower( $name );

			// Get operator.
			$operator = '=';

			if ( strpos( $name, '__' ) ) {
				list($name, $operator) = explode( '__', $name );

				$operator = $this->get_operator( $operator );
			}

			if ( '=' === $operator ) {
				$operator = 'AND';
			}

			// Get field.
			$field = hp\get_array_value( $this->model->_get_fields(), $name );

			if ( $field && $field->get_arg( '_relation' ) === 'many_to_many' && in_array( $operator, [ 'AND', 'IN', 'NOT IN', 'EXISTS', 'NOT EXISTS' ], true ) ) {

				// Set term clause.
				$clause = [
					'taxonomy' => $field->get_arg( '_alias' ),
					'operator' => $operator,
				];

				if ( ! in_array( $operator, [ 'EXISTS', 'NOT EXISTS' ], true ) ) {

					// Set term IDs.
					$clause['terms'] = array_map( 'absint', (array) $value );
				}

				// Set term filter.
				$this->args['tax_query'][] = $clause;
			}
		}

		return $this;
	}

	/**
	 * Gets query results.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	final protected function get_results( $args ) {
		return get_posts( $args );
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
	 * Trashes objects.
	 */
	final public function trash() {
		foreach ( $this->get_ids() as $id ) {
			$this->trash_by_id( $id );
		}
	}

	/**
	 * Trashes object by ID.
	 *
	 * @param int $id Object ID.
	 */
	final public function trash_by_id( $id ) {
		$this->model->trash( $id );
	}
}
