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
	 * Gets language code.
	 *
	 * @return string
	 */
	public function get_language() {
		return hp\get_first_array_value( explode( '_', get_locale() ) );
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
}
