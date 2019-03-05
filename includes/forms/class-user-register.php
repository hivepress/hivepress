<?php
/**
 * User register form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User register form class.
 *
 * @class User_Register
 */
class User_Register extends Model_Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {

		// Set fields.
		$fields = [
			'email'    => [
				'order' => 10,
			],

			'password' => [
				'order' => 20,
			],
		];

		// Add terms checkbox.
		$page_id = hp\get_post_id(
			[
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post__in'    => [ absint( get_option( 'hp_page_user_registration_terms' ) ) ],
			]
		);

		if ( 0 !== $page_id ) {
			$fields['terms'] = [
				'caption'  => sprintf( hp\sanitize_html( __( 'I agree to %s', 'hivepress' ) ), '<a href="' . esc_url( get_permalink( $page_id ) ) . '" target="_blank">' . get_the_title( $page_id ) . '</a>' ),
				'type'     => 'checkbox',
				'required' => true,
				'order'    => 100,
			];
		}

		// Set arguments.
		$args = hp\merge_arrays(
			$args,
			[
				'title'  => esc_html__( 'Register User', 'hivepress' ),
				'model'  => 'user',
				'action' => hp\get_rest_url( '/users' ),
				'fields' => $fields,
			]
		);

		parent::__construct( $args );
	}
}
