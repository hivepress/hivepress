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
	 * Query aliases.
	 *
	 * @var array
	 */
	protected static $aliases = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Query arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'aliases' => [
					'filter' => [
						'aliases' => [
							'id__in'     => 'include',
							'id__not_in' => 'exclude',
						],
					],

					'order'  => [
						'aliases' => [
							'id'     => 'term_id',
							'id__in' => 'include',
						],
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Query arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'args' => [
					'orderby'    => 'term_id',
					'hide_empty' => false,
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps query properties.
	 */
	protected function bootstrap() {

		// Set taxonomy.
		$this->args['taxonomy'] = hp\prefix( $this->model );

		parent::bootstrap();
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
					return hp\get_array_value(
						[
							'name__in' => 'name',
							'slug__in' => 'slug',
						],
						$name,
						$name
					);
				},
				array_keys( $this->args )
			),
			$this->args
		);

		return $this;
	}

	/**
	 * Sets object order.
	 *
	 * @param array $criteria Order criteria.
	 * @return object
	 */
	public function order( $criteria ) {
		parent::order( $criteria );

		$args = hp\get_array_value( $this->args, $this->get_alias( 'order' ) );

		if ( is_array( $args ) && ! empty( $args ) ) {
			$this->args[ $this->get_alias( 'order' ) ] = array_keys( $args )[0];
			$this->args['order']                       = reset( $args );
		}

		return $this;
	}

	/**
	 * Offsets the number of pages.
	 *
	 * @param int $number Page number.
	 * @return object
	 */
	final public function paginate( $number ) {
		$this->args[ $this->get_alias( 'offset' ) ] = hp\get_array_value( $this->args, 'number', 0 ) * ( absint( $number ) - 1 );

		return $this;
	}

	/**
	 * Gets WordPress objects.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	final protected function get_objects( $args ) {
		return get_terms( $args );
	}
}
