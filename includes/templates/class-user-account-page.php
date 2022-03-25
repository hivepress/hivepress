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
					'page_sidebar' => [
						'attributes' => [
							'data-component' => 'sticky',
						],

						'blocks'     => [
							'user_account_menu'    => [
								'type'       => 'menu',
								'menu'       => 'user_account',
								'_label'     => hivepress()->translator->get_string( 'menu' ),
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-widget', 'widget', 'widget_nav_menu' ],
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
