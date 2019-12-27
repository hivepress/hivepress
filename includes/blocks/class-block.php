<?php
/**
 * Abstract block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract block class.
 *
 * @class Block
 */
abstract class Block {
	use Traits\Mutator;
	use Traits\Meta;

	/**
	 * Block meta.
	 *
	 * @var array
	 */
	protected static $meta;

	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Block context.
	 *
	 * @var array
	 */
	protected $context = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Block arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'meta' => [
					'name'     => hp\get_class_name( static::class ),
					'settings' => [],
				],
			],
			$args
		);

		// Set properties.
		foreach ( $args as $name => $value ) {
			static::set_static_property( $name, $value );
		}
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
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
	 * Bootstraps block properties.
	 */
	protected function boot() {}

	/**
	 * Sets block meta.
	 *
	 * @param array $meta Block meta.
	 */
	final protected static function set_meta( $meta ) {

		// Set settings.
		if ( isset( $meta['settings'] ) ) {
			$settings = [];

			foreach ( hp\sort_array( $meta['settings'] ) as $name => $args ) {

				// Create field.
				$field = hp\create_class_instance( '\HivePress\Fields\\' . $args['type'], [ array_merge( $args, [ 'name' => $name ] ) ] );

				// Add field.
				if ( $field ) {
					$settings[ $name ] = $field;
				}
			}

			$meta['settings'] = $settings;
		}

		static::$meta = $meta;
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	abstract public function render();
}
