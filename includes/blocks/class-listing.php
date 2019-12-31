<?php
/**
 * Listing block.
 *
 * @package HivePress\Blocks
 */
// todo.
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
	protected function boot() {

		// Set template.
		if ( ! isset( $this->template ) ) {
			$this->template = 'view';
		}

		$this->template = 'listing_' . $this->template . '_block';

		// Get classes.
		$classes = [];

		// todo.
		//if ( $this->context['listing']->is_featured() ) {
			//$classes[] = 'hp-listing--featured';
		//}

		//if ( $this->context['listing']->is_verified() ) {
			//$classes[] = 'hp-listing--verified';
		//}

		// Add classes.
		if ( ! empty( $classes ) ) {
			$this->blocks = hp\merge_trees(
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
			)['blocks'];
		}

		parent::boot();
	}
}
