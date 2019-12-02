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
 * Content block class.
 *
 * @class Content
 */
class Content extends Block {

	/**
	 * Block content.
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
