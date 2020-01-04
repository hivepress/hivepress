<?php
/**
 * User password reset page template.
 *
 * @template user_password_reset_page
 * @description User password reset page.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User password reset page template class.
 *
 * @class User_Password_Reset_Page
 */
class User_Password_Reset_Page extends Page {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_container' => [
						'blocks' => [
							'page_columns' => [
								'type'       => 'container',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-row' ],
								],

								'blocks'     => [
									'page_content' => [
										'type'       => 'container',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-page__content', 'hp-col-sm-4', 'hp-col-sm-offset-4', 'hp-col-xs-12' ],
										],

										'blocks'     => [
											'page_title' => [
												'type'   => 'part',
												'path'   => 'page/page-title',
												'_order' => 5,
											],

											'user_password_reset_form' => [
												'type'   => 'user_password_reset_form',
												'_order' => 10,
											],
										],
									],
								],
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
