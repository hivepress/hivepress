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
 * User account settings page.
 */
class User_Edit_Settings_Page extends User_Account_Page {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => hivepress()->translator->get_string( 'user' ) . ' (' . hivepress()->translator->get_string( 'settings' ) . ')',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_content' => [
						'blocks' => [
							'user_delete_modal' => [
								'type'        => 'modal',
								'title'       => esc_html__( 'Delete Account', 'hivepress' ),
								'_capability' => 'read',
								'_parent'     => 'user_update_form',
								'_order'      => 5,

								'blocks'      => [
									'user_delete_form' => [
										'type'   => 'form',
										'form'   => 'user_delete',
										'_order' => 10,
									],
								],
							],

							'user_update_form'  => [
								'type'   => 'form',
								'form'   => 'user_update',
								'_label' => hivepress()->translator->get_string( 'form' ),
								'_order' => 10,

								'footer' => [
									'form_actions' => [
										'type'       => 'container',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-form__actions' ],
										],

										'blocks'     => [
											'user_delete_link' => [
												'type'   => 'part',
												'path'   => 'user/edit/page/user-delete-link',
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
