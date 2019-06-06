<?php
/**
 * User login page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User login page template class.
 *
 * @class User_Login_Page
 */
class User_Login_Page extends Page {

	/**
	 * Template name.
	 *
	 * @var string
	 */
	protected static $name;

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
									'page_content' => [
										'type'       => 'container',
										'order'      => 10,

										'attributes' => [
											'class' => [ 'hp-page__content', 'hp-col-sm-4', 'hp-col-sm-offset-4', 'hp-col-xs-12' ],
										],

										'blocks'     => [
											'page_title' => [
												'type'     => 'element',
												'filepath' => 'page/page-title',
												'order'    => 5,
											],

											'user_login_form' => [
												'type'  => 'user_login_form',
												'order' => 10,
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
