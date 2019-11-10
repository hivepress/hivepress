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
	 * Gets string.
	 *
	 * @param string $key String key.
	 * @return string
	 */
	public function get_string( $key ) {
		return hp\get_array_value( hivepress()->get_config( 'strings' ), $key, '' );
	}
}
