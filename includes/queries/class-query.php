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
	 * Model name.
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
		if ( strpos( $name, 'get_model_' ) === 0 ) {
			$class  = '\HivePress\Models\\' . $this->model;
			$method = str_replace( '_model_', '_', $name );

			if ( class_exists( $class ) && method_exists( $class, $method ) ) {
				return call_user_func_array( [ $class, $method ], $args );
			}
		}
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
			strtolower( $alias )
		);
	}

	/**
	 * Sets object filters.
	 *
	 * @param array $args Filter arguments.
	 * @return object
	 */
	abstract public function filter( $args );

	/**
	 * Sets object order.
	 *
	 * @param array $args Order arguments.
	 * @return object
	 */
	abstract public function order( $args );

	/**
	 * Limits the number of objects.
	 *
	 * @param int $number Objects number.
	 * @return object
	 */
	abstract public function limit( $number );

	/**
	 * Sets the current page number.
	 *
	 * @param int $number Page number.
	 * @return object
	 */
	abstract public function paginate( $number );

	/**
	 * Offsets the number of objects.
	 *
	 * @param int $number Objects number.
	 * @return object
	 */
	abstract public function offset( $number );

	/**
	 * Gets objects.
	 *
	 * @return array
	 */
	abstract public function get_all();

	/**
	 * Gets object IDs.
	 *
	 * @return array
	 */
	abstract public function get_ids();

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
	abstract public function get_count();

	/**
	 * Deletes objects.
	 */
	abstract public function delete();
}
