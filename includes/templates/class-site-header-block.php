<?php
/**
 * Site header block template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Site header block (for menus).
 */
class Site_Header_Block extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'site_header_menu' => [
						'type'       => 'container',
						'optional'   => true,
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-menu', 'hp-menu--site-header', 'hp-menu--main' ],
						],

						'blocks'     => [
							'listing_submit_link' => [
								'type'   => 'part',
								'path'   => 'listing/submit/listing-submit-link',
								'_order' => 10,
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
