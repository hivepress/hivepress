<?php
/**
 * Comment query.
 *
 * @package HivePress\Queries
 */

namespace HivePress\Queries;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Comment query class.
 *
 * @class Comment
 */
class Comment extends Query {

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
							'id__in'     => 'comment__in',
							'id__not_in' => 'comment__not_in',
						],
					],

					'order'  => [
						'aliases' => [
							'id'     => 'comment_ID',
							'id__in' => 'comment__in',
						],
					],
				],

				'args'    => [
					'orderby' => [ 'comment_ID' => 'ASC' ],
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

		// Set comment type.
		$this->args['type'] = hp\prefix( $this->model );

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

		// Remove prefixes.
		$this->args = array_combine(
			array_map(
				function( $name ) {
					$operator = '';

					if ( strpos( $name, '__' ) !== false ) {
						list($name, $operator) = explode( '__', $name );
					}

					if ( in_array( $name, $this->get_model_aliases(), true ) ) {
						$name = preg_replace( '/^comment_/', '', $name );
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
	 * Gets WordPress objects.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	final protected function get_objects( $args ) {
		return get_comments( $args );
	}
}
