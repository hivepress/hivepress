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
 * Queries comments.
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
	protected function boot() {

		// Set comment type.
		$model = $this->model;

		$this->args['type'] = $model::_get_meta( 'alias' );

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

		// Get field aliases.
		$field_aliases = array_filter(
			array_map(
				function( $field ) {
					return ! $field->get_arg( '_external' ) && ! $field->get_arg( '_relation' ) ? $field->get_arg( '_alias' ) : null;
				},
				$this->model->_get_fields()
			)
		);

		// Replace field aliases.
		$this->args = array_combine(
			array_map(
				function( $name ) use ( $field_aliases ) {

					// Get operator alias.
					$operator_alias = '';

					if ( strpos( $name, '__' ) ) {
						list($name, $operator_alias) = explode( '__', $name );
					}

					// Replace field alias.
					if ( in_array( $name, $field_aliases, true ) ) {
						$name = strtolower( preg_replace( '/^comment_/', '', $name ) );
					}

					$name = rtrim( $name . '__' . $operator_alias, '_' );

					return hp\get_array_value(
						[
							'user_id__in'     => 'author__in',
							'user_id__not_in' => 'author__not_in',
							'post_id__in'     => 'post__in',
							'post_id__not_in' => 'post__not_in',
						],
						$name,
						$name
					);
				},
				array_keys( $this->args )
			),
			$this->args
		);

		// Set comment status.
		if ( isset( $this->args['approved'] ) ) {
			$this->args['status'] = $this->args['approved'] ? 'approve' : 'hold';

			unset( $this->args['approved'] );
		}

		// Set comment IDs.
		if ( isset( $this->args['comment__in'] ) && empty( $this->args['comment__in'] ) ) {
			$this->args['comment__in'] = [ 0 ];
		}

		return $this;
	}

	/**
	 * Gets query results.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	final protected function get_results( $args ) {
		return get_comments( $args );
	}

	/**
	 * Trashes objects.
	 */
	final public function trash() {
		foreach ( $this->get_ids() as $id ) {
			$this->trash_by_id( $id );
		}
	}

	/**
	 * Trashes object by ID.
	 *
	 * @param int $id Object ID.
	 */
	final public function trash_by_id( $id ) {
		$this->model->trash( $id );
	}
}
