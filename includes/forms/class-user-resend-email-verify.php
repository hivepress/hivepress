<?php
/**
 * Resend user verification email form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Resend user verification email.
 */
class User_Resend_Email_Verify extends Model_Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'model' => 'user',
				'label' => 'Test',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'message'     => esc_html__( 'The email verification email was succesfully sent. Please check your email inbox', 'hivepress' ),
				'description' => esc_html__( 'Please click the button to resend the verification email', 'hivepress' ),

				'button'      => [
					'label' => esc_html__( 'Resend email', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps form properties.
	 */
	protected function boot() {

		// Set action.
		if ( $this->model->get_id() ) {
			$this->action = hivepress()->router->get_url(
				'user_resend_email_verify',
				[
					'user_id' => $this->model->get_id(),
				]
			);
		}

		parent::boot();
	}
}
