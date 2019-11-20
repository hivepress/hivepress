<?php
/**
 * WordPress component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * WordPress component class.
 *
 * @class WordPress
 */
final class WordPress {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Manage posts.
		add_action( 'save_post', [ $this, 'update_post' ] );
		add_action( 'delete_post', [ $this, 'delete_post' ] );
	}

	/**
	 * Updates post.
	 *
	 * @param int $post_id Post ID.
	 */
	public function update_post( $post_id ) {
		$post_type = get_post_type( $post_id );

		if ( strpos( $post_type, 'hp_' ) === 0 ) {
			do_action( 'hivepress/v1/models/post/update' );
			do_action( 'hivepress/v1/models/' . hp\unprefix( $post_type ) . '/update' );
		}
	}

	/**
	 * Deletes post.
	 *
	 * @param int $post_id Post ID.
	 */
	public function delete_post( $post_id ) {
		$post_type = get_post_type( $post_id );

		if ( strpos( $post_type, 'hp_' ) === 0 ) {
			do_action( 'hivepress/v1/models/post/delete' );
			do_action( 'hivepress/v1/models/' . hp\unprefix( $post_type ) . '/delete' );
		}
	}
}
