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
 * Queries taxonomy terms.
 */
class Term extends Query {

	/**
	 * Class constructor.
	 *
	 * @param array $args Query arguments.
	 */
	public function __construct( $args = [] ) {
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

				'args'    => [
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
	protected function boot() {

		// Set taxonomy.
		$model = $this->model;

		$this->args['taxonomy'] = $model::_get_meta( 'alias' );

		parent::boot();
	}

	/**
	 * Sets query filters.
	 *
	 * @param array $criteria Filter criteria.
	 * @return object
	 */
	final public function filter( $criteria ) {
		parent::filter( $criteria );

		// Replace field aliases.
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

		// Set term IDs.
		if ( isset( $this->args['include'] ) && empty( $this->args['include'] ) ) {
			$this->args['name'] = md5( time() );
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
		parent::order( $criteria );

		$args = hp\get_array_value( $this->args, $this->get_alias( 'order' ) );

		if ( is_array( $args ) && $args ) {
			$this->args[ $this->get_alias( 'order' ) ] = hp\get_first_array_value( array_keys( $args ) );
			$this->args['order']                       = hp\get_first_array_value( $args );
		}

		return $this;
	}

	/**
	 * Skips the number of pages.
	 *
	 * @param int $number Page number.
	 * @return object
	 */
	final public function paginate( $number ) {
		$this->args[ $this->get_alias( 'offset' ) ] = hp\get_array_value( $this->args, 'number', 0 ) * ( absint( $number ) - 1 );

		return $this;
	}

	/**
	 * Gets query results.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	final protected function get_results( $args ) {
		return get_terms( $args );
	}
}
