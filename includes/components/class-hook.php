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
 * Hook component class.
 *
 * @class Hook
 */
final class Hook extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Update users.
		add_action( 'user_register', [ $this, 'update_user' ] );
		add_action( 'profile_update', [ $this, 'update_user' ] );
		add_action( 'delete_user', [ $this, 'update_user' ] );

		// Update user meta.
		add_action( 'added_user_meta', [ $this, 'update_user_meta' ], 10, 4 );
		add_action( 'updated_user_meta', [ $this, 'update_user_meta' ], 10, 4 );

		// Update posts.
		add_action( 'save_post', [ $this, 'update_post' ], 10, 3 );
		add_action( 'delete_post', [ $this, 'update_post' ] );

		// Update post status.
		add_action( 'transition_post_status', [ $this, 'update_post_status' ], 10, 3 );

		// Update post meta.
		add_action( 'added_post_meta', [ $this, 'update_post_meta' ], 10, 4 );
		add_action( 'updated_post_meta', [ $this, 'update_post_meta' ], 10, 4 );

		// Update post terms.
		add_action( 'set_object_terms', [ $this, 'update_post_terms' ], 10, 6 );

		// Update terms.
		add_action( 'create_term', [ $this, 'update_term' ], 10, 3 );
		add_action( 'edit_term', [ $this, 'update_term' ], 10, 3 );
		add_action( 'delete_term', [ $this, 'update_term' ], 10, 3 );

		// Update term meta.
		add_action( 'added_term_meta', [ $this, 'update_term_meta' ], 10, 4 );
		add_action( 'updated_term_meta', [ $this, 'update_term_meta' ], 10, 4 );

		// Update comments.
		add_action( 'wp_insert_comment', [ $this, 'update_comment' ] );
		add_action( 'edit_comment', [ $this, 'update_comment' ] );
		add_action( 'delete_comment', [ $this, 'update_comment' ] );

		// Update comment status.
		add_action( 'wp_set_comment_status', [ $this, 'update_comment_status' ], 10, 2 );

		// Update comment meta.
		add_action( 'added_comment_meta', [ $this, 'update_comment_meta' ], 10, 4 );
		add_action( 'updated_comment_meta', [ $this, 'update_comment_meta' ], 10, 4 );

		// Start import.
		add_action( 'import_start', [ $this, 'start_import' ] );

		parent::__construct( $args );
	}

	/**
	 * Gets model name.
	 *
	 * @param string $type Model type.
	 * @param string $alias Model alias.
	 * @return mixed
	 */
	protected function get_model_name( $type, $alias ) {
		foreach ( hivepress()->get_classes( 'models' ) as $model ) {
			if ( $model::_get_meta( 'type' ) === $type && $model::_get_meta( 'alias' ) === $alias ) {
				return hp\get_class_name( $model );
			}
		}
	}

	/**
	 * Gets field name.
	 *
	 * @param string $model Model name.
	 * @param string $alias Field alias.
	 * @return string
	 */
	protected function get_field_name( $model, $alias ) {

		// Create model.
		$object = hp\create_class_instance( '\HivePress\Models\\' . $model );

		if ( $object ) {

			// Get fields.
			$aliases = array_map(
				function( $field ) {
					$alias = null;

					if ( $field->get_arg( '_relation' ) === 'many_to_many' ) {
						$alias = hp\call_class_method( '\HivePress\Models\\' . $field->get_arg( '_model' ), '_get_meta', [ 'alias' ] );
					} elseif ( $field->get_arg( '_external' ) ) {
						$alias = $field->get_arg( '_alias' );
					}

					return $alias;
				},
				$object->_get_fields()
			);

			if ( in_array( $alias, $aliases, true ) ) {
				return array_search( $alias, $aliases, true );
			}
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

		// Update user.
		list($model, $action) = explode( '_', current_action() );

		if ( 'register' === $action ) {
			$action = 'create';
		} elseif ( 'update' !== $action ) {
			$action = 'delete';
		}

		do_action( 'hivepress/v1/models/user/' . $action, $user_id );
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

		// Update user meta.
		if ( strpos( $meta_key, 'hp_' ) === 0 || in_array( $meta_key, [ 'first_name', 'last_name', 'description' ], true ) ) {

			// Get field name.
			$field = $this->get_field_name( 'user', $meta_key );

			if ( $field ) {
				do_action( 'hivepress/v1/models/user/update_' . $field, $user_id, $meta_value );
			}
		}
	}

	/**
	 * Updates post.
	 *
	 * @param int   $post_id Post ID.
	 * @param mixed $post Post object.
	 * @param bool  $update Update flag.
	 */
	public function update_post( $post_id, $post = null, $update = false ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Update post.
		$post_type = get_post_type( $post_id );

		if ( strpos( $post_type, 'hp_' ) === 0 || 'attachment' === $post_type ) {
			list($action, $model) = explode( '_', current_action() );

			if ( 'save' === $action ) {
				if ( $update ) {
					$action = 'update';
				} else {
					$action = 'create';
				}
			}

			do_action( 'hivepress/v1/models/post/' . $action, $post_id );

			// Get model name.
			$model = $this->get_model_name( 'post', $post_type );

			if ( $model ) {
				do_action( 'hivepress/v1/models/' . $model . '/' . $action, $post_id );
			}
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

		// Update post status.
		if ( ( strpos( $post->post_type, 'hp_' ) === 0 || 'attachment' === $post->post_type ) && $new_status !== $old_status ) {

			// Get model name.
			$model = $this->get_model_name( 'post', $post->post_type );

			if ( $model ) {
				do_action( 'hivepress/v1/models/' . $model . '/update_status', $post->ID, $new_status, $old_status );
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

		// Update post meta.
		if ( strpos( $meta_key, 'hp_' ) === 0 ) {
			$post_type = get_post_type( $post_id );

			if ( strpos( $post_type, 'hp_' ) === 0 || 'attachment' === $post_type ) {

				// Get model name.
				$model = $this->get_model_name( 'post', $post_type );

				if ( $model ) {

					// Get field name.
					$field = $this->get_field_name( $model, $meta_key );

					if ( $field && 'status' !== $field ) {
						do_action( 'hivepress/v1/models/' . $model . '/update_' . $field, $post_id, $meta_value );
					}
				}
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

		if ( strpos( $taxonomy, 'hp_' ) === 0 ) {

			// Get post type.
			$post_type = get_post_type( $post_id );

			if ( strpos( $post_type, 'hp_' ) === 0 ) {

				// Get new term IDs.
				$new_term_ids = get_terms(
					[
						'taxonomy'         => $taxonomy,
						'term_taxonomy_id' => $term_taxonomy_ids,
						'fields'           => 'ids',
						'hide_empty'       => false,
					]
				);

				// Get old term IDs.
				$old_term_ids = get_terms(
					[
						'taxonomy'         => $taxonomy,
						'term_taxonomy_id' => $old_term_taxonomy_ids,
						'fields'           => 'ids',
						'hide_empty'       => false,
					]
				);

				do_action( 'hivepress/v1/models/post/update_terms', $post_id, $new_term_ids, $old_term_ids, $post_type, $taxonomy );

				// Get model.
				$model = $this->get_model_name( 'post', $post_type );

				if ( $model ) {

					// Get field.
					$field = $this->get_field_name( $model, $taxonomy );

					if ( $field ) {
						do_action( 'hivepress/v1/models/' . $model . '/update_' . $field, $post_id, $new_term_ids, $old_term_ids );
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
	public function update_term( $term_id, $term_taxonomy_id, $taxonomy ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Update term.
		if ( strpos( $taxonomy, 'hp_' ) === 0 ) {
			list($action, $model) = explode( '_', current_action() );

			if ( 'edit' === $action ) {
				$action = 'update';
			}

			do_action( 'hivepress/v1/models/term/' . $action, $term_id );

			// Get model name.
			$model = $this->get_model_name( 'term', $taxonomy );

			if ( $model ) {
				do_action( 'hivepress/v1/models/' . $model . '/' . $action, $term_id );
			}
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

		// Update term meta.
		if ( strpos( $meta_key, 'hp_' ) === 0 ) {
			$taxonomy = get_term( $term_id )->taxonomy;

			if ( strpos( $taxonomy, 'hp_' ) === 0 ) {

				// Get model name.
				$model = $this->get_model_name( 'term', $taxonomy );

				if ( $model ) {

					// Get field name.
					$field = $this->get_field_name( $model, $meta_key );

					if ( $field ) {
						do_action( 'hivepress/v1/models/' . $model . '/update_' . $field, $term_id, $meta_value );
					}
				}
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

		// Update comment.
		$comment_type = get_comment_type( $comment_id );

		if ( strpos( $comment_type, 'hp_' ) === 0 ) {
			list($action, $model) = explode( '_', preg_replace( '/^wp_/', '', current_action() ) );

			if ( 'insert' === $action ) {
				$action = 'create';
			} elseif ( 'edit' === $action ) {
				$action = 'update';
			}

			do_action( 'hivepress/v1/models/comment/' . $action, $comment_id );

			// Get model name.
			$model = $this->get_model_name( 'comment', $comment_type );

			if ( $model ) {
				do_action( 'hivepress/v1/models/' . $model . '/' . $action, $comment_id );
			}
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
			$comment_type = get_comment_type( $comment_id );

			if ( strpos( $comment_type, 'hp_' ) === 0 ) {

				// Get model name.
				$model = $this->get_model_name( 'comment', $comment_type );

				if ( $model ) {
					do_action( 'hivepress/v1/models/' . $model . '/update_status', $comment_id, $new_status );
				}
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

		// Update comment meta.
		if ( strpos( $meta_key, 'hp_' ) === 0 ) {
			$comment_type = get_comment_type( $comment_id );

			if ( strpos( $comment_type, 'hp_' ) === 0 ) {

				// Get model name.
				$model = $this->get_model_name( 'comment', $comment_type );

				if ( $model ) {

					$field = $this->get_field_type( $model, $meta_key );

					if ( $field && 'status' !== $field ) {
						do_action( 'hivepress/v1/models/' . $model . '/update_' . $field, $comment_id, $meta_value );
					}
				}
			}
		}
	}

	/**
	 * Checks import status.
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
}
