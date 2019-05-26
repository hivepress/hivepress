<?php
/**
 * Header block template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		'container' => [
			'type'       => 'container',
			'order'      => 10,

			'attributes' => [
				'class' => [ 'hp-menu', 'hp-menu--main' ],
			],

			'blocks'     => [
				'user_account_link'     => [
					'type'      => 'element',
					'file_path' => 'user/account-link',
					'order'     => 10,
				],

				'listing_submit_button' => [
					'type'      => 'element',
					'file_path' => 'listing/submit-button',
					'order'     => 20,
				],
			],
		],
	],
];
