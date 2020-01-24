<?php
/**
 * User component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User component class.
 *
 * @class User
 */
final class User extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Register user.
		add_action( 'hivepress/v1/models/user/register', [ $this, 'register_user' ], 10, 2 );

		// Update user.
		add_action( 'hivepress/v1/models/user/update_first_name', [ $this, 'update_user' ] );

		// Add registration fields.
		add_filter( 'hivepress/v1/forms/user_register', [ $this, 'add_registration_fields' ] );

		// Render user image.
		add_filter( 'get_avatar', [ $this, 'render_user_image' ], 1, 5 );

		if ( ! is_admin() ) {

			// Alter templates.
			add_filter( 'hivepress/v1/templates/site_footer_block', [ $this, 'alter_site_footer_block' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Registers user.
	 *
	 * @param int   $user_id User ID.
	 * @param array $values User values.
	 */
	public function register_user( $user_id, $values ) {

		// Get user.
		$user = Models\User::query()->get_by_id( $user_id );

		// Hide admin bar.
		update_user_meta( $user_id, 'show_admin_bar_front', 'false' );

		// Send emails.
		wp_new_user_notification( $user_id );

		( new Emails\User_Register(
			[
				'recipient' => $user->get_email(),

				'tokens'    => [
					'user_name'     => $user->get_display_name(),
					'user_password' => hp\get_array_value( $values, 'password' ),
				],
			]
		) )->send();
	}

	/**
	 * Updates user.
	 *
	 * @param int $user_id User ID.
	 */
	public function update_user( $user_id ) {

		// Remove action.
		remove_action( 'hivepress/v1/models/user/update_first_name', [ $this, 'update_user' ] );

		// Get user.
		$user = Models\User::query()->get_by_id( $user_id );

		// Update user.
		$user->fill(
			[
				'display_name' => $user->get_first_name() ? $user->get_first_name() : $user->get_username(),
			]
		)->save();
	}

	/**
	 * Adds registration fields.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function add_registration_fields( $form ) {

		// Get terms page ID.
		$page_id = reset(
			( get_posts(
				[
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'post__in'       => [ absint( get_option( 'hp_page_user_registration_terms' ) ) ],
					'posts_per_page' => 1,
					'fields'         => 'ids',
				]
			) )
		);

		if ( $page_id ) {

			// Add terms field.
			$form['fields']['_terms'] = [
				'caption'   => sprintf( hp\sanitize_html( __( 'I agree to the <a href="%s" target="_blank">terms and conditions</a>', 'hivepress' ) ), esc_url( get_permalink( $page_id ) ) ),
				'type'      => 'checkbox',
				'required'  => true,
				'_separate' => true,
				'_order'    => 1000,
			];
		}

		return $form;
	}

	/**
	 * Renders user image.
	 *
	 * @param string $image Image HTML.
	 * @param mixed  $id_or_email User ID.
	 * @param int    $size Image size.
	 * @param string $default Default image.
	 * @param string $alt Image description.
	 * @return string
	 */
	public function render_user_image( $image, $id_or_email, $size, $default, $alt ) {

		// Get user.
		$user_object = null;

		if ( is_object( $id_or_email ) ) {
			$user_object = get_userdata( $id_or_email->user_id );
		} elseif ( is_numeric( $id_or_email ) ) {
			$user_object = get_userdata( $id_or_email );
		} elseif ( is_email( $id_or_email ) ) {
			$user_object = get_user_by( 'email', $id_or_email );
		}

		if ( $user_object ) {
			$user = Models\User::query()->get_by_id( $user_object );

			// Render image.
			if ( $user->get_image__url( 'thumbnail' ) ) {
				$image = '<img src="' . esc_url( $user->get_image__url( 'thumbnail' ) ) . '" class="avatar avatar-' . esc_attr( $size ) . ' photo" height="' . esc_attr( $size ) . '" width="' . esc_attr( $size ) . '" alt="' . esc_attr( $alt ) . '">';
			}
		}

		return $image;
	}

	/**
	 * Alters site footer block.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_site_footer_block( $template ) {
		if ( ! is_user_logged_in() ) {
			$template = hp\merge_trees(
				$template,
				[
					'blocks' => [
						'modals' => [
							'blocks' => [
								'user_login_modal'    => [
									'type'   => 'modal',
									'title'  => esc_html__( 'Sign In', 'hivepress' ),

									'blocks' => [
										'user_login_form' => [
											'type'   => 'user_login_form',
											'_order' => 10,
										],
									],
								],

								'user_register_modal' => [
									'type'   => 'modal',
									'title'  => esc_html__( 'Register', 'hivepress' ),

									'blocks' => [
										'user_register_form' => [
											'type'       => 'form',
											'form'       => 'user_register',
											'_order'     => 10,

											'attributes' => [
												'class' => [ 'hp-form--narrow' ],
											],

											'footer'     => [
												'form_actions' => [
													'type' => 'container',
													'_order' => 10,

													'attributes' => [
														'class' => [ 'hp-form__actions' ],
													],

													'blocks' => [
														'user_login_link' => [
															'type' => 'part',
															'path' => 'user/register/user-login-link',
															'_order' => 10,
														],
													],
												],
											],
										],
									],
								],

								'user_password_request_modal' => [
									'type'   => 'modal',
									'title'  => esc_html__( 'Reset Password', 'hivepress' ),

									'blocks' => [
										'user_password_request_form' => [
											'type'       => 'form',
											'form'       => 'user_password_request',
											'_order'     => 10,

											'attributes' => [
												'class' => [ 'hp-form--narrow' ],
											],
										],
									],
								],
							],
						],
					],
				]
			);
		}

		return $template;
	}
}
