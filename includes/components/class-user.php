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
use HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles users.
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

		// Login user.
		add_filter( 'authenticate', [ $this, 'login_user' ], 100 );

		// Update user.
		add_action( 'hivepress/v2/models/user/update', [ $this, 'update_user' ], 10, 2 );

		// Alter registration form.
		add_filter( 'hivepress/v1/forms/user_register', [ $this, 'alter_register_form' ] );

		// Alter model fields.
		add_filter( 'hivepress/v1/models/user', [ $this, 'alter_model_fields' ] );

		// Render user image.
		add_filter( 'get_avatar', [ $this, 'render_user_image' ], 1, 5 );

		if ( is_admin() ) {

			// Manage admin columns.
			if ( get_option( 'hp_user_verify_email' ) ) {
				add_filter( 'manage_users_columns', [ $this, 'add_admin_columns' ] );
				add_filter( 'manage_users_custom_column', [ $this, 'render_admin_columns' ], 10, 3 );
			}

			// Manage profile fields.
			add_filter( 'hivepress/v1/meta_boxes/user_settings', [ $this, 'add_profile_fields' ] );

			add_action( 'personal_options_update', [ $this, 'update_profile_fields' ], 100 );
			add_action( 'edit_user_profile_update', [ $this, 'update_profile_fields' ], 100 );
		} else {

			// Set request context.
			add_filter( 'hivepress/v1/components/request/context', [ $this, 'set_request_context' ] );

			// Redirect author page.
			add_action( 'template_redirect', [ $this, 'redirect_author_page' ] );

			// Alter templates.
			add_filter( 'hivepress/v1/templates/user_view_block/blocks', [ $this, 'alter_user_view_blocks' ], 10, 2 );
			add_filter( 'hivepress/v1/templates/user_view_page/blocks', [ $this, 'alter_user_view_blocks' ], 10, 2 );

			add_filter( 'hivepress/v1/templates/vendor_view_block/blocks', [ $this, 'alter_vendor_view_blocks' ], 10, 2 );
			add_filter( 'hivepress/v1/templates/vendor_view_page/blocks', [ $this, 'alter_vendor_view_blocks' ], 10, 2 );

			add_filter( 'hivepress/v1/templates/site_footer_block', [ $this, 'alter_site_footer_block' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Checks online status.
	 *
	 * @param object $user User object.
	 * @return bool
	 */
	protected function is_online( $user ) {
		return $user->get_online_time() > time() - 15 * MINUTE_IN_SECONDS;
	}

	/**
	 * Gets online status.
	 *
	 * @param object $user User object.
	 * @return string
	 */
	protected function get_online_status( $user ) {
		$status = __( 'Offline', 'hivepress' );

		if ( $this->is_online( $user ) ) {
			$status = __( 'Online', 'hivepress' );
		} elseif ( $user->get_online_time() ) {

			/* translators: %s: time. */
			$status = sprintf( __( 'Last seen %s ago', 'hivepress' ), human_time_diff( $user->get_online_time(), time() ) );
		}

		return $status;
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

		if ( get_option( 'hp_user_verify_email' ) && ! isset( $values['id'] ) ) {
			return;
		}

		( new Emails\User_Register(
			[
				'recipient' => $user->get_email(),

				'tokens'    => [
					'user'          => $user,
					'user_name'     => $user->get_display_name(),
					'user_password' => hp\get_array_value( $values, 'password' ),
				],
			]
		) )->send();
	}

	/**
	 * Logins user.
	 *
	 * @param WP_User $user User object.
	 */
	public function login_user( $user ) {

		// Check email verification.
		if ( get_option( 'hp_user_verify_email' ) && hp\is_class_instance( $user, 'WP_User' ) && $user->hp_email_verify_key ) {
			return new \WP_Error( 'email_not_verified', esc_html__( 'Please check your email to activate your account.', 'hivepress' ) );
		}

		return $user;
	}

	/**
	 * Updates user.
	 *
	 * @param int    $user_id User ID.
	 * @param object $user User object.
	 */
	public function update_user( $user_id, $user ) {

		// Remove action.
		remove_action( 'hivepress/v2/models/user/update', [ $this, 'update_user' ] );

		// Get display name.
		$display_name = null;

		switch ( get_option( 'hp_user_display_name' ) ) {
			case 'first_name':
				$display_name = $user->get_first_name();

				break;

			case 'first_name_extra':
				$display_name = $user->get_first_name();

				if ( $user->get_last_name() ) {
					$display_name .= ' ' . mb_substr( $user->get_last_name(), 0, 1 ) . '.';
				}

				break;

			case 'last_name':
				$display_name = $user->get_last_name();

				break;

			case 'last_name_extra':
				if ( $user->get_first_name() ) {
					$display_name = mb_substr( $user->get_first_name(), 0, 1 ) . '. ';
				}

				$display_name .= $user->get_last_name();

				break;

			case 'full_name':
				$display_name = $user->get_full_name();

				break;
		}

		if ( ! $display_name ) {
			$display_name = $user->get_username();
		}

		// Update display name.
		$user->set_display_name( $display_name )->save_display_name();
	}

	/**
	 * Alters registration form.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function alter_register_form( $form ) {
		if ( get_option( 'hp_user_verify_email' ) ) {

			// Set form message.
			$form['message']  = esc_html__( 'Please check your email to activate your account.', 'hivepress' );
			$form['redirect'] = false;

			// Add redirect field.
			$form['fields']['_redirect'] = [
				'type'         => 'url',
				'display_type' => 'hidden',
				'default'      => hp\get_array_value( $_GET, 'redirect' ),
				'_separate'    => true,
			];
		}

		// Add username field.
		if ( ! get_option( 'hp_user_generate_username' ) ) {
			$form['fields']['username'] = [
				'label'      => esc_html__( 'Username', 'hivepress' ),
				'type'       => 'text',
				'max_length' => 60,
				'required'   => true,
				'_order'     => 5,

				'attributes' => [
					'autocomplete' => 'username',
				],
			];
		}

		// Get terms page ID.
		$page_id = absint( get_option( 'hp_page_user_registration_terms' ) );

		if ( $page_id ) {

			// Get terms page URL.
			$page_url = get_permalink( $page_id );

			if ( $page_url ) {

				// Add terms field.
				$form['fields']['_terms'] = [
					'caption'   => sprintf( hivepress()->translator->get_string( 'i_agree_to_terms_and_conditions' ), esc_url( $page_url ) ),
					'type'      => 'checkbox',
					'required'  => true,
					'_separate' => true,
					'_order'    => 1000,
				];
			}
		}

		return $form;
	}

	/**
	 * Alters model fields.
	 *
	 * @param array $model Model arguments.
	 * @return array
	 */
	public function alter_model_fields( $model ) {
		if ( get_option( 'hp_user_display_online' ) ) {
			$model['fields']['online_time'] = [
				'type'      => 'number',
				'min_value' => 0,
				'_external' => true,
			];
		}

		return $model;
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

		// Check ID.
		if ( ! $id_or_email ) {
			return $image;
		}

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
				$image = '<img src="' . esc_url( $user->get_image__url( 'thumbnail' ) ) . '" class="avatar avatar-' . esc_attr( $size ) . ' photo" height="' . esc_attr( $size ) . '" width="' . esc_attr( $size ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy">';
			}
		}

		return $image;
	}

	/**
	 * Adds admin columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function add_admin_columns( $columns ) {
		return array_merge(
			array_slice( $columns, 0, 5, true ),
			[
				'verified' => '',
			],
			array_slice( $columns, 5, null, true )
		);
	}

	/**
	 * Renders admin columns.
	 *
	 * @param string $output Output.
	 * @param string $column Column name.
	 * @param int    $user_id User ID.
	 */
	public function render_admin_columns( $output, $column, $user_id ) {
		if ( 'verified' === $column && get_user_meta( $user_id, 'hp_email_verify_key', true ) ) {
			$output = '<div class="hp-status hp-status--draft"><span>' . esc_html_x( 'Unverified', 'user', 'hivepress' ) . '</span></div>';
		}

		return $output;
	}

	/**
	 * Adds admin profile fields.
	 *
	 * @param array $meta_box Meta box arguments.
	 * @return array
	 */
	public function add_profile_fields( $meta_box ) {

		// Check permissions.
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}

		// Get user ID.
		$user_id = absint( hp\get_array_value( $_GET, 'user_id' ) );

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( get_user_meta( $user_id, 'hp_email_verify_key', true ) ) {
			$meta_box['fields']['email_verified'] = [
				'caption' => esc_html__( 'Confirm the email verification', 'hivepress' ),
				'type'    => 'checkbox',
				'_order'  => 15,
			];
		}

		return $meta_box;
	}

	/**
	 * Updates admin profile fields.
	 *
	 * @param int $user_id User ID.
	 */
	public function update_profile_fields( $user_id ) {

		// Check permissions.
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}

		// Delete verification key.
		if ( hp\get_array_value( $_POST, 'hp_email_verified' ) && get_user_meta( $user_id, 'hp_email_verify_key', true ) ) {
			delete_user_meta( $user_id, 'hp_email_verified' );
			delete_user_meta( $user_id, 'hp_email_verify_key' );
		}
	}

	/**
	 * Sets request context.
	 *
	 * @param array $context Request context.
	 * @return array
	 */
	public function set_request_context( $context ) {
		if ( get_option( 'hp_user_display_online' ) ) {
			$user = $context['user'];

			if ( ! $this->is_online( $user ) ) {
				$user->set_online_time( time() )->save_online_time();
			}
		}

		return $context;
	}

	/**
	 * Redirect author page.
	 */
	public function redirect_author_page() {

		// Check settings.
		if ( ! get_option( 'hp_user_enable_display' ) || ! is_author() ) {
			return;
		}

		// Redirect user.
		wp_safe_redirect( hivepress()->router->get_url( 'user_view_page', [ 'username' => get_the_author_meta( 'user_login' ) ] ) );

		exit;
	}

	/**
	 * Alters user view blocks.
	 *
	 * @param array  $blocks Block arguments.
	 * @param object $template Template object.
	 * @return array
	 */
	public function alter_user_view_blocks( $blocks, $template ) {

		// Get user.
		$user = $template->get_context( 'user' );

		if ( $user && get_option( 'hp_user_display_online' ) ) {
			$blocks = hivepress()->template->merge_blocks(
				$blocks,
				[
					'user_name' => [
						'blocks' => [
							'user_online_badge' => [
								'type'    => 'part',
								'path'    => 'user/view/user-online-badge',
								'_order'  => 5,

								'context' => [
									'user_online'        => $this->is_online( $user ),
									'user_online_status' => $this->get_online_status( $user ),
								],
							],
						],
					],
				]
			);
		}

		return $blocks;
	}

	/**
	 * Alters vendor view blocks.
	 *
	 * @param array  $blocks Block arguments.
	 * @param object $template Template object.
	 * @return array
	 */
	public function alter_vendor_view_blocks( $blocks, $template ) {

		// Get vendor.
		$vendor = $template->get_context( 'vendor' );

		if ( $vendor && get_option( 'hp_user_display_online' ) ) {

			// Get user.
			$user = $vendor->get_user();

			if ( $user ) {
				$blocks = hivepress()->template->merge_blocks(
					$blocks,
					[
						'vendor_name' => [
							'blocks' => [
								'user_online_badge' => [
									'type'    => 'part',
									'path'    => 'user/view/user-online-badge',
									'_order'  => 5,

									'context' => [
										'user_online' => $this->is_online( $user ),
										'user_online_status' => $this->get_online_status( $user ),
									],
								],
							],
						],
					]
				);
			}
		}

		return $blocks;
	}

	/**
	 * Alters site footer block.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_site_footer_block( $template ) {
		return hivepress()->template->merge_blocks(
			$template,
			[
				'modals' => [
					'blocks' => [
						'user_login_modal'            => [
							'type'        => 'modal',
							'title'       => esc_html__( 'Sign In', 'hivepress' ),
							'_capability' => 'login',

							'blocks'      => [
								'user_login_form' => [
									'type'   => 'user_login_form',
									'_order' => 10,
								],
							],
						],

						'user_register_modal'         => [
							'type'        => 'modal',
							'title'       => esc_html__( 'Register', 'hivepress' ),
							'_capability' => 'login',

							'blocks'      => [
								'user_register_form' => [
									'type'   => 'user_register_form',
									'_order' => 10,
								],
							],
						],

						'user_password_request_modal' => [
							'type'        => 'modal',
							'title'       => esc_html__( 'Reset Password', 'hivepress' ),
							'_capability' => 'login',

							'blocks'      => [
								'user_password_request_form' => [
									'type'   => 'form',
									'form'   => 'user_password_request',
									'_order' => 10,
								],
							],
						],
					],
				],
			]
		);
	}
}
