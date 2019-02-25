<?php
/**
 * User register form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User register form class.
 *
 * @class User_Register
 */
class User_Register extends Form {

	/**
	 * Form captcha.
	 *
	 * @var bool
	 */
	protected $captcha = false;

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		parent::__construct( $args );

		// Set title.
		$this->set_title( esc_html__( 'Register User', 'hivepress' ) );

		// Set fields.
		$this->set_fields(
			[
				'email'    => [
					'label'    => esc_html__( 'Email', 'hivepress' ),
					'type'     => 'email',
					'required' => true,
					'order'    => 10,
				],

				'password' => [
					'label'      => esc_html__( 'Password', 'hivepress' ),
					'type'       => 'password',
					'min_length' => 6,
					'required'   => true,
					'order'      => 20,
				],
			]
		);

		// Add terms checkbox.
		$page_id = hp_get_post_id(
			[
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post__in'    => [ absint( get_option( 'hp_page_user_registration_terms' ) ) ],
			]
		);

		if ( 0 !== $page_id ) {
			$this->set_fields(
				[
					'terms' => [
						'caption'  => sprintf( hp_sanitize_html( __( 'I agree to %s', 'hivepress' ) ), '<a href="' . esc_url( get_permalink( $page_id ) ) . '" target="_blank">' . get_the_title( $page_id ) . '</a>' ),
						'type'     => 'checkbox',
						'required' => true,
						'order'    => 100,
					],
				]
			);
		}
	}
}
