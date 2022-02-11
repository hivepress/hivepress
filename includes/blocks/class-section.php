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
 * Renders a section of blocks.
 */
class Section extends Container {

	/**
	 * Section title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'optional' => true,
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Add title.
		if ( $this->title ) {
			$this->header['section_title'] = [
				'type'    => 'part',
				'path'    => 'page/section-title',
				'context' => [ 'section_title' => $this->title ],
				'_order'  => 5,
			];
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
