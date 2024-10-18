<?php
/**
 * Abstract query.
 *
 * @package HivePress\Queries
 */

namespace HivePress\Queries;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract query class.
 */
abstract class Query extends \ArrayObject {
	use Traits\Mutator;

	/**
	 * Parameter aliases.
	 *
	 * @var array
	 */
	protected $aliases = [];

	/**
	 * WP query arguments.
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * Model object.
	 *
	 * @var object
	 */
	protected $model;

	/**
	 * Is query already executed?
	 *
	 * @var bool
	 */
	protected $executed = false;

	/**
	 * Class constructor.
	 *
	 * @param array $args Query arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'aliases' => [
					'search'    => 'search',
					'limit'     => 'number',
					'offset'    => 'offset',
					'paginate'  => 'paged',

					'select'    => [
						'name'    => 'fields',

						'aliases' => [
							'id' => 'ids',
						],
					],

					'aggregate' => [
						'name'    => 'aggregate',

						'aliases' => [
							'count' => 'count',
						],
					],

					'filter'    => [
						'name' => 'filter',
					],

					'order'     => [
						'name' => 'orderby',
					],
				],
			],
			$args
		);

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->boot();
	}

	/**
	 * Bootstraps query properties.
	 */
	protected function boot() {}

	/**
	 * Gets parameter alias.
	 *
	 * @param string $path Alias path.
	 * @return string
	 */
	final protected function get_alias( $path ) {
		$alias = [ 'aliases' => $this->aliases ];

		foreach ( explode( '/', $path ) as $name ) {
			if ( isset( $alias['aliases'][ $name ] ) ) {
				$alias = $alias['aliases'][ $name ];
			} else {
				return;
			}
		}

		if ( is_array( $alias ) ) {
			$alias = hp\get_array_value( $alias, 'name' );
		}

		return $alias;
	}

	/**
	 * Gets comparison operator.
	 *
	 * @param string $alias Operator alias.
	 * @return string
	 */
	final protected function get_operator( $alias ) {
		return hp\get_array_value(
			[
				'not'         => '!=',
				'gt'          => '>',
				'gte'         => '>=',
				'lt'          => '<',
				'lte'         => '<=',
				'like'        => 'LIKE',
				'not_like'    => 'NOT LIKE',
				'in'          => 'IN',
				'not_in'      => 'NOT IN',
				'between'     => 'BETWEEN',
				'not_between' => 'NOT BETWEEN',
				'exists'      => 'EXISTS',
				'not_exists'  => 'NOT EXISTS',
			],
			strtolower( $alias ),
			'='
		);
	}

	/**
	 * Sets WP query arguments.
	 *
	 * @param array $args Query arguments.
	 * @return object
	 */
	final public function set_args( $args ) {
		$this->args = hp\merge_arrays( $this->args, $args );

		return $this;
	}

	/**
	 * Gets WP query arguments.
	 *
	 * @return array
	 */
	final public function get_args() {
		return $this->args;
	}

