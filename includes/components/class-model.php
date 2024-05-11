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

		// Sync attributes.
		add_action( 'hivepress/v2/models/user/update', [ $this, 'update_user' ], 100, 2 );
		add_action( 'hivepress/v1/models/vendor/update', [ $this, 'update_vendor' ], 100, 2 );

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
	 * Syncs attributes.
	 *
	 * @param object $model Model object to sync.
	 * @param string $model_sync Model name to sync with.
	 * @param string $model_attributes Model name of attributes to sync.
	 * @param array  $model_filters Query filters to get models to sync with.
	 */
	public function sync_attributes( $model, $model_sync, $model_attributes, $model_filters = [] ) {

		// Get class.
		$class = '\HivePress\Models\\' . $model_sync;

		// Check class.
		if ( ! class_exists( $class ) ) {
			return;
		}

		// Get attributes.
		$attributes = array_filter(
			hivepress()->attribute->get_attributes( $model_attributes ),
			function( $attribute ) {
				return hp\get_array_value( $attribute, 'synced' );
			}
		);

		if ( ! $attributes ) {
			return;
		}

		// Get values.
		$values = array_intersect_key( $model->serialize(), $attributes );

		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( ! isset( $attribute['edit_field']['options'] ) || isset( $attribute['edit_field']['_external'] ) ) {
				continue;
			}

			// Get field.
			$attribute_field = hp\get_array_value( $model->_get_fields(), $attribute_name );

			if ( ! $attribute_field || ! $attribute_field->get_value() ) {
				continue;
			}

			// Get term names.
			$term_names = get_terms(
				[
					'taxonomy'   => $attribute_field->get_arg( 'option_args' )['taxonomy'],
					'include'    => (array) $attribute_field->get_value(),
					'fields'     => 'names',
					'hide_empty' => false,
				]
			);

			if ( ! $term_names ) {
				continue;
			}

			// Get term IDs.
			$term_ids = get_terms(
				[
					'taxonomy'   => $attribute['edit_field']['option_args']['taxonomy'],
					'name'       => $term_names,
					'fields'     => 'ids',
					'hide_empty' => false,
				]
			);

			if ( ! $term_ids ) {
				continue;
			}

			// Set value.
			$values[ $attribute_name ] = $term_ids;
		}

		// Get query.
		$query = call_user_func( [ $class, 'query' ] );

		// Update models.
		foreach ( $query->filter( $model_filters )->get() as $sync_model ) {
			if ( array_intersect_key( $sync_model->serialize(), $attributes ) !== $values ) {
				$sync_model->fill( $values )->save( array_keys( $values ) );
			}
		}
	}

	/**
	 * Updates vendor.
	 *
	 * @param int    $vendor_id Vendor ID.
	 * @param object $vendor Vendor object.
	 */
	public function update_vendor( $vendor_id, $vendor ) {

		// Remove action.
		remove_action( 'hivepress/v1/models/vendor/update', [ $this, 'update_vendor' ] );

		// Sync attributes.
		$this->sync_attributes(
			$vendor,
			'listing',
			'listing',
			[
				'status__in' => [ 'auto-draft', 'draft', 'pending', 'publish' ],
				'user'       => $vendor->get_user__id(),
			]
		);
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

		// Sync attributes.
		$this->sync_attributes(
			$user,
			'vendor',
			'user',
			[
				'status__in' => [ 'auto-draft', 'draft', 'publish' ],
				'user'       => $user_id,
			]
		);
	}
}
