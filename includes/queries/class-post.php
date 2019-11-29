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
	 * Bootstraps query properties.
	 */
	protected function bootstrap() {
		$this->args = [
			'post_type'           => hp\prefix( $this->model ),
			'post_status'         => 'any',
			'posts_per_page'      => -1,
			'ignore_sticky_posts' => true,
			'tax_query'           => [],
			'meta_query'          => [],
		];

		parent::bootstrap();
	}

	/**
	 * Sets object filters.
	 *
	 * @param array $args Filter arguments.
	 * @return object
	 */
	final public function filter( $args ) {
		foreach ( $args as $field_name => $field_value ) {

			// Get operator.
			$operator = '=';

			if ( strpos( $field_name, '__' ) !== false ) {
				list($field_name, $operator_alias) = explode( '__', $field_name );

				$operator = $this->get_operator( $operator_alias );
			}

			if ( 'id' === $field_name ) {

				// Set ID filter.
				if ( 'IN' === $operator && empty( $field_value ) ) {
					$field_value = [ 0 ];
				}

				$this->args[ 'post__' . $operator_alias ] = $field_value;
			} else {

				// Get field name.
				$field_name = $this->todo( $field_name );

				// Get field.
				$field = hp\get_array_value( $this->get_model_aliases(), $field_name );

				if ( ! is_null( $field ) ) {
					$field->set_value( $field_value );

					if ( ! is_null( $field_value ) && $field->get_filters() ) {
						if ( isset( $this->get_model_aliases()[ $field_name ] ) ) {

							// Set alias filter.
							if ( '=' !== $operator ) {
								$this->args[ $field_name ] = $field_value . '__' . $operator;
							} else {
								$this->args[ $field_name ] = $field_value;
							}
						} elseif ( in_array( $field_name, $this->get_model_relations(), true ) ) {

							// Add term filter.
							if ( '=' === $operator ) {
								$operator = 'AND';
							}

							$this->args['tax_query'][] = [
								'taxonomy' => hp\prefix( $field_name ),
								'terms'    => $field_value,
								'operator' => $operator,
							];
						} else {

							// Normalize value.
							if ( is_bool( $field_value ) ) {
								$field_value = $field_value ? 1 : 0;
							}

							if ( in_array( $operator, [ 'EXISTS', 'NOT_EXISTS' ], true ) ) {
								unset( $filters['value'] );
							}

							unset( $filters['operator'] );

							// Add meta filter.
							$this->args['meta_query'][ $field_name . '_todo' ] = array_merge(
								$field->get_filters(),
								[
									'key'     => hp\prefix( $field_name ),
									'type'    => $filters['type'],
									'compare' => $operator,
									'value'   => $field_value,
								]
							);
						}
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Sets object order.
	 *
	 * @param array $args Order arguments.
	 * @return object
	 */
	final public function order( $args ) {
		if ( 'random' === $args ) {
			$this->args['orderby'] = 'rand';
		} else {
			$this->args['orderby'] = [];

			if ( ! is_array( $args ) ) {
				$args = [ $args => 'DESC' ];
			}

			foreach ( $args as $field_name => $field_order ) {
				$field_order = strtoupper( $field_order );

				if ( in_array( $field_order, [ 'ASC', 'DESC' ], true ) ) {
					$field_name = $this->todo( $field_name );

					if ( isset( $this->get_model_aliases()[ $field_name ] ) ) {
						$this->args['orderby'][ preg_replace( '/^post_/', '', $field_name ) ] = $field_order;
					} elseif ( ! isset( $this->get_model_relations()[ $field_name ] ) ) {
						$this->args['orderby'][ $field_name . '_todo' ] = $field_order;
					}
				}
				$field_name = hp\get_array_value(
					[
						'',
					],
					$this->todo( $field_name )
				);

				$field_order = hp\get_array_value(
					[
						'asc'  => 'ASC',
						'desc' => 'DESC',
					],
					strtolower( $field_order )
				);
				$this->args['orderby'][ preg_replace( '/^post_/', '', $field_name ) ] = strtoupper( $field_order );
			}
		}

		return $this;
	}

	/**
	 * Limits the number of objects.
	 *
	 * @param int $number Objects number.
	 * @return object
	 */
	final public function limit( $number ) {
		$this->args['posts_per_page'] = $number;

		return $this;
	}

	/**
	 * Offsets the number of objects.
	 *
	 * @param int $number Objects number.
	 * @return object
	 */
	final public function offset( $number ) {
		$this->args['offset'] = $number;

		return $this;
	}

	/**
	 * Sets the current page number.
	 *
	 * @param int $number Page number.
	 * @return object
	 */
	final public function paginate( $number ) {
		$this->args['paged'] = $number;

		return $this;
	}

	/**
	 * Gets objects.
	 *
	 * @return array
	 */
	final public function get_all() {
		return array_map(
			function( $post ) {
				return $this->get_model_by_id( $post->ID );
			},
			get_posts( $this->args )
		);
	}

	/**
	 * Gets object IDs.
	 *
	 * @return array
	 */
	final public function get_ids() {
		return get_terms( array_merge( $this->args, [ 'fields' => 'ids' ] ) );
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
	 * Deletes objects.
	 */
	final public function delete() {
		foreach ( $this->get_ids() as $id ) {
			wp_delete_post( $id, true );
		}
	}

	final public function todo( $alias ) {

		// Get name.
		$name = hp\get_array_value( array_merge( $this->get_model_relations(), $this->get_model_aliases() ), $alias, $alias );

		// Normalize name.
		$name = hp\get_array_value( [ 'post_author' => 'author' ], $name, $name );

		return $name;
	}
}
