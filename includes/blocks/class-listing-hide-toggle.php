<?php
/**
 * Listing hide toggle block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders the listing hide toggle.
 */
class Listing_Hide_Toggle extends Toggle {

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'states' => [
					[
						'icon'    => 'eye-slash',
						'caption' => esc_html__( 'Hide', 'hivepress' ),
					],
					[
						'icon'    => 'eye',
						'caption' => esc_html__( 'Unhide', 'hivepress' ),
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Get listing.
		$listing = $this->get_context( 'listing' );

		if ( hp\is_class_instance( $listing, '\HivePress\Models\Listing' ) ) {
			if ( $listing->get_status() === 'draft' && $listing->get_expired_time() && $listing->get_expired_time() < time() ) {

				// Hide toggle.
				$this->states = [];
			} else {

				// Set URL.
				$this->url = hivepress()->router->get_url(
					'listing_hide_action',
					[
						'listing_id' => $listing->get_id(),
					]
				);

				// Set active flag.
				if ( $listing->get_status() === 'draft' ) {
					$this->active = true;
				}
			}
		}

		parent::boot();
	}
}
