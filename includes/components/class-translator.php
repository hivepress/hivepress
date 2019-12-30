<?php
/**
 * Translator component.
 *
 * @package HivePress\Components
 */
// ok.
namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Translator component class.
 *
 * @class Translator
 */
final class Translator extends Component {

	/**
	 * Gets language code.
	 *
	 * @return string
	 */
	public function get_language() {
		return explode( '_', get_locale() )[0];
	}

	/**
	 * Gets region code.
	 *
	 * @return string
	 */
	public function get_region() {
		$parts = explode( '_', get_locale() );

		if ( count( $parts ) > 1 ) {
			return end( $parts );
		}
	}

	/**
	 * Gets translation string.
	 *
	 * @param string $key String key.
	 * @return mixed
	 */
	public function get_string( $key ) {
		return hp\get_array_value( hivepress()->get_config( 'strings' ), $key );
	}
}
