<?php
/**
 * Header block template.
 *
 * @template header_block
 * @description Site header block.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Header block template class.
 *
 * @class Header_Block
 */
class Header_Block extends Template {

	/**
	 * Template blocks.
	 *
	 * @var array
	 */
	protected static $blocks = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Template arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'main_menu' => [
						'type'       => 'container',
						'order'      => 10,

						'attributes' => [
							'class' => [ 'hp-menu', 'hp-menu--main' ],
						],

						'blocks'     => [
							'user_account_link'   => [
								'type'     => 'element',
								'filepath' => 'user/login/user-login-link',
								'order'    => 10,
							],

							'listing_submit_link' => [
								'type'     => 'element',
								'filepath' => 'listing/submit/listing-submit-link',
								'order'    => 20,
							],
						],
					],
				],
			],
			$args,
			'blocks'
		);

		parent::init( $args );
	}
}
