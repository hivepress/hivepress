<?php
/**
 * Comment model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Comment model class.
 *
 * @class Comment
 */
abstract class Comment extends Model {

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
		return $this->get_id() && wp_delete_comment( $this->get_id(), true ) !== false;
	}
}
