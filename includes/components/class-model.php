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
 * Model component class.
 *
 * @class Model
 */
final class Model extends Component {

	/**
	 * Gets model name.
	 *
	 * @param string $type Model type.
	 * @param string $alias Model alias.
	 * @return mixed
	 */
	public function get_model_name( $type, $alias ) {
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
	public function get_field_name( $model, $alias ) {

		// Create model.
		$model = hp\create_class_instance( '\HivePress\Models\\' . $model );

		if ( $model ) {

			// Get fields aliases.
			$aliases = array_map(
				function( $field ) {
					$alias = null;

					if ( $field->get_arg( '_relation' ) === 'many_to_many' ) {
						if ( $field->get_arg( '_alias' ) ) {
							$alias = $field->get_arg( '_alias' );
						} else {
							$alias = hp\call_class_method( '\HivePress\Models\\' . $field->get_arg( '_model' ), '_get_meta', [ 'alias' ] );
						}
					} elseif ( $field->get_arg( '_external' ) ) {
						$alias = $field->get_arg( '_alias' );
					}

					return $alias;
				},
				$model->_get_fields()
			);

			if ( in_array( $alias, $aliases, true ) ) {
				return array_search( $alias, $aliases, true );
			}
		}
	}
}
