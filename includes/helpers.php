<?php
/**
 * Contains helper functions.
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Adds plugin prefix.
 *
 * @param mixed $names
 * @return mixed
 */
function hp_prefix( $names ) {
	$prefixed = '';

	if ( is_array( $names ) ) {
		$prefixed = array_map(
			function( $name ) {
				return 'hp_' . $name;
			},
			$names
		);
	} else {
		$prefixed = 'hp_' . $names;
	}

	return $prefixed;
}

/**
 * Removes plugin prefix.
 *
 * @param mixed $names
 * @return mixed
 */
function hp_unprefix( $names ) {
	$unprefixed = '';

	if ( is_array( $names ) ) {
		$unprefixed = array_map(
			function( $name ) {
				return str_replace( 'hp_', '', $name );
			},
			$names
		);
	} else {
		$unprefixed = str_replace( 'hp_', '', $names );
	}

	return $unprefixed;
}

/**
 * Gets array item value by key.
 *
 * @param array  $array
 * @param string $key
 * @param mixed  $default
 * @return mixed
 */
function hp_get_array_value( $array, $key, $default = null ) {
	$value = $default;

	if ( is_array( $array ) && isset( $array[ $key ] ) ) {
		$value = $array[ $key ];
	}

	return $value;
}

/**
 * Sorts array by custom order.
 *
 * @param array $array
 * @return array
 */
function hp_sort_array( $array ) {
	$sorted = [];

	foreach ( $array as $key => $value ) {
		if ( is_array( $value ) ) {
			if ( ! isset( $value['order'] ) ) {
				$value['order'] = 0;
			}

			$sorted[ $key ] = $value;
		}
	}

	$sorted = wp_list_sort( $sorted, 'order', 'ASC', true );

	return $sorted;
}

/**
 * Merges two arrays with mixed values.
 *
 * @return array
 */
function hp_merge_arrays() {
	$merged = [];

	$arrays = func_get_args();

	foreach ( $arrays as $array ) {
		foreach ( $array as $key => $value ) {
			if ( ! isset( $merged[ $key ] ) || ( ! is_array( $merged[ $key ] ) || ! is_array( $value ) ) ) {
				$merged[ $key ] = $value;
			} else {
				$merged[ $key ] = hp_merge_arrays( $merged[ $key ], $value );
			}
		}
	}

	return $merged;
}

/**
 * Renders HTML attributes.
 *
 * @param array $atts
 * @return string
 */
function hp_html_attributes( $atts ) {
	$output = '';

	foreach ( $atts as $att_name => $att_value ) {
		if ( ! is_null( $att_value ) ) {
			$output .= esc_html( $att_name ) . '="' . esc_attr( trim( $att_value ) ) . '" ';
		}
	}

	return trim( $output );
}

/**
 * Replaces placeholders with values.
 *
 * @param array  $placeholders
 * @param string $text
 * @return string
 */
function hp_replace_placeholders( $placeholders, $text ) {
	foreach ( $placeholders as $placeholder_name => $placeholder_value ) {
		if ( ! is_array( $placeholder_value ) ) {
			$text = str_replace( '%' . $placeholder_name . '%', $placeholder_value, $text );
		}
	}

	return $text;
}

/**
 * Sanitizes HTML.
 *
 * @param string $html
 * @return string
 */
function hp_sanitize_html( $html ) {
	$tags = [
		'strong' => [],
		'a'      => [
			'href'   => [],
			'target' => [],
		],
		'i'      => [
			'class' => [],
		],
	];

	return wp_kses( $html, $tags );
}

/**
 * Sanitizes ID.
 *
 * @param string $text
 * @return string
 */
function hp_sanitize_id( $text ) {
	$id = $text;

	if ( function_exists( 'transliterator_transliterate' ) ) {
		$id = transliterator_transliterate( 'Any-Latin; Latin-ASCII', $id );
	}

	$id = trim( preg_replace( '/[^a-z0-9\s\-]/', '', $id ) );
	$id = str_replace( '-', '_', sanitize_title( $id ) );
	$id = substr( $id, 0, 32 );

	if ( '' === $id ) {
		$id = md5( $text );
	}

	return $id;
}

/**
 * Gets current page number.
 *
 * @return int
 */
function hp_get_current_page() {
	$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
	$page = get_query_var( 'page' ) ? get_query_var( 'page' ) : $page;

	return absint( $page );
}

/**
 * Gets the current URL.
 *
 * @param array $query
 * @return string
 */
function hp_get_current_url( $query = [] ) {
	global $wp;

	$query_string = '';

	if ( ! empty( $_GET ) || ! empty( $query ) ) {
		$query_string = '/?' . http_build_query( array_merge( $_GET, $query ) );
	}

	return home_url( $wp->request . $query_string );
}

/**
 * Validates redirect URL.
 *
 * @param mixed $url
 * return bool
 */
function hp_validate_redirect( $url ) {
	return wp_validate_redirect( $url ) && ( strpos( $url, 'http://' ) === 0 || strpos( $url, 'https://' ) === 0 );
}

/**
 * Redirects HTTP request.
 *
 * @param mixed $url
 */
function hp_redirect( $url = null ) {
	if ( is_null( $url ) || true === $url ) {
		$url = hp_get_current_url();
	}

	wp_safe_redirect( esc_url( $url ) );
	exit;
}

/**
 * Gets remote JSON.
 *
 * @param string $url
 * @param array  $headers
 * @return mixed
 */
function hp_get_remote_json( $url, $headers = [] ) {
	$response = wp_remote_get(
		$url,
		[
			'headers'   => $headers,
			'sslverify' => false,
		]
	);

	if ( ! is_wp_error( $response ) && 200 === $response['response']['code'] ) {
		return json_decode( $response['body'], true );
	}

	return false;
}

/**
 * Gets post ID.
 *
 * @param array $args
 * @return int
 */
function hp_get_post_id( $args ) {
	$args = array_merge(
		[
			'posts_per_page' => 1,
			'fields'         => 'ids',
		],
		$args
	);

	$posts = get_posts( $args );

	return absint( reset( $posts ) );
}
