<?php
/**
 * Model form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Model form class.
 *
 * @class Model_Form
 */
abstract class Model_Form extends Form {

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected static $model;

	/**
	 * Model object.
	 *
	 * @var object
	 */
	protected $object;

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {

		// Set model object.
		$this->set_object( hp\get_array_value( $args, 'object' ) );

		parent::__construct( $args );
	}

	/**
	 * Bootstraps form properties.
	 */
	protected function bootstrap() {
		$attributes = [];

		// Set model name.
		$attributes['data-model'] = static::$model;

		// Set object ID.
		if ( $this->object->get_id() ) {
			$attributes['data-id'] = $this->object->get_id();
		}

		// Set field values.
		$this->set_values( $this->object->serialize() );

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::bootstrap();
	}

	/**
	 * Gets model name.
	 *
	 * @return string
	 */
	final public static function get_model() {
		return static::$model;
	}

	/**
	 * Sets model object.
	 *
	 * @param mixed $object Model object.
	 */
	final protected function set_object( $object ) {
		if ( ! isset( $this->object ) ) {
			if ( ! is_object( $object ) || strtolower( get_class( $object ) ) !== strtolower( 'HivePress\Models\\' . static::$model ) ) {
				$object = hp\create_class_instance( '\HivePress\Models\\' . static::$model );
			}

			$this->object = $object;
		}
	}

	/**
	 * Sets form fields.
	 *
	 * @param array $fields Form fields.
	 */
	final protected function set_fields( $fields ) {
		foreach ( $fields as $name => $args ) {
			if ( isset( $this->object->_get_fields()[ $name ] ) ) {
				$fields[ $name ] = hp\merge_arrays( $this->object->_get_fields()[ $name ]->get_args(), $args );
			}
		}

		parent::set_fields( $fields );
	}
}
