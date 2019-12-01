<?php
/**
 * Term query.
 *
 * @package HivePress\Queries
 */

namespace HivePress\Queries;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Term query class.
 *
 * @class Term
 */
class Term extends Query {

	/**
	 * Bootstraps query properties.
	 */
	protected function bootstrap() {
		$this->args = [
			'taxonomy'   => hp\prefix( $this->model ),
			'orderby'    => 'term_id',
			'hide_empty' => false,
		];

		parent::bootstrap();
	}

	final protected function get_objects( $this->args ) {
		return get_terms( $this->args );
	}

	/**
	 * Sets object filters.
	 *
	 * @param array $args Filter arguments.
	 * @return object
	 */
	final public function filter( $args ) {
		foreach ( $args as $name => $value ) {

			// Get operator.
			$operator = '=';

			$name = strtolower( $name );

			if ( strpos( $name, '__' ) !== false ) {
				list($name, $operator) = explode( '__', $name );

				$operator = $this->get_operator( $operator );
			}

			if ( 'id' === $name ) {
				if ( 'IN' === $operator ) {
					$this->args['include'] = $value;
				} elseif ( 'NOT IN' === $operator ) {
					$this->args['exclude'] = $value;
				}
			} else {
				// todo.
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
		// todo.
		return $this;
	}

	/**
	 * Sets the current page number.
	 *
	 * @param int $number Page number.
	 * @return object
	 */
	final public function paginate( $number ) {
		$this->args['offset'] = hp\get_array_value( $this->args, 'number', 0 ) * ( absint( $number ) - 1 );

		return $this;
	}

	/**
	 * Gets all objects.
	 *
	 * @return array
	 */
	final public function get_all() {
		return array_map(
			function( $term ) {
				return $this->get_model_by_id( $term->term_id );
			},
			$this->get_objects( $this->args )
		);
	}

	/**
	 * Deletes objects.
	 */
	final public function delete() {
		foreach ( $this->get_ids() as $id ) {
			wp_delete_term( $id, hp\prefix( $this->model ) );
		}
	}
}
