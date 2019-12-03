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
	 * Query model.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Query aliases.
	 *
	 * @var array
	 */
	protected $aliases = [];

	/**
	 * Query arguments.
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Query arguments.
	 */
	public function __construct( $args = [] ) {

		$args = hp\merge_arrays(
			[
				'aliases' => [
					'limit'    => 'number',
					'offset'   => 'offset',
					'paginate' => 'paged',
					'count'    => 'count',

					'select'   => [
						'name'    => 'fields',

						'aliases' => [
							'id' => 'ids',
						],
					],

					'order'    => [
						'name' => 'order',
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
				$class  = '\HivePress\Models\\' . $this->model;
				$method = $prefix . '_' . substr( $name, strlen( $prefix . '_model_' ) );

				if ( class_exists( $class ) && method_exists( $class, $method ) ) {
					return call_user_func_array( [ $class, $method ], $args );
				}

				break;
			}
		}

		throw new \BadMethodCallException();
	}

	/**
	 * Gets query argument alias.
	 *
	 * @param string $path Alias path.
	 * @return string
	 */
	final protected function get_alias( $path ) {
		$alias = [ 'aliases' => $this->aliases ];
		$parts = explode( '/', $path );

		foreach ( $parts as $part ) {
			if ( isset( $alias['aliases'][ $part ] ) ) {
				$alias = $alias['aliases'][ $part ];
			} else {
				return null;
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
	 * Gets todo objects.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	abstract protected function get_objects( $args );

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
	 * @return object
	 */
	final public function get_ids() {
		$this->args[ $this->get_alias( 'select' ) ] = $this->get_alias( 'select/id' );

		$this->exchangeArray( $this->get_objects( $this->args ) );

		return $this;
	}

	/**
	 * Gets the first object.
	 *
	 * @return mixed
	 */
	final public function get_first() {
		return hp\get_array_value( $this->limit( 1 )->get_all(), 0 );
	}

	/**
	 * Gets the first object ID.
	 *
	 * @return mixed
	 */
	final public function get_first_id() {
		return hp\get_array_value( $this->limit( 1 )->get_ids(), 0 );
	}

	/**
	 * Gets object count.
	 *
	 * @return int
	 */
	public function get_count() {
		return $this->get_objects( array_merge( $this->args, [ $this->get_alias( 'count' ) => true ] ) );
	}

	/**
	 * Deletes objects.
	 */
	final public function delete() {
		foreach ( $this->get_ids() as $id ) {
			$this->delete_model_by_id( $id );
		}
	}
























	/**
	 * Sets object filters.
	 *
	 * @param array $criteria Filtering criteria.
	 * @return object
	 */
	public function filter( $criteria ) {
		foreach ( $criteria as $name => $value ) {
			if ( $this->get_alias( 'filter/' . $name ) ) {
				$this->args[ $this->get_alias( 'filter/' . $name ) ] = $value;
			} else {
				$operator = '';

				if ( strpos( $name, '__' ) !== false ) {
					list($name, $operator) = explode( '__', $name );
				}

				if ( in_array( $name, static::get_model_aliases(), true ) ) {
					$this->args[ rtrim( array_search( $name, static::get_model_aliases(), true ) . '__' . $operator, '_' ) ] = $value;
				} elseif ( isset( $this->get_model_fields()[ $name ] ) ) {
					$operator = $this->get_operator( $operator );

					$filter = [
						'key'     => hp\prefix( $name ),
						'compare' => $operator,
					];
					if ( is_bool( $value ) ) {
						$value = $value ? '1' : '0';
					}
					if ( ! in_array( $operator, [ 'EXISTS', 'NOT EXISTS' ] ) ) {
						$filter = array_merge(
							$filter,
							[
								'type'  => 'todo',
								'value' => $value,
							]
						);
					}

					$this->args['meta_query'][ $name . '_clause' ] = $filter;
				}
			}
		}

		return $this;
	}

	/**
	 * Sets object order.
	 *
	 * @param array $criteria Ordering criteria.
	 * @return object
	 */
	public function order( $criteria ) {
		$args = [];

		if ( is_array( $criteria ) ) {
			$args[ $this->get_alias( 'order' ) ] = [];

			foreach ( $criteria as $name => $order ) {
				$order = strtoupper( $order );

				if ( in_array( $order, [ 'ASC', 'DESC' ] ) ) {
					if ( $this->get_alias( 'order/' . $name ) ) {
						$args[ $this->get_alias( 'order/' . $name ) ] = $order;
					} elseif ( in_array( $name, static::get_model_aliases(), true ) ) {
						$args[ array_search( $name, static::get_model_aliases(), true ) ] = $order;
					} elseif ( isset( $this->get_model_fields()[ $name ] ) ) {
						$args[ $name . '_clause' ] = $order;
					}
				}
			}
		} elseif ( $this->get_alias( 'order/' . $criteria ) ) {
			$args = $this->get_alias( 'order/' . $criteria );
		}

		$this->args[$this->get_alias('order')]=$args;

		return $this;
	}
}
