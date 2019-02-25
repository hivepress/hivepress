<?php
/**
 * Helper functions.
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Adds HivePress prefix.
 *
 * @param mixed $names Names to prefix.
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
 * Removes HivePress prefix.
 *
 * @param mixed $names Names to unprefix.
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
 * @param array  $array Source array.
 * @param string $key Key to search.
 * @param mixed  $default Default value.
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
 * @param array $array Source array.
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
 * Renders HTML attributes.
 *
 * @param array $atts Array of attributes.
 * @return string
 */
function hp_html_attributes( $atts ) {
	$output = '';

	if ( is_array( $atts ) ) {
		foreach ( $atts as $att_name => $att_value ) {
			if ( ! is_null( $att_value ) ) {
				if ( true === $att_value ) {
					$att_value = $att_name;
				}

				$output .= esc_html( $att_name ) . '="' . esc_attr( trim( $att_value ) ) . '" ';
			}
		}
	}

	return trim( $output );
}

/**
 * Sanitizes HTML.
 *
 * @param string $html HTML to sanitize.
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
 * Replaces placeholders with values.
 *
 * @param array  $placeholders Array of placeholders.
 * @param string $text Text to be processed.
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
 * Gets post ID.
 *
 * @param array $args Post arguments.
 * @return int
 */
function hp_get_post_id( $args ) {
	$args = array_merge(
		$args,
		[
			'posts_per_page' => 1,
			'fields'         => 'ids',
		]
	);

	if ( hp_get_array_value( $args, 'post_status' ) === 'any' ) {
		$args['post_status'] = [ 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash' ];
	}

	$post_ids = get_posts( $args );

	return absint( reset( $post_ids ) );
}

/**
 * Gets REST API URL.
 *
 * @param string $path URL path.
 * @return string
 */
function hp_get_rest_url( $path ) {
	return get_rest_url( null, 'hivepress/v1' . $path );
}

/**
 * Gets REST API error.
 *
 * @param int   $code Error code.
 * @param array $errors Additional errors.
 * @return WP_Rest_Response
 */
function hp_rest_error( $code, $errors = [] ) {
	$error = [
		'code' => $code,
	];

	if ( ! empty( $errors ) ) {
		$error['errors'] = array_map(
			function( $error ) {
				return [
					'message' => $error,
				];
			},
			(array) $errors
		);
	}

	return new WP_Rest_Response(
		[
			'error' => $error,
		],
		$code
	);
}
