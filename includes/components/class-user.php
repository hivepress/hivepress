<?php
namespace HivePress;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages users.
 *
 * @class User
 */
class User extends Component {

	/**
	 * Array of user data.
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * Class constructor.
	 *
	 * @param array $settings
	 */
	public function __construct( $settings ) {
		parent::__construct( $settings );

		// Initialize user data.
		add_action( 'wp_loaded', [ $this, 'init_data' ] );

		// Register user.
		add_filter( 'hivepress/form/form_fields/user__register', [ $this, 'add_terms_checkbox' ] );
		add_action( 'hivepress/form/submit_form/user__register', [ $this, 'register' ] );

		// Login user.
		add_filter( 'hivepress/form/form_args/user__login', [ $this, 'set_login_form_args' ] );
		add_filter( 'hivepress/form/form_values/user__login', [ $this, 'set_login_form_values' ] );
		add_action( 'hivepress/form/submit_form/user__login', [ $this, 'login' ] );

		// Reset password.
		add_action( 'hivepress/form/submit_form/user__request_password', [ $this, 'request_password' ] );
		add_filter( 'hivepress/form/form_values/user__reset_password', [ $this, 'set_password_form_values' ] );
		add_action( 'hivepress/form/submit_form/user__reset_password', [ $this, 'reset_password' ] );

		// Update user.
		add_filter( 'hivepress/form/form_values/user__update', [ $this, 'set_update_form_values' ] );
		add_action( 'hivepress/form/submit_form/user__update', [ $this, 'update' ] );

		// Delete user.
		add_action( 'hivepress/form/submit_form/user__delete', [ $this, 'delete' ] );

		// Manage user image.
		add_action( 'hivepress/form/upload_file/user__image', [ $this, 'update_image' ] );
		add_action( 'hivepress/form/delete_file/user__image', [ $this, 'update_image' ] );
		add_filter( 'get_avatar', [ $this, 'set_image' ], 1, 5 );

		if ( ! is_admin() ) {

			// Set redirect URL.
			add_filter( 'hivepress/template/redirect_url', [ $this, 'set_redirect_url' ] );

			// Redirect account page.
			add_action( 'hivepress/template/redirect_page/user__account', [ $this, 'redirect_account' ] );

			// Set password template context.
			add_filter( 'hivepress/template/template_context/user_reset_password', [ $this, 'set_password_template_context' ] );
		}
	}

	/**
	 * Initializes user data.
	 */
	public function init_data() {
		if ( is_user_logged_in() ) {
			$this->data = $this->get_data( get_current_user_id() );
		}
	}

	/**
	 * Gets user data.
	 *
	 * @param int $id
	 * @return array
	 */
	private function get_data( $id ) {
		$data = [];

		// Get user.
		$user = get_userdata( $id );

		// Get data.
		if ( false !== $user ) {
			$data = [
				'id'          => $user->ID,
				'email'       => $user->user_email,
				'password'    => $user->user_pass,
				'name'        => $user->display_name,
				'image'       => absint( $user->hp_image ),
				'first_name'  => $user->first_name,
				'last_name'   => $user->last_name,
				'description' => $user->description,
			];
		}

		return $data;
	}

	/**
	 * Routes component functions.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __call( $name, $args ) {
		parent::__call( $name, $args );

		// Get user data.
		if ( strpos( $name, 'get_' ) === 0 ) {
			return hp_get_array_value( $this->data, str_replace( 'get_', '', $name ) );
		}
	}

	/**
	 * Adds terms checkbox.
	 *
	 * @param array $fields
	 * @return array
	 */
	public function add_terms_checkbox( $fields ) {

		// Get page ID.
		$page_id = hp_get_post_id(
			[
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post__in'    => [ absint( get_option( 'hp_page_user_registration_terms' ) ) ],
			]
		);

		// Add checkbox.
		if ( 0 !== $page_id ) {
			$fields['terms'] = [
				'label'    => sprintf( hp_sanitize_html( __( 'I agree to %s', 'hivepress' ) ), '<a href="' . esc_url( get_permalink( $page_id ) ) . '" target="_blank">' . get_the_title( $page_id ) . '</a>' ),
				'type'     => 'checkbox',
				'required' => true,
				'order'    => 1000,
			];
		}

		return $fields;
	}

