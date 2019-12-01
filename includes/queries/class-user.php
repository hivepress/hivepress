<?php
/**
 * User query.
 *
 * @package HivePress\Queries
 */

namespace HivePress\Queries;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User query class.
 *
 * @class User
 */
class User extends Query {

	final protected function get_objects( $this->args ) {
		return get_users( $this->args );
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
	 * Gets all objects.
	 *
	 * @return array
	 */
	final public function get_all() {
		return array_map(
			function( $user ) {
				return $this->get_model_by_id( $user->ID );
			},
			$this->get_objects( $this->args )
		);
	}

	/**
	 * Gets object IDs.
	 *
	 * @return array
	 */
	final public function get_ids() {
		return $this->get_objects( array_merge( $this->args, [ 'fields' => 'ID' ] ) );
	}

	/**
	 * Gets object count.
	 *
	 * @return int
	 */
	final public function get_count() {
		return get_terms( array_merge( $this->args, [ 'count_total' => true ] ) );
	}

	/**
	 * Deletes objects.
	 */
	final public function delete() {
		foreach ( $this->get_ids() as $id ) {
			wp_delete_user( $id );
		}
	}
}
