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
}
