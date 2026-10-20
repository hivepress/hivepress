<?php
/**
 * Model component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles models.
 */
final class Model extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Delete drafts.
		add_action( 'hivepress/v1/events/hourly', [ $this, 'delete_drafts' ] );

		parent::__construct( $args );
	}

	/**
	 * Gets model name.
	 *
	 * @param string $type Model type.
	 * @param string $alias Model alias.
	 * @return string
	 */
	public function get_model_name( $type, $alias ) {
		foreach ( hivepress()->get_classes( 'models' ) as $model ) {
			if ( $model::_get_meta( 'type' ) === $type && $model::_get_meta( 'alias' ) === $alias ) {
				return hp\get_class_name( $model );
			}
		}
	}

	/**
	 * Gets model object.
	 *
	 * @param string $model Model name.
	 * @param int    $id Object ID.
	 * @return object
	 */
	public function get_model_object( $model, $id ) {
		$object = null;

		// Get class.
		$class = '\HivePress\Models\\' . $model;

		if ( class_exists( $class ) && ! ( new \ReflectionClass( $class ) )->isAbstract() ) {

			// Get query.
			$query = call_user_func( [ $class, 'query' ] );

			if ( $query ) {

				// Get object.
				$object = $query->get_by_id( $id );
			}
		}

		return $object;
	}

	/**
	 * Gets cache group.
	 *
	 * @param string $type Model type.
	 * @param string $alias Model alias.
	 * @return string
	 */
	public function get_cache_group( $type, $alias ) {
		$group = 'models/';

		$model = $this->get_model_name( $type, $alias );

		if ( $model ) {
			$group .= $model;
		} else {
			$group .= $type . '/' . hp\unprefix( $alias );
		}

		return $group;
	}

	/**
	 * Gets field name.
	 *
	 * @param string $model Model name.
	 * @param string $type Field type.
	 * @param string $alias Field alias.
	 * @param int    $id Object ID.
	 * @return string
	 */
	public function get_field_name( $model, $type, $alias, $id = null ) {

		// Create model.
		$model = hp\create_class_instance( '\HivePress\Models\\' . $model );

		if ( $model ) {

			// Set object ID.
			if ( $id ) {
				$model->set_id( $id );
			}

			// Get fields aliases.
			$aliases = array_map(
				function( $field ) use ( $type ) {
					if ( ( 'meta' === $type && $field->get_arg( '_external' ) ) || ( 'term' === $type && $field->get_arg( '_relation' ) === 'many_to_many' ) ) {
						return $field->get_arg( '_alias' );
					}
				},
				$model->_get_fields()
			);

			if ( in_array( $alias, $aliases, true ) ) {
				return array_search( $alias, $aliases, true );
			}
		}
	}

	/**
	 * Gets model draft.
	 *
	 * @param string $model Model name.
	 * @return object
	 */
	public function get_draft( $model ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Get cached draft.
		$draft = hivepress()->request->get_context( $model . '_draft' );

		if ( $draft ) {
			return $draft;
		}

		// Create model.
		$object = hp\create_class_instance( '\HivePress\Models\\' . $model );

		if ( ! $object ) {
			return;
		}

		// Get user ID.
		$user_id = get_current_user_id();

		if ( $object::_get_meta( 'type' ) === 'post' ) {

			// Check fields.
			$fields = $object->_get_fields();

			if ( ! isset( $fields['drafted'], $fields['user'] ) ) {
				return;
			}

			// Get draft.
			$draft = $object::query()->filter(
				[
					'status'  => 'auto-draft',
					'drafted' => true,
					'user'    => $user_id,
				]
			)->get_first();

			if ( ! $draft ) {

				// Add draft.
				$draft = $object->fill(
					[
						'status'  => 'auto-draft',
						'drafted' => true,
						'user'    => $user_id,
					]
				);

				if ( ! $draft->save( [ 'status', 'drafted', 'user' ] ) ) {
					return;
				}
			}
		} elseif ( $object::_get_meta( 'type' ) === 'comment' ) {

			// Get cached draft ID.
			$draft_id = hivepress()->cache->get_user_cache( $user_id, 'draft_id', 'models/' . $model );

			if ( ! $draft_id || ! get_comment( $draft_id ) ) {

				// Get draft ID.
				$draft_id = (int) hp\get_first_array_value(
					get_comments(
						[
							'type'       => $object::_get_meta( 'alias' ),
							'user_id'    => $user_id,
							'meta_key'   => 'hp_drafted',
							'meta_value' => '1',
							'fields'     => 'ids',
							'number'     => 1,
						]
					)
				);

				if ( ! $draft_id ) {

					// Add draft.
					$draft_id = (int) wp_insert_comment(
						[
							'comment_type' => $object::_get_meta( 'alias' ),
							'user_id'      => $user_id,
							'comment_meta' => [
								'hp_drafted' => 1,
							],
						]
					);
				}

				// Cache draft ID.
				if ( $draft_id ) {
					hivepress()->cache->set_user_cache( $user_id, 'draft_id', 'models/' . $model, $draft_id );
				}
			}

			// Get draft.
			if ( $draft_id ) {
				$draft = $object::query()->get_by_id( $draft_id );

				// Set request context.
				hivepress()->request->set_context( $model . '_draft', $draft );
			}
		}

		return $draft;
	}

	/**
	 * Deletes drafts.
	 */
	public function delete_drafts() {

		// Get comment types.
		$types = hp\prefix( array_keys( hivepress()->get_config( 'comment_types' ) ) );

		if ( ! $types ) {
			return;
		}

		// Get draft IDs.
		$draft_ids = get_comments(
			[
				'type'       => $types,
				'meta_key'   => 'hp_drafted',
				'meta_value' => '1',
				'fields'     => 'ids',
				'number'     => 100,

				'date_query' => [
					[
						'column' => 'comment_date_gmt',
						'before' => gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS ),
					],
				],
			]
		);

		foreach ( $draft_ids as $draft_id ) {
			wp_delete_comment( $draft_id, true );
		}
	}
}
