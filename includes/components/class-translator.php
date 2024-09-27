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

	/**
	 * Gets WPML translatable post ID.
	 *
	 * @param int    $post_id Parent post ID.
	 * @param string $post_type Post type.
	 * @param string $lang_code Language code.
	 * @return int
	 */
	public function get_wpml_post_id( $post_id, $post_type = 'post', $lang_code = null ) {
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return apply_filters( 'wpml_object_id', $post_id, $post_type, true, $lang_code );
		}

		return $post_id;
	}

	/**
	 * Gets WPML default language.
	 *
	 * @return string
	 */
	public function get_wpml_default_language() {
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return apply_filters( 'wpml_default_language', null );
		}

		return null;
	}

	/**
	 * Gets WPML translatable post language code.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $post_type Post type.
	 * @return string
	 */
	public function get_wpml_post_language_code( $post_id, $post_type ) {
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return apply_filters(
				'wpml_element_language_code',
				null,
				[
					'element_id'   => $post_id,
					'element_type' => $post_type,
				]
			);
		}

		return null;
	}
}
