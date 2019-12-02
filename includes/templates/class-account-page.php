<?php
/**
 * Account page template.
 *
 * @template account_page
 * @description User account page.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Account page template class.
 *
 * @class Account_Page
 */
class Account_Page extends Page {

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
					'page_container' => [
						'blocks' => [
							'page_columns' => [
								'type'       => 'container',
								'order'      => 10,

								'attributes' => [
									'class' => [ 'hp-row' ],
								],

								'blocks'     => [
									'page_sidebar' => [
										'type'       => 'container',
										'tag'        => 'aside',
										'order'      => 10,

										'attributes' => [
											'class' => [ 'hp-page__sidebar', 'hp-col-sm-4', 'hp-col-xs-12' ],
											'data-component' => 'sticky',
										],

										'blocks'     => [
											'account_menu' => [
												'type'  => 'menu',
												'menu'  => 'account',
												'order' => 10,

												'attributes' => [
													'class' => [ 'hp-widget', 'widget', 'widget_nav_menu' ],
												],
											],

											'sidebar_widgets' => [
												'type'  => 'widgets',
												'area'  => 'account_sidebar',
												'order' => 20,
											],
										],
									],

									'page_content' => [
										'type'       => 'container',
										'tag'        => 'main',
										'order'      => 20,

										'attributes' => [
											'class' => [ 'hp-page__content', 'hp-col-sm-8', 'hp-col-xs-12' ],
										],

										'blocks'     => [
											'page_title' => [
												'type'     => 'element',
												'filepath' => 'page/page-title',
												'order'    => 5,
											],
										],
									],
								],
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
