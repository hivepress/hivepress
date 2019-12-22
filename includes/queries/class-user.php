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
	 * Sets object filters.
	 *
	 * @param array $criteria Filter criteria.
	 * @return object
	 */
	final public function filter( $criteria ) {
		parent::filter( $criteria );

		// Replace aliases.
		$this->args = array_combine(
			array_map(
				function( $name ) {
					$operator = '';

					if ( strpos( $name, '__' ) ) {
						list($name, $operator) = explode( '__', $name );
					}

					if ( in_array( $name, $this->model->_get_aliases(), true ) ) {
						$name = preg_replace( '/^user_/', '', $name );
					}

					return rtrim( $name . '__' . $operator, '_' );
				},
				array_keys( $this->args )
			),
			$this->args
		);

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
