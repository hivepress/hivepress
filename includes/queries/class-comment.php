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
	 * Bootstraps query properties.
	 */
	protected function bootstrap() {
		$this->args = [
			'type'    => hp\prefix( $this->model ),
			'orderby' => 'comment_ID',
			'order'   => 'ASC',
		];

		parent::bootstrap();
	}

	/**
	 * Sets object filters.
	 *
	 * @param array $args Filter arguments.
	 * @return object
	 */
	final public function filter( $args ) {
		foreach ( $args as $name => $value ) {

			// Get operator.
			$operator = '=';

			$name = strtolower( $name );

			if ( strpos( $name, '__' ) !== false ) {
				list($name, $operator) = explode( '__', $name );

				$operator = $this->get_operator( $operator );
			}

			if ( 'id' === $name ) {
				if ( 'IN' === $operator ) {
					$this->args['comment__in'] = $value;
				} elseif ( 'NOT IN' === $operator ) {
					$this->args['comment__not_in'] = $value;
				}
			} else {
				// todo.
			}
		}

		return $this;
	}

	/**
	 * Sets object order.
	 *
	 * @param array $args Order arguments.
	 * @return object
	 */
	final public function order( $args ) {
		// todo.
		return $this;
	}

	/**
	 * Limits the number of objects.
	 *
	 * @param int $number Objects number.
	 * @return object
	 */
	final public function limit( $number ) {
		$this->args['number'] = absint( $number );

		return $this;
	}

	/**
	 * Sets the current page number.
	 *
	 * @param int $number Page number.
	 * @return object
	 */
	final public function paginate( $number ) {
		$this->args['paged'] = absint( $number );

		return $this;
	}

	/**
	 * Offsets the number of objects.
	 *
	 * @param int $number Objects number.
	 * @return object
	 */
	final public function offset( $number ) {
		$this->args['offset'] = absint( $number );

		return $this;
	}

	/**
	 * Gets objects.
	 *
	 * @return array
	 */
	final public function get_all() {
		return array_map(
			function( $user ) {
				return $this->get_model_by_id( $comment->comment_ID );
			},
			get_comments( $this->args )
		);
	}

	/**
	 * Gets object IDs.
	 *
	 * @return array
	 */
	final public function get_ids() {
		return get_comments( array_merge( $this->args, [ 'fields' => 'ids' ] ) );
	}

	/**
	 * Gets object count.
	 *
	 * @return int
	 */
	final public function get_count() {
		return get_comments( array_merge( $this->args, [ 'count' => true ] ) );
	}

	/**
	 * Deletes objects.
	 */
	final public function delete() {
		foreach ( $this->get_ids() as $id ) {
			wp_delete_comment( $id, true );
		}
	}
}
