<?php
/**
 * Hook component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Implements integration with WordPress hooks.
 */
final class Hook extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Start import.
		add_action( 'import_start', [ $this, 'start_import' ] );

		// Update users.
		add_action( 'user_register', [ $this, 'update_user' ] );
		add_action( 'profile_update', [ $this, 'update_user' ] );
		add_action( 'delete_user', [ $this, 'update_user' ] );

		// Update user meta.
		add_action( 'added_user_meta', [ $this, 'update_user_meta' ], 10, 4 );
		add_action( 'updated_user_meta', [ $this, 'update_user_meta' ], 10, 4 );
		add_action( 'deleted_user_meta', [ $this, 'update_user_meta' ], 10, 4 );

		// Update posts.
		add_action( 'save_post', [ $this, 'update_post' ], 10, 3 );
		add_action( 'before_delete_post', [ $this, 'update_post' ] );

		add_action( 'add_attachment', [ $this, 'update_post' ] );
		add_action( 'edit_attachment', [ $this, 'update_post' ] );
		add_action( 'delete_attachment', [ $this, 'update_post' ] );

		// Update post status.
		add_action( 'transition_post_status', [ $this, 'update_post_status' ], 10, 3 );

		// Update post meta.
		add_action( 'added_post_meta', [ $this, 'update_post_meta' ], 10, 4 );
		add_action( 'updated_post_meta', [ $this, 'update_post_meta' ], 10, 4 );
		add_action( 'deleted_post_meta', [ $this, 'update_post_meta' ], 10, 4 );

		// Update post terms.
		add_action( 'set_object_terms', [ $this, 'update_post_terms' ], 10, 6 );

		// Update terms.
		add_action( 'create_term', [ $this, 'update_term' ], 10, 3 );
		add_action( 'edit_term', [ $this, 'update_term' ], 10, 3 );
		add_action( 'pre_delete_term', [ $this, 'update_term' ], 10, 2 );

		// Update term meta.
		add_action( 'added_term_meta', [ $this, 'update_term_meta' ], 10, 4 );
		add_action( 'updated_term_meta', [ $this, 'update_term_meta' ], 10, 4 );
		add_action( 'deleted_term_meta', [ $this, 'update_term_meta' ], 10, 4 );

		// Update comments.
		add_action( 'wp_insert_comment', [ $this, 'update_comment' ] );
		add_action( 'edit_comment', [ $this, 'update_comment' ] );
		add_action( 'delete_comment', [ $this, 'update_comment' ] );

		// Update comment status.
		add_action( 'wp_set_comment_status', [ $this, 'update_comment_status' ], 10, 2 );

		// Update comment meta.
		add_action( 'added_comment_meta', [ $this, 'update_comment_meta' ], 10, 4 );
		add_action( 'updated_comment_meta', [ $this, 'update_comment_meta' ], 10, 4 );
		add_action( 'deleted_comment_meta', [ $this, 'update_comment_meta' ], 10, 4 );

		parent::__construct( $args );
	}

	/**
	 * Checks if import started.
	 *
	 * @return bool
	 */
	protected function is_import_started() {
		return defined( 'HP_IMPORT' ) && HP_IMPORT;
	}

	/**
	 * Starts import.
	 */
	public function start_import() {
		if ( ! defined( 'HP_IMPORT' ) ) {
			define( 'HP_IMPORT', true );
		}
	}

	/**
	 * Updates user.
	 *
	 * @param int $user_id User ID.
	 */
	public function update_user( $user_id ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Get action.
		$action = hp\get_last_array_value( explode( '_', current_action() ) );

		if ( 'register' === $action ) {
			$action = 'create';
		} elseif ( 'update' !== $action ) {
			$action = 'delete';
		}

		/**
		 * Fires when a user is created, updated or deleted. The dynamic part of the hook refers to the action type (`create`, `update` or `delete`).
		 *
		 * @hook hivepress/v2/models/user/{action_type}
		 * @param {int} $user_id User ID.
		 * @param {object} $user User object.
		 */
		do_action( 'hivepress/v2/models/user/' . $action, $user_id, hivepress()->model->get_model_object( 'user', $user_id ) );

		/**
		 * Fires when a user is created, updated or deleted. The dynamic part of the hook refers to the action type (`create`, `update` or `delete`).
		 *
		 * @hook hivepress/v1/models/user/{action_type}
		 * @param {int} $user_id User ID.
		 */
		do_action( 'hivepress/v1/models/user/' . $action, $user_id, 'user' );
	}

	/**
	 * Updates user meta.
	 *
	 * @param int    $meta_id Meta ID.
	 * @param int    $user_id User ID.
	 * @param string $meta_key Meta key.
	 * @param string $meta_value Meta value.
	 */
	public function update_user_meta( $meta_id, $user_id, $meta_key, $meta_value ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Get field name.
		$field = hivepress()->model->get_field_name( 'user', 'meta', $meta_key, $user_id );

		if ( $field ) {

			// Normalize meta value.
			if ( strpos( current_action(), 'deleted_' ) === 0 ) {
				$meta_value = null;
			}

			/**
			 * Fires when a specific user field is updated. The dynamic part of the hook refers to the field name. For example, use the `hivepress/v1/models/user/update_image` hook to call a custom function each time the user image is changed.
			 *
			 * @hook hivepress/v1/models/user/update_{field_name}
			 * @param {int} $user_id User ID.
			 * @param {mixed} $value Field value.
			 */
			do_action( 'hivepress/v1/models/user/update_' . $field, $user_id, $meta_value );
		}
	}

	/**
	 * Updates post.
	 *
	 * @param int   $post_id Post ID.
	 * @param mixed $post Post object.
	 * @param bool  $update Is post updated?
	 */
	public function update_post( $post_id, $post = null, $update = false ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Get post type.
		$post_type = get_post_type( $post_id );

		// Get model name.
		$model = hivepress()->model->get_model_name( 'post', $post_type );

		if ( $model || strpos( $post_type, 'hp_' ) === 0 ) {

			// Get action.
			$action = hp\get_first_array_value( explode( '_', current_action() ) );

			if ( in_array( $action, [ 'save', 'add', 'edit' ], true ) ) {
				if ( $update || 'edit' === $action ) {
					$action = 'update';
				} else {
					$action = 'create';
				}
			} else {
				$action = 'delete';
			}

			if ( $model ) {

				/**
				 * Fires when the model (e.g. `listing` or `vendor`) object is created, updated or deleted. The last part of the hook refers to the action type (`create`, `update` or `delete`). For example, use the `hivepress/v1/models/listing/update` hook to call a custom function each time a listing is updated.
				 *
				 * @hook hivepress/v1/models/{model_name}/{action_type}
				 * @param {int} $object_id Object ID.
				 * @param {object} $object Model object.
				 */
				do_action( 'hivepress/v1/models/' . $model . '/' . $action, $post_id, hivepress()->model->get_model_object( $model, $post_id ) );
			}

			/**
			 * Fires when a post is created, updated or deleted. The dynamic part of the hook refers to the action type (`create`, `update` or `delete`).
			 *
			 * @hook hivepress/v1/models/post/{action_type}
			 * @param {int} $post_id Post ID.
			 * @param {string} $post_type Post type.
			 */
			do_action( 'hivepress/v1/models/post/' . $action, $post_id, $post_type );
		}
	}

	/**
	 * Updates post status.
	 *
	 * @param string  $new_status New status.
	 * @param string  $old_status Old status.
	 * @param WP_Post $post Post object.
	 */
	public function update_post_status( $new_status, $old_status, $post ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		if ( $new_status !== $old_status ) {

			// Get model name.
			$model = hivepress()->model->get_model_name( 'post', $post->post_type );

			if ( $model ) {

				/**
				 * Fires when the model object status is updated. The dynamic part of the hook refers to the model name (e.g. `listing` or `vendor`). For example, use the `hivepress/v1/models/listing/update_status` hook to call a custom function each time the listing status is changed.
				 *
				 * @hook hivepress/v1/models/{model_name}/update_status
				 * @param {int} $object_id Object ID.
				 * @param {string} $new_status New status.
				 * @param {string} $old_status Old status.
				 * @param {object} $object Model object.
				 */
				do_action( 'hivepress/v1/models/' . $model . '/update_status', $post->ID, $new_status, $old_status, hivepress()->model->get_model_object( $model, $post->ID ) );
			}
		}
	}

	/**
	 * Updates post meta.
	 *
	 * @param int    $meta_id Meta ID.
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Meta key.
	 * @param string $meta_value Meta value.
	 */
	public function update_post_meta( $meta_id, $post_id, $meta_key, $meta_value ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Get model name.
		$model = hivepress()->model->get_model_name( 'post', get_post_type( $post_id ) );

		if ( $model ) {

			// Get field name.
			$field = hivepress()->model->get_field_name( $model, 'meta', $meta_key, $post_id );

			if ( $field && 'status' !== $field ) {

				// Normalize meta value.
				if ( strpos( current_action(), 'deleted_' ) === 0 ) {
					$meta_value = null;
				}

				/**
				 * Fires when a specific model (e.g. `listing` or `vendor`) object field is updated. The last part of the hook refers to the field name. For example, use the `hivepress/v1/models/listing/update_image` hook to call a custom function each time the listing image is changed.
				 *
				 * @hook hivepress/v1/models/{model_name}/update_{field_name}
				 * @param {int} $object_id Object ID.
				 * @param {mixed} $value Field value.
				 */
				do_action( 'hivepress/v1/models/' . $model . '/update_' . $field, $post_id, $meta_value );
			}
		}
	}

	/**
	 * Updates post terms.
	 *
	 * @param int    $post_id Post ID.
	 * @param array  $terms Terms.
	 * @param array  $term_taxonomy_ids Term taxonomy IDs.
	 * @param string $taxonomy Taxonomy name.
	 * @param bool   $append Append property.
	 * @param array  $old_term_taxonomy_ids Old term taxonomy IDs.
	 */
	public function update_post_terms( $post_id, $terms, $term_taxonomy_ids, $taxonomy, $append, $old_term_taxonomy_ids ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		if ( array_diff( $term_taxonomy_ids, $old_term_taxonomy_ids ) || array_diff( $old_term_taxonomy_ids, $term_taxonomy_ids ) ) {

			// Get term model name.
			$term_model = hivepress()->model->get_model_name( 'term', $taxonomy );

			if ( $term_model || strpos( $taxonomy, 'hp_' ) === 0 ) {

				// Get post type.
				$post_type = get_post_type( $post_id );

				// Get post model name.
				$post_model = hivepress()->model->get_model_name( 'post', $post_type );

				if ( $post_model || strpos( $post_type, 'hp_' ) === 0 ) {

					// Get term IDs.
					$term_ids = get_terms(
						[
							'taxonomy'         => $taxonomy,
							'term_taxonomy_id' => array_merge( [ 0 ], $term_taxonomy_ids ),
							'fields'           => 'ids',
							'hide_empty'       => false,
						]
					);

					/**
					 * Fires when the taxonomy terms linked to a post are updated.
					 *
					 * @hook hivepress/v1/models/post/update_terms
					 * @param {int} $post_id Post ID.
					 * @param {string} $post_type Post type.
					 * @param {string} $taxonomy Taxonomy name.
					 */
					do_action( 'hivepress/v1/models/post/update_terms', $post_id, $post_type, $taxonomy );

					if ( $post_model ) {

						// Get post field name.
						$post_field = hivepress()->model->get_field_name( $post_model, 'term', $taxonomy, $post_id );

						if ( $post_field ) {
							do_action( 'hivepress/v1/models/' . $post_model . '/update_' . $post_field, $post_id, $term_ids );
						}
					}
				}
			}
		}
	}

	/**
	 * Updates term.
	 *
	 * @param int    $term_id Term ID.
	 * @param int    $term_taxonomy_id Term taxonomy ID.
	 * @param string $taxonomy Taxonomy name.
	 */
	public function update_term( $term_id, $term_taxonomy_id, $taxonomy = null ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Get taxonomy.
		if ( empty( $taxonomy ) ) {
			$taxonomy = $term_taxonomy_id;
		}

		// Get model name.
		$model = hivepress()->model->get_model_name( 'term', $taxonomy );

		if ( $model || strpos( $taxonomy, 'hp_' ) === 0 ) {

			// Get action.
			$action = hp\get_first_array_value( explode( '_', current_action() ) );

			if ( 'edit' === $action ) {
				$action = 'update';
			} elseif ( 'create' !== $action ) {
				$action = 'delete';
			}

			if ( $model ) {
				do_action( 'hivepress/v1/models/' . $model . '/' . $action, $term_id, hivepress()->model->get_model_object( $model, $term_id ) );
			}

			/**
			 * Fires when a taxonomy term is created, updated or deleted. The dynamic part of the hook refers to the action type (`create`, `update` or `delete`).
			 *
			 * @hook hivepress/v1/models/term/{action_type}
			 * @param {int} $term_id Term ID.
			 * @param {string} $taxonomy Taxonomy name.
			 */
			do_action( 'hivepress/v1/models/term/' . $action, $term_id, $taxonomy );
		}
	}

	/**
	 * Updates term meta.
	 *
	 * @param int    $meta_id Meta ID.
	 * @param int    $term_id Term ID.
	 * @param string $meta_key Meta key.
	 * @param string $meta_value Meta value.
	 */
	public function update_term_meta( $meta_id, $term_id, $meta_key, $meta_value ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Get model name.
		$model = hivepress()->model->get_model_name( 'term', get_term( $term_id )->taxonomy );

		if ( $model ) {

			// Get field name.
			$field = hivepress()->model->get_field_name( $model, 'meta', $meta_key, $term_id );

			if ( $field ) {

				// Normalize meta value.
				if ( strpos( current_action(), 'deleted_' ) === 0 ) {
					$meta_value = null;
				}

				// Fire action.
				do_action( 'hivepress/v1/models/' . $model . '/update_' . $field, $term_id, $meta_value );
			}
		}
	}

	/**
	 * Updates comment.
	 *
	 * @param int $comment_id Comment ID.
	 */
	public function update_comment( $comment_id ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Get comment type.
		$comment_type = get_comment_type( $comment_id );

		// Get model name.
		$model = hivepress()->model->get_model_name( 'comment', $comment_type );

		if ( $model || strpos( $comment_type, 'hp_' ) === 0 ) {

			// Get action.
			$action = hp\get_first_array_value( explode( '_', preg_replace( '/^wp_/', '', current_action() ) ) );

			if ( 'insert' === $action ) {
				$action = 'create';
			} elseif ( 'edit' === $action ) {
				$action = 'update';
			}

			if ( $model ) {
				do_action( 'hivepress/v1/models/' . $model . '/' . $action, $comment_id, hivepress()->model->get_model_object( $model, $comment_id ) );
			}

			/**
			 * Fires when a comment is created, updated or deleted. The dynamic part of the hook refers to the action type (`create`, `update` or `delete`).
			 *
			 * @hook hivepress/v1/models/comment/{action_type}
			 * @param {int} $comment_id Comment ID.
			 * @param {string} $comment_type Comment type.
			 */
			do_action( 'hivepress/v1/models/comment/' . $action, $comment_id, $comment_type );
		}
	}

	/**
	 * Updates comment status.
	 *
	 * @param int    $comment_id Comment ID.
	 * @param string $new_status New status.
	 */
	public function update_comment_status( $comment_id, $new_status ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Update comment status.
		if ( 'delete' !== $new_status ) {

			// Get model name.
			$model = hivepress()->model->get_model_name( 'comment', get_comment_type( $comment_id ) );

			if ( $model ) {

				// Fire action.
				do_action( 'hivepress/v1/models/' . $model . '/update_status', $comment_id, $new_status, null, hivepress()->model->get_model_object( $model, $comment_id ) );
			}
		}
	}

	/**
	 * Updates comment meta.
	 *
	 * @param int    $meta_id Meta ID.
	 * @param int    $comment_id Comment ID.
	 * @param string $meta_key Meta key.
	 * @param string $meta_value Meta value.
	 */
	public function update_comment_meta( $meta_id, $comment_id, $meta_key, $meta_value ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Get model name.
		$model = hivepress()->model->get_model_name( 'comment', get_comment_type( $comment_id ) );

		if ( $model ) {

			// Get field name.
			$field = hivepress()->model->get_field_name( $model, 'meta', $meta_key, $comment_id );

			if ( $field && 'status' !== $field ) {

				// Normalize meta value.
				if ( strpos( current_action(), 'deleted_' ) === 0 ) {
					$meta_value = null;
				}

				// Fire action.
				do_action( 'hivepress/v1/models/' . $model . '/update_' . $field, $comment_id, $meta_value );
			}
		}
	}
}
