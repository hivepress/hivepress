<?php
/**
 * Abstract block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract block class.
 *
 * @class Block
 */
abstract class Block {

	/**
	 * Block title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Block attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Class constructor.
	 */
	public function __construct() {

	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	abstract public function render();
}
