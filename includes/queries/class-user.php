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
 * Queries users.
 */
class User extends Query {

	/**
	 * Class constructor.
	 *
	 * @param array $args Query arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'aliases' => [
					'select' => [
						'aliases' => [
							'id' => 'ID',
						],
					],

					'filter' => [
						'aliases' => [
							'id__in'       => 'include',
							'id__not_in'   => 'exclude',
							'role'         => 'role',
							'role__in'     => 'role__in',
							'role__not_in' => 'role__not_in',
						],
					],

					'order'  => [
						'aliases' => [
							'id'     => 'ID',
							'id__in' => 'include',
						],
					],
				],

				'args'    => [
					'orderby'     => 'ID',
					'count_total' => false,
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Sets search filter.
	 *
	 * @param string $query Search query.
	 * @return object
	 */
	public function search( $query ) {
		$query = '*' . $query . '*';

		return parent::search( $query );
	}

	/**
	 * Sets query filters.
	 *
	 * @param array $criteria Filter criteria.
	 * @return object
	 */
	final public function filter( $criteria ) {
		parent::filter( $criteria );

		// Get field aliases.
		$field_aliases = array_filter(
			array_map(
				function( $field ) {
					return ! $field->get_arg( '_external' ) && ! $field->get_arg( '_relation' ) ? $field->get_arg( '_alias' ) : null;
				},
				$this->model->_get_fields()
			)
		);

		// Replace field aliases.
		$this->args = array_combine(
			array_map(
				function( $name ) use ( $field_aliases ) {

					// Get operator alias.
					$operator_alias = '';

					if ( strpos( $name, '__' ) ) {
						list($name, $operator_alias) = explode( '__', $name );
					}

					// Replace alias.
					if ( in_array( $name, $field_aliases, true ) ) {
						$name = strtolower( preg_replace( '/^user_/', '', $name ) );
					}

					return rtrim( $name . '__' . $operator_alias, '_' );
				},
				array_keys( $this->args )
			),
			$this->args
		);

		// Set user IDs.
		if ( isset( $this->args['include'] ) && empty( $this->args['include'] ) ) {
			$this->args['include'] = [ 0 ];
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
		return get_users( $args );
	}

	/**
	 * Gets object count.
	 *
	 * @return int
	 */
	final public function get_count() {
		return count( $this->get_ids() );
	}
}
