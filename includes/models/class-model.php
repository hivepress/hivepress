<?php
/**
 * Abstract model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract model class.
 *
 * @class Model
 */
abstract class Model {

	/**
	 * Instance ID.
	 *
	 * @var int
	 */
	private $id;

	/**
	 * Class constructor.
	 */
	public function __construct() {

	}

	/**
	 * Saves instance to the database.
	 */
	abstract public function save();

	/**
	 * Deletes instance from the database.
	 */
	abstract public function delete();
}