	/**
	 * Registers user.
	 *
	 * @param array $values
	 */
	public function register( $values ) {

		// Check username.
		if ( isset( $values['username'] ) ) {
			if ( sanitize_user( $values['username'], true ) !== $values['username'] ) {
				hivepress()->form->add_error( esc_html__( 'Username contains invalid characters.', 'hivepress' ) );
			} elseif ( username_exists( $values['username'] ) ) {
				hivepress()->form->add_error( esc_html__( 'This username is already in use.', 'hivepress' ) );
			}
		}

		// Check email.
		if ( email_exists( $values['email'] ) ) {
			hivepress()->form->add_error( esc_html__( 'This email is already registered.', 'hivepress' ) );
		}

		// Check password.
		if ( isset( $values['password'] ) && strlen( $values['password'] ) < 6 ) {
			hivepress()->form->add_error( esc_html__( 'Password is too short.', 'hivepress' ) );
		}

		if ( count( hivepress()->form->get_messages() ) === 0 ) {

			// Get username.
			list($username, $domain) = explode( '@', $values['email'] );

			if ( isset( $values['username'] ) ) {
				$username = $values['username'];
			} else {
				$username = sanitize_user( $username, true );

				if ( empty( $username ) ) {
					$username = 'user';
				}

				while ( username_exists( $username ) ) {
					$username .= wp_rand( 1, 9 );
				}
			}

			// Register user.
			$user_id = wp_create_user( $username, $values['password'], $values['email'] );

			if ( ! is_wp_error( $user_id ) ) {

				// Hide admin bar.
				update_user_meta( $user_id, 'show_admin_bar_front', 'false' );

				// Authenticate user.
				wp_set_auth_cookie( $user_id, true );

				// Send emails.
				wp_new_user_notification( $user_id );

				hivepress()->email->send(
					'user__register',
					[
						'to'           => $values['email'],
						'placeholders' => [
							'user_name'     => $username,
							'user_password' => $values['password'],
						],
					]
				);
			}
		}
	}

	/**
	 * Sets login form arguments.
	 *
	 * @param array $args
	 * @return array
	 */
	public function set_login_form_args( $args ) {
		$url = hp_get_array_value( $_POST, 'redirect' );

		if ( hp_validate_redirect( $url ) ) {
			$args['success_redirect'] = $url;
		}

		return $args;
	}

	/**
	 * Sets login form values.
	 *
	 * @param array $values
	 * @return array
	 */
	public function set_login_form_values( $values ) {
		$url = hp_get_array_value( $_GET, 'redirect' );

		if ( hp_validate_redirect( $url ) ) {
			$values['redirect'] = $url;
		}

		return $values;
	}

	/**
	 * Logins user.
	 *
	 * @param array $values
	 */
	public function login( $values ) {

		// Set credentials.
		$credentials = [
			'user_password' => $values['password'],
			'remember'      => true,
		];

		if ( is_email( $values['username'] ) ) {
			$credentials['user_email'] = $values['username'];
		} else {
			$credentials['user_login'] = $values['username'];
		}

		// Authenticate user.
		$user = wp_signon( $credentials, is_ssl() );

		if ( is_wp_error( $user ) ) {
			hivepress()->form->add_error( esc_html__( 'Username or password is incorrect.', 'hivepress' ) );
		}
	}

	/**
	 * Sends password reset email.
	 *
	 * @param array $values
	 */
	public function request_password( $values ) {

		// Get user.
		$user = false;

		if ( is_email( $values['username'] ) ) {
			$user = get_user_by( 'email', $values['username'] );
		} else {
			$user = get_user_by( 'login', $values['username'] );
		}

		if ( false !== $user ) {

			// Set URL.
			$url = add_query_arg(
				[
					'username' => $user->user_login,
					'key'      => get_password_reset_key( $user ),
				],
				hivepress()->template->get_url( 'user__reset_password' )
			);

			// Send email.
			hivepress()->email->send(
				'user__request_password',
				[
					'to'           => $user->user_email,
					'placeholders' => [
						'user_name'          => $user->display_name,
						'password_reset_url' => $url,
					],
				]
			);
		} else {
			if ( is_email( $values['username'] ) ) {
				hivepress()->form->add_error( esc_html__( "User with this email doesn't exist.", 'hivepress' ) );
			} else {
				hivepress()->form->add_error( esc_html__( "User with this username doesn't exist.", 'hivepress' ) );
			}
		}
	}

	/**
	 * Sets password form values.
	 *
	 * @param array $values
	 * @return array
	 */
	public function set_password_form_values( $values ) {

		// Get username and key.
		$values['username'] = sanitize_user( hp_get_array_value( $_GET, 'username' ) );
		$values['key']      = sanitize_text_field( hp_get_array_value( $_GET, 'key' ) );

		return $values;
	}

	/**
	 * Resets password.
	 *
	 * @param array $values
	 */
	public function reset_password( $values ) {

		// Get user.
		$user = check_password_reset_key( $values['key'], $values['username'] );

		if ( ! is_wp_error( $user ) ) {
			if ( strlen( $values['password'] ) < 6 ) {
				hivepress()->form->add_error( esc_html__( 'New password is too short.', 'hivepress' ) );
			} else {

				// Reset password.
				reset_password( $user, $values['password'] );

				// Authenticate user.
				wp_signon(
					[
						'user_login'    => $values['username'],
						'user_password' => $values['password'],
						'remember'      => true,
					],
					is_ssl()
				);

				// Send email.
				wp_password_change_notification( $user );
			}
		} else {
			hivepress()->form->add_error( esc_html__( 'Password reset link is expired or invalid.', 'hivepress' ) );
		}
	}

	/**
	 * Sets update form values.
	 *
	 * @param array $values
	 * @return array
	 */
	public function set_update_form_values( $values ) {
		return array_merge( $values, $this->get_data( get_current_user_id() ) );
	}

