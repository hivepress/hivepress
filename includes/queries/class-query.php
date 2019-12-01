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
abstract class Query {
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
	}

	/**
	 * Gets query argument alias.
	 *
	 * @param string $name Argument name.
	 * @return string
	 */
	final protected function get_alias( $name ) {
		return hp\get_array_value( $this->aliases, $name, $name );
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
		$this->args[ $this->get_alias( 'todo' ) ] = absint( $number );

		return $this;
	}

	/**
	 * Gets all objects.
	 *
	 * @return array
	 */
	final public function get_all() {
		return array_map(
			function( $object ) {
				return $this->get_model_from_todo( $object );
			},
			$this->get_objects( $this->args )
		);
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
	 * @param array $args Filter arguments.
	 * @return object
	 */
	final public function filter( $args ) {

	}

	/**
	 * Sets object order.
	 *
	 * @param array $args Order arguments.
	 * @return object
	 */
	final public function order( $args ) {

	}

	/**
	 * Gets object IDs.
	 *
	 * @return array
	 */
	public function get_ids() {
		return $this->get_objects( array_merge( $this->args, [ 'fields' => 'ids' ] ) );
	}


}
