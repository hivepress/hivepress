<?php
/**
 * Listing submit form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing submit form class.
 *
 * @class Listing_Submit
 */
class Listing_Submit extends Listing_Update {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {

		// Set fields.
		$fields = [];

		// Add terms checkbox.
		$page_id = hp_get_post_id(
			[
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post__in'    => [ absint( get_option( 'hp_page_listing_submission_terms' ) ) ],
			]
		);

		if ( 0 !== $page_id ) {
			$fields['terms'] = [
				'caption'  => sprintf( hp_sanitize_html( __( 'I agree to %s', 'hivepress' ) ), '<a href="' . esc_url( get_permalink( $page_id ) ) . '" target="_blank">' . get_the_title( $page_id ) . '</a>' ),
				'type'     => 'checkbox',
				'required' => true,
				'order'    => 100,
			];
		}

		// Set arguments.
		$args = array_replace_recursive(
			[
				'title'  => esc_html__( 'Submit Listing', 'hivepress' ),
				'fields' => $fields,
			],
			$args
		);

		parent::__construct( $args );
	}
}