	/**
	 * Sets query filters.
	 *
	 * @param array $criteria Filter criteria.
	 * @return object
	 */
	public function filter( $criteria ) {
		foreach ( $criteria as $name => $value ) {

			// Normalize name.
			$name = strtolower( $name );

			if ( $this->get_alias( 'filter/' . $name ) ) {

				// Set query filter.
				$this->args[ $this->get_alias( 'filter/' . $name ) ] = $value;
			} else {

				// Get operator alias.
				$operator_alias = '';

				if ( strpos( $name, '__' ) ) {
					list($name, $operator_alias) = explode( '__', $name );
				}

				// Get field.
				$field = hp\get_array_value( $this->model->_get_fields(), $name );

				if ( $field ) {
					if ( $field->get_arg( '_external' ) ) {

						// Get operator.
						$operator = $this->get_operator( $operator_alias );

						// Set meta clause.
						$clause = [
							'key'     => $field->get_arg( '_alias' ),
							'compare' => $operator,
						];

						if ( ! in_array( $operator, [ 'EXISTS', 'NOT EXISTS' ], true ) ) {

							// Normalize meta value.
							if ( is_bool( $value ) ) {
								$value = $value ? '1' : null;
							}

							if ( is_null( $value ) ) {

								// Set operator.
								$clause['compare'] = 'NOT EXISTS';
							} else {

								// Set meta type and value.
								$clause = array_merge(
									$clause,
									[
										'type'  => $field::get_meta( 'type' ),
										'value' => $value,
									]
								);
							}
						}

						// Normalize operator alias.
						if ( empty( $operator_alias ) ) {
							$operator_alias = 'equals';
						}

						// Set meta filter.
						$this->args['meta_query'][ $name . '__' . $operator_alias ] = $clause;
					} elseif ( $field->get_arg( '_alias' ) && ! $field->get_arg( '_relation' ) ) {

						// Normalize operator alias.
						if ( ! in_array( $operator_alias, [ 'in', 'not_in', 'like' ], true ) ) {
							$operator_alias = '';
						}

						// Set alias filter.
						$this->args[ rtrim( $field->get_arg( '_alias' ) . '__' . $operator_alias, '_' ) ] = $value;
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Sets query order.
	 *
	 * @param array $criteria Order criteria.
	 * @return object
	 */
	public function order( $criteria ) {
		$args = [];

		if ( is_array( $criteria ) ) {
			foreach ( $criteria as $name => $order ) {

				// Normalize order.
				$order = strtoupper( $order );

				if ( in_array( $order, [ 'ASC', 'DESC' ], true ) ) {
					if ( $this->get_alias( 'order/' . $name ) ) {

						// Set query order.
						$args[ $this->get_alias( 'order/' . $name ) ] = $order;
					} else {

						// Get field.
						$field = hp\get_array_value( $this->model->_get_fields(), $name );

						if ( $field ) {
							if ( $field->get_arg( '_external' ) ) {

								// Update field filter.
								$field->update_filter( true );

								// Set meta filter.
								$filter = [
									'key'  => $field->get_arg( '_alias' ),
									'type' => hp\get_array_value( $field->get_filter(), 'type' ),
								];

								// Add meta clause.
								$this->args['meta_query'][] = [
									'relation'        => 'OR',

									$name . '__order' => array_merge(
										$filter,
										[
											'compare' => 'NOT EXISTS',
										]
									),

									array_merge(
										$filter,
										[
											'compare' => 'EXISTS',
										]
									),
								];

								// Set meta order.
								$args[ $name . '__order' ] = $order;
							} elseif ( $field->get_arg( '_alias' ) && ! $field->get_arg( '_relation' ) ) {

								// Set alias order.
								$args[ $field->get_arg( '_alias' ) ] = $order;
							}
						}
					}
				}
			}
		} elseif ( $this->get_alias( 'order/' . $criteria ) ) {

			// Set query order.
			$args = $this->get_alias( 'order/' . $criteria );
		}

		// Set order arguments.
		if ( $args ) {
			$this->args[ $this->get_alias( 'order' ) ] = $args;
		}

		return $this;
	}

	/**
	 * Sets search filter.
	 *
	 * @param string $query Search query.
	 * @return object
	 */
	public function search( $query ) {
		$this->args[ $this->get_alias( 'search' ) ] = $query;

		return $this;
	}

	/**
	 * Limits the number of results.
	 *
	 * @param int $number Objects number.
	 * @return object
	 */
	public function limit( $number ) {
		$this->args[ $this->get_alias( 'limit' ) ] = absint( $number );

		return $this;
	}

	/**
	 * Skips the number of results.
	 *
	 * @param int $number Objects number.
	 * @return object
	 */
	public function offset( $number ) {
		$this->args[ $this->get_alias( 'offset' ) ] = absint( $number );

		return $this;
	}

	/**
	 * Skips the number of pages.
	 *
	 * @param int $number Page number.
	 * @return object
	 */
	public function paginate( $number ) {
		$this->args[ $this->get_alias( 'paginate' ) ] = absint( $number );

		return $this;
	}

	/**
	 * Gets query results.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	abstract protected function get_results( $args );

	/**
	 * Gets objects.
	 *
	 * @return object
	 */
	final public function get() {
		if ( ! $this->executed ) {
			$this->exchangeArray(
				array_map(
					function( $result ) {
						return $this->model->get( $result );
					},
					$this->get_results( $this->args )
				)
			);

			$this->executed = true;
		}

		return $this;
	}

	/**
	 * Gets object IDs.
	 *
	 * @return array
	 */
	public function get_ids() {
		$ids = [];

		if ( $this->executed ) {
			$ids = array_map(
				function( $object ) {
					return $object->get_id();
				},
				$this->serialize()
			);
		} else {
			$ids = array_map(
				'absint',
				$this->get_results(
					array_merge(
						$this->args,
						[
							$this->get_alias( 'select' ) => $this->get_alias( 'select/id' ),
						]
					)
				)
			);
		}

		return $ids;
	}

	/**
	 * Gets the first object.
	 *
	 * @return object
	 */
	final public function get_first() {
		$object = null;

		if ( $this->executed ) {
			$object = hp\get_first_array_value( $this->serialize() );
		} else {
			$query = clone $this;

			$object = hp\get_first_array_value( $query->limit( 1 )->get()->serialize() );
		}

		return $object;
	}

	/**
	 * Gets the first object ID.
	 *
	 * @return int
	 */
	final public function get_first_id() {
		$id = null;

		if ( $this->executed ) {
			$id = hp\get_first_array_value( $this->get_ids() );
		} else {
			$query = clone $this;

			$id = hp\get_first_array_value( $query->limit( 1 )->get_ids() );
		}

		return $id;
	}

	/**
	 * Gets object by ID.
	 *
	 * @param int $id Object ID.
	 * @return object
	 */
	final public function get_by_id( $id ) {
		return $this->model->get( $id );
	}

	/**
	 * Gets object count.
	 *
	 * @return int
	 */
	public function get_count() {
		$count = 0;

		if ( $this->executed ) {
			$count = count( $this->get_ids() );
		} else {
			$count = $this->get_results(
				array_merge(
					$this->args,
					[
						$this->get_alias( 'aggregate/count' ) => true,
					]
				)
			);
		}

		return $count;
	}

	/**
	 * Deletes objects.
	 */
	final public function delete() {
		foreach ( $this->get_ids() as $id ) {
			$this->delete_by_id( $id );
		}
	}

	/**
	 * Deletes object by ID.
	 *
	 * @param int $id Object ID.
	 */
	final public function delete_by_id( $id ) {
		$this->model->delete( $id );
	}

	/**
	 * Gets objects array.
	 *
	 * @todo Fix the return type or class implementation.
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	final public function serialize() {
		return $this->getArrayCopy();
	}
}
