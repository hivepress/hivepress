<?php
/**
 * Abstract form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract form class.
 *
 * @class Form
 */
abstract class Form {

	/**
	 * Form attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Class constructor.
	 */
	public function __construct() {

	}
}
