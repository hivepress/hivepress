<?php
/**
 * Translator component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles translations.
 */
final class Translator extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {
		if ( $this->is_multilingual() ) {

			// Register options.
			add_filter( 'alloptions', [ $this, 'register_options' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Gets language code.
	 *
	 * @return string
	 */
	public function get_language() {
		$language = hp\get_first_array_value( explode( '_', get_locale() ) );

		if ( $this->is_multilingual() ) {
			$language = apply_filters( 'wpml_current_language', $language );
		}

		return $language;
	}

	/**
	 * Gets region code.
	 *
	 * @return string
	 */
	public function get_region() {
		$parts = explode( '_', get_locale() );

		if ( count( $parts ) > 1 ) {
			return hp\get_last_array_value( $parts );
		}
	}

	/**
	 * Gets translation string.
	 *
	 * @param string $key String key.
	 * @return string
	 */
	public function get_string( $key ) {
		return hp\get_array_value( hivepress()->get_config( 'strings' ), $key );
	}

	/**
	 * Checks multilingual status.
	 *
	 * @return bool
	 */
	public function is_multilingual() {
		return defined( 'ICL_SITEPRESS_VERSION' );
	}

	/**
	 * Registers options.
	 *
	 * @param array $options Options.
	 * @return array
	 */
	public function register_options( $options ) {
		remove_filter( 'alloptions', [ $this, 'register_options' ] );

		foreach ( $options as $name => $value ) {
			if ( strpos( $name, 'hp_page_' ) === 0 && absint( $value ) > 1 ) {
				add_filter( 'option_' . $name, [ $this, 'get_object_id' ] );
			}
		}

		return $options;
	}

	/**
	 * Gets translated object ID.
	 *
	 * @param int    $id Object ID.
	 * @param string $type Object type.
	 * @return int
	 */
	public function get_object_id( $id, $type = 'page' ) {
		return apply_filters( 'wpml_object_id', $id, $type );
	}
}
