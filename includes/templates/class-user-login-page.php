<?php
/**
 * User login page template.
 *
 * @template user_login_page
 * @description User login page.
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
	 * Template meta.
	 *
	 * @var array
	 */
	protected static $meta;

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
												'type'     => 'element',
												'filepath' => 'page/page-title',
												'_order'   => 5,
											],

											'user_login_form' => [
												'type'   => 'user_login_form',
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
			$args,
			'blocks'
		);

		parent::__construct( $args );
	}
}
