<?php
/**
 * Post query.
 *
 * @package HivePress\Queries
 */

namespace HivePress\Queries;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Post query class.
 *
 * @class Post
 */
class Post extends Query {

	/**
	 * Bootstraps query properties.
	 */
	protected function bootstrap() {
		$this->args = [
			'post_type'           => hp\prefix( $this->model ),
			'post_status'         => 'any',
			'posts_per_page'      => -1,
			'ignore_sticky_posts' => true,
			'tax_query'           => [],
			'meta_query'          => [],
		];

		parent::bootstrap();
	}

	final protected function get_objects( $args ) {
		return get_posts( $this->args );
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
		$this->args['posts_per_page'] = $number;

		return $this;
	}

	

	/**
	 * Gets object count.
	 *
	 * @return int
	 */
	final public function get_count() {
		return count( $this->get_ids() );
	}

	/**
	 * Deletes objects.
	 */
	final public function delete() {
		foreach ( $this->get_ids() as $id ) {
			wp_delete_post( $id, true );
		}
	}

	final public function todo( $alias ) {

		// Get name.
		$name = hp\get_array_value( array_merge( $this->get_model_relations(), $this->get_model_aliases() ), $alias, $alias );

		// Normalize name.
		$name = hp\get_array_value( [ 'post_author' => 'author' ], $name, $name );

		return $name;
	}
}
