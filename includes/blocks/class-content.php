<?php
/**
 * Content block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders HTML content.
 */
class Content extends Block {

	/**
	 * HTML content.
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		return $this->content;
	}
}
