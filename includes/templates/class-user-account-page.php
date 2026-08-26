<?php
/**
 * User account page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Base user account page.
 */
class User_Account_Page extends Page_Sidebar_Left {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_header'  => [
						'blocks' => [
							'user_account_menu_link' => [
								'type'    => 'part',
								'path'    => 'user/edit/page/user-account-menu-link',
								'_parent' => 'user_account_menu_container',
								'_order'  => 10,
							],
						],
					],

					'page_sidebar' => [
						'attributes' => [
							'data-component' => 'sticky',
						],

						'blocks'     => [
							'user_account_menu_container' => [
								'type'       => 'container',
								'_label'     => hivepress()->translator->get_string( 'menu' ),
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'widget', 'hp-widget', 'hp-widget--desktop', 'hp-widget--user-account-menu', 'hp-menu' ],
								],

								'blocks'     => [
									'user_account_menu_modal' => [
										'type'       => 'modal',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-modal--mobile' ],
										],

										'blocks'     => [
											'user_account_menu' => [
												'type'       => 'menu',
												'menu'       => 'user_account',
												'_order'     => 10,

												'attributes' => [
													'class' => [ 'widget_nav_menu' ],
												],
											],
										],
									],
								],
							],

							'page_sidebar_widgets' => [
								'type'   => 'widgets',
								'area'   => 'hp_user_account_sidebar',
								'_label' => hivepress()->translator->get_string( 'widgets' ),
								'_order' => 100,
							],
						],
					],

					'page_content' => [],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
