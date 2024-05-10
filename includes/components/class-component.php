<?php
/**
 * Abstract component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract component class.
 */
abstract class Component {
	use Traits\Mutator;

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->boot();
	}

	/**
	 * Bootstraps component properties.
	 */
	protected function boot() {}

	/**
	 * Sets the action and filter callbacks.
	 *
	 * @param array $callbacks Callback arguments.
	 */
	final protected function set_callbacks( $callbacks ) {
		foreach ( $callbacks as $callback ) {

			// Get hook type.
			$type = hp\get_array_value( $callback, 'filter' ) ? 'filter' : 'action';

			// Register callback.
			call_user_func_array(
				'add_' . $type,
				[
					$callback['hook'],
					$callback['action'],
					hp\get_array_value( $callback, '_order', 10 ),
					hp\get_array_value( $callback, 'args', 1 ),
				]
			);
		}
	}

	/**
	 * Syncs attributes between two models.
	 *
	 * @param object $model Object of sync.
	 * @param string $with Model name to sync with.
	 * @param string $filter_by Model name to filter attributes by.
	 * @param array $filters Filters to find model to sync with.
	 */
	final protected function sync_attributes( $model, $with, $filter_by, $filters = [] ) {

		// Get model.
		$class = '\HivePress\Models\\' . $with;

		// Check model.
		if ( ! class_exists( $class ) ) {
			return;
		}

		// Get attributes.
		$attributes = array_filter(
			hivepress()->attribute->get_attributes( $filter_by ),
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
		$query = hp\call_class_method( $class, 'query' );

		// Get models.
		$sync_models = $query->filter($filters)->get();

		// Update model.
		foreach ( $sync_models as $sync_model ) {
			if ( array_intersect_key( $sync_model->serialize(), $attributes ) !== $values ) {
				$sync_model->fill( $values )->save( array_keys( $values ) );
			}
		}
	}
}
