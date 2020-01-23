<?php
/**
 * Section block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Section block class.
 *
 * @class Section
 */
class Section extends Container {

	/**
	 * Section title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Add title.
		if ( $this->title ) {
			array_unshift(
				$this->blocks,
				new Part(
					[
						'path'    => 'page/section-title',
						'context' => [ 'section_title' => $this->title ],
					]
				)
			);
		}

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [ 'hp-section' ],
			]
		);

		parent::boot();
	}
}
