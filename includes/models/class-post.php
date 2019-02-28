<?php
/**
 * Post model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Post model class.
 *
 * @class Post
 */
abstract class Post extends Model {

	/**
	 * Saves instance to the database.
	 *
	 * @return bool
	 */
	public function save() {
		// todo.
	}

	/**
	 * Deletes instance from the database.
	 *
	 * @return bool
	 */
	public function delete() {
		return wp_delete_post( $this->get_id(), true ) !== false;
	}
}
