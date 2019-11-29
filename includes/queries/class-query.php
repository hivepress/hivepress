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
	 * Gets model class.
	 *
	 * @return string
	 */
	final protected function get_model() {
		return '\HivePress\Models\\' . $this->model;
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

		// Get operator.
		$operator = hp\get_array_value(
			[
				'not' => '!=',
				'gt'  => '>',
				'gte' => '>=',
				'lt'  => '<',
				'lte' => '<=',
			],
			$alias,
			$alias
		);

		// Normalize operator.
		$operator = strtoupper( str_replace( '_', ' ', $operator ) );

		return $operator;
	}
}