	/**
	 * Updates user.
	 *
	 * @param array $values
	 */
	public function update( $values ) {

		// Get user ID.
		$user_id = get_current_user_id();

		// Get user name.
		$first_name   = hp_get_array_value( $values, 'first_name', '' );
		$last_name    = hp_get_array_value( $values, 'last_name', '' );
		$display_name = trim( $first_name . ' ' . $last_name );

		// Update name and description.
		update_user_meta( $user_id, 'first_name', $first_name );
		update_user_meta( $user_id, 'last_name', $last_name );
		update_user_meta( $user_id, 'description', hp_get_array_value( $values, 'description' ) );

		if ( '' !== $display_name ) {
			wp_update_user(
				[
					'ID'           => $user_id,
					'display_name' => $display_name,
				]
			);
		}

		// Update email and password.
		if ( $values['email'] !== $this->get_email() || '' !== $values['new_password'] ) {

			// Check password.
			if ( '' === $values['current_password'] ) {
				hivepress()->form->add_error( esc_html__( 'The current password is required.', 'hivepress' ) );
			} elseif ( ! wp_check_password( $values['current_password'], $this->get_password(), $user_id ) ) {
				hivepress()->form->add_error( esc_html__( 'The current password is incorrect.', 'hivepress' ) );
			} else {

				// Update email.
				if ( $values['email'] !== $this->get_email() ) {
					wp_update_user(
						[
							'ID'         => $user_id,
							'user_email' => $values['email'],
						]
					);
				}

				// Change password.
				if ( '' !== $values['new_password'] ) {
					if ( strlen( $values['new_password'] ) < 6 ) {
						hivepress()->form->add_error( esc_html__( 'New password is too short.', 'hivepress' ) );
					} else {
						wp_update_user(
							[
								'ID'        => $user_id,
								'user_pass' => $values['new_password'],
							]
						);
					}
				}
			}
		}
	}

	/**
	 * Deletes user.
	 *
	 * @param array $values
	 */
	public function delete( $values ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';

		// Check password.
		if ( ! wp_check_password( $values['password'], $this->get_password(), get_current_user_id() ) ) {
			hivepress()->form->add_error( esc_html__( 'Password is incorrect.', 'hivepress' ) );
		} elseif ( ! current_user_can( 'manage_options' ) ) {

			// Delete user.
			wp_delete_user( get_current_user_id() );
		}
	}

	/**
	 * Updates user image.
	 *
	 * @param array $args
	 */
	public function update_image( $args ) {
		if ( strpos( current_action(), 'delete' ) === false ) {
			update_user_meta( $args['user_id'], 'hp_image', $args['attachment_id'] );
		} else {
			delete_user_meta( $args['user_id'], 'hp_image' );
		}
	}

	/**
	 * Sets user image.
	 *
	 * @param string $image
	 * @param mixed  $user
	 * @param int    $size
	 * @param string $default
	 * @param string $alt
	 * @return string
	 */
	public function set_image( $image, $user, $size, $default, $alt ) {

		// Get user ID.
		$user_id = 0;

		if ( is_numeric( $user ) ) {
			$user_id = absint( $user );
		} elseif ( is_object( $user ) ) {
			$user_id = absint( $user->user_id );
		}

		if ( 0 !== $user_id ) {

			// Get image URL.
			$image_url = wp_get_attachment_image_src( absint( get_user_meta( $user_id, 'hp_image', true ) ), 'thumbnail' );

			if ( false !== $image_url ) {
				$image = '<img src="' . esc_url( reset( $image_url ) ) . '" class="avatar avatar-' . esc_attr( $size ) . ' photo" height="' . esc_attr( $size ) . '" width="' . esc_attr( $size ) . '" alt="' . esc_attr( $alt ) . '">';
			}
		}

		return $image;
	}

	/**
	 * Sets redirect URL.
	 *
	 * @param string $url
	 * @return string
	 */
	public function set_redirect_url( $url ) {
		if ( is_user_logged_in() ) {
			$url = hivepress()->template->get_url( 'user__account' );
		} else {
			$url = add_query_arg( 'redirect', rawurlencode( hp_get_current_url() ), hivepress()->template->get_url( 'user__login' ) );
		}

		return $url;
	}

	/**
	 * Redirects account page.
	 */
	public function redirect_account() {
		$menu_items = hivepress()->template->get_menu( 'user_account' );

		if ( ! empty( $menu_items ) ) {
			$menu_item = reset( $menu_items );

			hp_redirect( $menu_item['url'] );
		}
	}

	/**
	 * Sets password template context.
	 *
	 * @param array $context
	 * @return array
	 */
	public function set_password_template_context( $context ) {

		// Get username and key.
		$username = sanitize_user( hp_get_array_value( $_GET, 'username' ) );
		$key      = sanitize_text_field( hp_get_array_value( $_GET, 'key' ) );

		// Set context.
		$context['password_reset_key_valid'] = ! is_wp_error( check_password_reset_key( $key, $username ) );

		return $context;
	}
}
