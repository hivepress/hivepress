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
 * User account page template class.
 *
 * @class User_Account_Page
 */
class User_Account_Page extends Page {

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
									'page_sidebar' => [
										'type'       => 'container',
										'tag'        => 'aside',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-page__sidebar', 'hp-col-sm-4', 'hp-col-xs-12' ],
											'data-component' => 'sticky',
										],

										'blocks'     => [
											'user_account_menu' => [
												'type'   => 'menu',
												'menu'   => 'user_account',
												'_order' => 10,

												'attributes' => [
													'class' => [ 'hp-widget', 'widget', 'widget_nav_menu' ],
												],
											],

											'page_sidebar_widgets' => [
												'type'   => 'widgets',
												'area'   => 'account_sidebar',
												'_order' => 20,
											],
										],
									],

									'page_content' => [
										'type'       => 'container',
										'tag'        => 'main',
										'_order'     => 20,

										'attributes' => [
											'class' => [ 'hp-page__content', 'hp-col-sm-8', 'hp-col-xs-12' ],
										],

										'blocks'     => [
											'page_title' => [
												'type'   => 'part',
												'path'   => 'page/page-title',
												'_order' => 5,
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
