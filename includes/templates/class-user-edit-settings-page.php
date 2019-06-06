<?php
/**
 * User edit settings page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User edit settings page template class.
 *
 * @class User_Edit_Settings_Page
 */
class User_Edit_Settings_Page extends Account_Page {

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
					'page_content' => [
						'blocks' => [
							'user_delete_modal' => [
								'type'    => 'modal',
								'caption' => esc_html__( 'Delete Account', 'hivepress' ),
								'order'   => 5,

								'blocks'  => [
									'user_delete_form' => [
										'type'       => 'form',
										'form'       => 'user_delete',
										'order'      => 10,

										'attributes' => [
											'class' => [ 'hp-form--narrow' ],
										],
									],
								],
							],

							'user_update_form'  => [
								'type'   => 'form',
								'form'   => 'user_update',
								'order'  => 10,

								'footer' => [
									'form_actions' => [
										'type'       => 'container',
										'order'      => 10,

										'attributes' => [
											'class' => [ 'hp-form__actions' ],
										],

										'blocks'     => [
											'user_delete_link' => [
												'type'     => 'element',
												'filepath' => 'user/edit/user-delete-link',
												'order'    => 10,
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
