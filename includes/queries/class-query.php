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
 *
 * @class Query
 */
abstract class Query extends \ArrayObject {
	use Traits\Mutator;

	/**
	 * Query aliases.
	 *
	 * @var array
	 */
	protected static $aliases = [];

	/**
	 * Query model.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Query arguments.
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Query arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'aliases' => [
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
			static::set_static_property( $name, $value );
		}
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Query arguments.
	 */
	public function __construct( $args = [] ) {

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->bootstrap();
	}

	/**
	 * Bootstraps query properties.
	 */
	protected function bootstrap() {}

	/**
	 * Routes methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @throws \BadMethodCallException Invalid method.
	 * @return mixed
	 */
	final public function __call( $name, $args ) {
		$prefixes = [ 'get', 'delete' ];

		foreach ( $prefixes as $prefix ) {
			if ( strpos( $name, $prefix . '_model_' ) === 0 ) {
				return hp\call_class_method( '\HivePress\Models\\' . $this->model, $prefix . '_' . substr( $name, strlen( $prefix . '_model_' ) ), $args );
			}
		}

		throw new \BadMethodCallException();
	}

	/**
	 * Gets query alias.
	 *
	 * @param string $path Alias path.
	 * @return mixed
	 */
	final protected static function get_alias( $path ) {
		$alias = [ 'aliases' => static::$aliases ];

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
	final protected static function get_operator( $alias ) {
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
	 * Gets query arguments.
	 *
	 * @return array
	 */
	final public function get_args() {
		return $this->args;
	}

	/**
	 * Sets query arguments.
	 *
	 * @param array $args Query arguments.
	 * @return object
	 */
	final public function set_args( $args ) {
		$this->args = hp\merge_arrays( $this->args, $args );

		return $this;
	}

	/**
	 * Sets object filters.
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

				if ( strpos( $name, '__' ) !== false ) {
					list($name, $operator_alias) = explode( '__', $name );
				}

				if ( in_array( $name, $this->get_model_aliases(), true ) ) {

					// Normalize operator alias.
					if ( ! in_array( $operator_alias, [ 'in', 'not_in', 'like' ], true ) ) {
						$operator_alias = '';
					}

					// Set alias filter.
					$this->args[ rtrim( array_search( $name, $this->get_model_aliases(), true ) . '__' . $operator_alias, '_' ) ] = $value;
				} elseif ( isset( $this->get_model_fields()[ $name ] ) ) {

					// Get field.
					$field = $this->get_model_fields()[ $name ];

					// Get operator.
					$operator = $this->get_operator( $operator_alias );

					// Set meta clause.
					$clause = [
						'key'     => hp\prefix( $name ),
						'compare' => $operator,
					];

					if ( ! in_array( $operator, [ 'EXISTS', 'NOT EXISTS' ], true ) ) {

						// Normalize meta value.
						if ( is_bool( $value ) ) {
							$value = $value ? '1' : '0';
						}

						// Set meta type and value.
						$clause = array_merge(
							$clause,
							[
								'type'  => $field::get_type(),
								'value' => $value,
							]
						);
					}

					// Set meta filter.
					$this->args['meta_query'][ $name . '_clause' ] = $clause;
				}
			}
		}

		return $this;
	}

	/**
	 * Sets object order.
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
					} elseif ( in_array( $name, $this->get_model_aliases(), true ) ) {

						// Set alias order.
						$args[ array_search( $name, $this->get_model_aliases(), true ) ] = $order;
					} elseif ( isset( $this->get_model_fields()[ $name ] ) ) {

						// Set meta order.
						$args[ $name . '_clause' ] = $order;
					}
				}
			}
		} elseif ( $this->get_alias( 'order/' . $criteria ) ) {

			// Set query order.
			$args = $this->get_alias( 'order/' . $criteria );
		}

		// Set order arguments.
		if ( ! empty( $args ) ) {
			$this->args[ $this->get_alias( 'order' ) ] = $args;
		}

		return $this;
	}

	/**
	 * Limits the number of objects.
	 *
	 * @param int $number Objects number.
	 * @return object
	 */
	public function limit( $number ) {
		$this->args[ $this->get_alias( 'limit' ) ] = absint( $number );

		return $this;
	}

	/**
	 * Offsets the number of objects.
	 *
	 * @param int $number Objects number.
	 * @return object
	 */
	public function offset( $number ) {
		$this->args[ $this->get_alias( 'offset' ) ] = absint( $number );

		return $this;
	}

	/**
	 * Offsets the number of pages.
	 *
	 * @param int $number Page number.
	 * @return object
	 */
	public function paginate( $number ) {
		$this->args[ $this->get_alias( 'paginate' ) ] = absint( $number );

		return $this;
	}

	/**
	 * Gets WordPress objects.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	abstract protected function get_objects( $args );

	/**
	 * Gets all objects.
	 *
	 * @return object
	 */
	final public function get_all() {
		$this->exchangeArray(
			array_map(
				function( $object ) {
					return $this->get_model_by_object( $object );
				},
				$this->get_objects( $this->args )
			)
		);

		return $this;
	}

	/**
	 * Gets object IDs.
	 *
	 * @return array
	 */
	public function get_ids() {
		return array_map( 'absint', $this->get_objects( array_merge( $this->args, [ $this->get_alias( 'select' ) => $this->get_alias( 'select/id' ) ] ) ) );
	}

	/**
	 * Gets the first object.
	 *
	 * @return mixed
	 */
	final public function get_first() {
		$query = clone $this;

		return hp\get_array_value( $query->limit( 1 )->get_all()->getArrayCopy(), 0 );
	}

	/**
	 * Gets the first object ID.
	 *
	 * @return mixed
	 */
	final public function get_first_id() {
		$query = clone $this;

		return hp\get_array_value( $query->limit( 1 )->get_ids(), 0 );
	}

	/**
	 * Gets object count.
	 *
	 * @return int
	 */
	public function get_count() {
		return $this->get_objects( array_merge( $this->args, [ $this->get_alias( 'aggregate/count' ) => true ] ) );
	}

	/**
	 * Deletes objects.
	 */
	final public function delete() {
		foreach ( $this->get_ids() as $id ) {
			$this->delete_model_by_id( $id );
		}
	}
}
