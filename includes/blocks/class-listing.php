<?php
/**
 * Listing block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing block class.
 *
 * @class Listing
 */
class Listing extends Template {

	/**
	 * Bootstraps block properties.
	 */
	protected function bootstrap() {

		// Get classes.
		$classes = [];

		if ( get_post_meta( get_the_ID(), 'hp_featured', true ) ) {
			$classes[] = 'hp-listing--featured';
		}

		if ( get_post_meta( get_the_ID(), 'hp_verified', true ) ) {
			$classes[] = 'hp-listing--verified';
		}

		// Add classes.
		if ( ! empty( $classes ) ) {
			$blocks = hp\merge_trees(
				[
					'blocks' => [
						'listing_container' => [
							'attributes' => [
								'class' => $classes,
							],
						],
					],
				],
				[ 'blocks' => $this->blocks ],
				'blocks'
			);

			$this->blocks = reset( $blocks );
		}

		parent::bootstrap();
	}
}
