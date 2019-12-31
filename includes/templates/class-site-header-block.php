<?php
/**
 * Site header block template.
 *
 * @template site_header_block
 * @description Site header block.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Site header block template class.
 *
 * @class Site_Header_Block
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
					'main_menu' => [
						'type'       => 'container',
						'_order'     => 10,

						'attributes' => [
							'class' => [ 'hp-menu', 'hp-menu--main' ],
						],

						'blocks'     => [
							'user_account_link'   => [
								'type'   => 'part',
								'path'   => 'user/login/user-login-link',
								'_order' => 10,
							],

							'listing_submit_link' => [
								'type'   => 'part',
								'path'   => 'listing/submit/listing-submit-link',
								'_order' => 20,
							],
						],
					],
				],
			],
			$args,
			'blocks'
		);

		parent::__construct( $args );
	}
}
