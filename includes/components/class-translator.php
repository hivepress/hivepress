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
 * Translator component class.
 *
 * @class Translator
 */
final class Translator {

	/**
	 * Gets language code.
	 *
	 * @return string
	 */
	public function get_language() {
		$parts = explode( '_', get_locale() );

		return reset( $parts );
	}

	/**
	 * Gets region code.
	 *
	 * @return string
	 */
	public function get_region() {
		$parts = explode( '_', get_locale() );

		if ( count( $parts ) === 2 ) {
			return end( $parts );
		}

		return '';
	}

	/**
	 * Gets translation string.
	 *
	 * @param string $key String key.
	 * @return string
	 */
	public function get_string( $key ) {
		return hp\get_array_value( hivepress()->get_config( 'strings' ), $key, '' );
	}
}
