<?php
/**
 * Helper functions.
 *
 * @package HivePress
 */

namespace HivePress\Helpers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Adds HivePress prefix.
 *
 * @param mixed $names Names to prefix.
 * @return mixed
 */
function prefix( $names ) {
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
function unprefix( $names ) {
	$unprefixed = '';

	if ( is_array( $names ) ) {
		$unprefixed = array_map(
			function( $name ) {
				return preg_replace( '/^hp_/', '', $name );
			},
			$names
		);
	} else {
		$unprefixed = preg_replace( '/^hp_/', '', $names );
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
function get_array_value( $array, $key, $default = null ) {
	$value = $default;

	if ( is_array( $array ) && isset( $array[ $key ] ) ) {
		$value = $array[ $key ];
	}

	return $value;
}

/**
 * Searches array item value by keys.
 *
 * @param array $array Source array.
 * @param mixed $keys Keys to search.
 * @return mixed
 */
function search_array_value( $array, $keys ) {
	$keys = (array) $keys;

	foreach ( $keys as $key ) {
		if ( isset( $array[ $key ] ) ) {
			if ( end( $keys ) === $key ) {
				return $array[ $key ];
			} elseif ( is_array( $array[ $key ] ) ) {
				$array = $array[ $key ];
			}
		} else {
			foreach ( $array as $subarray ) {
				if ( is_array( $subarray ) ) {
					$value = search_array_value( $subarray, $keys );

					if ( ! is_null( $value ) ) {
						return $value;
					}
				}
			}

			break;
		}
	}
}

/**
 * Sorts array by custom order.
 *
 * @param array $array Source array.
 * @return array
 */
function sort_array( $array ) {
	$sorted = [];

	foreach ( $array as $key => $value ) {
		if ( is_array( $value ) ) {
			if ( ! isset( $value['_order'] ) ) {
				$value['_order'] = 0;
			}

			$sorted[ $key ] = $value;
		}
	}

	$sorted = wp_list_sort( $sorted, '_order', 'ASC', true );

	return $sorted;
}

/**
 * Merges arrays with mixed values.
 *
 * @return array
 */
function merge_arrays() {
	$merged = [];

	foreach ( func_get_args() as $array ) {
		foreach ( $array as $key => $value ) {
			if ( ! isset( $merged[ $key ] ) || ( ! is_array( $merged[ $key ] ) || ! is_array( $value ) ) ) {
				if ( is_numeric( $key ) ) {
					$merged[] = $value;
				} elseif ( ! is_null( $value ) ) {
					$merged[ $key ] = $value;
				} else {
					unset( $merged[ $key ] );
				}
			} else {
				$merged[ $key ] = merge_arrays( $merged[ $key ], $value );
			}
		}
	}

	return $merged;
}

/**
 * Merges trees with mixed values.
 *
 * @param array  $parent_tree Parent tree.
 * @param array  $child_tree Child tree.
 * @param string $tree_key Tree key.
 * @param string $node_key Node key.
 * @return array
 */
function merge_trees( $parent_tree, $child_tree, $tree_key, $node_key = null ) {
	if ( isset( $parent_tree[ $tree_key ] ) ) {
		foreach ( $parent_tree[ $tree_key ] as $parent_node_key => $parent_node ) {
			$parent_tree[ $tree_key ][ $parent_node_key ] = merge_trees( $parent_node, $child_tree, $tree_key, $parent_node_key );
		}
	}

	if ( is_null( $node_key ) ) {
		unset( $child_tree[ $tree_key ] );

		$parent_tree = merge_arrays( $parent_tree, $child_tree );
	} else {
		$child_node = search_array_value( $child_tree, [ $tree_key, $node_key ] );

		if ( ! is_null( $child_node ) ) {
			$parent_tree = merge_arrays( $parent_tree, $child_node );
		}
	}

	return $parent_tree;
}

/**
 * Renders HTML attributes.
 *
 * @param array $atts Array of attributes.
 * @return string
 */
function html_attributes( $atts ) {
	$output = '';

	if ( is_array( $atts ) ) {
		foreach ( $atts as $name => $value ) {
			if ( true === $value ) {
				$value = $name;
			} elseif ( is_array( $value ) ) {
				$value = implode( ' ', $value );
			}

			$output .= esc_attr( $name ) . '="' . esc_attr( trim( $value ) ) . '" ';
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
function sanitize_html( $html ) {
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
 * Sanitizes slug.
 *
 * @param string $text Text to sanitize.
 * @return string
 */
function sanitize_slug( $text ) {
	return str_replace( '_', '-', strtolower( $text ) );
}

/**
 * Sanitizes key.
 *
 * @param string $text Text to sanitize.
 * @return string
 */
function sanitize_key( $text ) {
	$key = $text;

	if ( function_exists( 'transliterator_transliterate' ) ) {
		$key = transliterator_transliterate( 'Any-Latin; Latin-ASCII; Lower()', $key );
	} else {
		$key = strtolower( $key );
	}

	$key = preg_replace( '/[^a-z0-9]+/', '_', $key );
	$key = ltrim( trim( $key, '_' ), '0..9' );

	if ( '' === $key ) {
		$key = 'a' . substr( md5( $text ), 0, 31 );
	}

	return $key;
}

/**
 * Replaces tokens with values.
 *
 * @param array  $tokens Array of tokens.
 * @param string $text Text to be processed.
 * @return string
 */
function replace_tokens( $tokens, $text ) {
	foreach ( $tokens as $name => $value ) {
		if ( ! is_array( $value ) ) {
			$text = str_replace( '%' . $name . '%', $value, $text );
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
function get_post_id( $args ) {
	$args = array_merge(
		$args,
		[
			'posts_per_page' => 1,
			'fields'         => 'ids',
		]
	);

	$post_ids = get_posts( $args );

	return absint( reset( $post_ids ) );
}

/**
 * Gets REST API URL.
 *
 * @param string $url Redirect URL.
 * @return bool
 */
function validate_redirect( $url ) {
	return wp_validate_redirect( $url ) && ( strpos( $url, 'http://' ) === 0 || strpos( $url, 'https://' ) === 0 );
}

/**
 * Gets current URL.
 *
 * @return string
 */
function get_current_url() {
	global $wp;

	$query_string = '';

	if ( ! empty( $_GET ) ) {
		$query_string = '/?' . http_build_query( $_GET );
	}

	return home_url( $wp->request . $query_string );
}

/**
 * Gets REST API URL.
 *
 * @param string $path URL path.
 * @return string
 */
function get_rest_url( $path = '' ) {
	return \get_rest_url( null, 'hivepress/v1' . $path );
}

/**
 * Gets REST API error.
 *
 * @param int   $code Error code.
 * @param array $errors Additional errors.
 * @return WP_Rest_Response
 */
function rest_error( $code, $errors = [] ) {
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

	return new \WP_Rest_Response(
		[
			'error' => $error,
		],
		$code
	);
}

// todo.
function rest_response( $code, $data=null ) {
	if(is_null($data)) {
		return new \WP_Rest_Response( (object) [], $code );
	}

	return new \WP_Rest_Response(
		[
			'data' => $data,
		],
		$code
	);
}

/**
 * Gets current page number.
 *
 * @return int
 */
function get_current_page() {
	$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
	$page = get_query_var( 'page' ) ? get_query_var( 'page' ) : $page;

	return absint( $page );
}

/**
 * Creates class instance.
 *
 * @param string $class Class name.
 * @param array  $args Instance arguments.
 * @return mixed
 */
function create_class_instance( $class, $args = [] ) {
	if ( class_exists( $class ) && ! ( new \ReflectionClass( $class ) )->isAbstract() ) {
		if ( empty( $args ) ) {
			return new $class();
		} else {
			return new $class( ...$args );
		}
	}
}

/**
 * Calls class method.
 *
 * @param string $class Class name.
 * @param string $method Method name.
 * @param array  $args Method arguments.
 * @return mixed
 */
function call_class_method( $class, $method, $args = [] ) {
	if ( class_exists( $class ) && method_exists( $class, $method ) ) {
		return call_user_func_array( [ $class, $method ], $args );
	}
}

/**
 * Gets class name.
 *
 * @param string $class Class name.
 * @return string
 */
function get_class_name( $class ) {
	return strtolower( ( new \ReflectionClass( $class ) )->getShortName() );
}

// todo.
function get_redirect_url( $url ) {
	return add_query_arg( 'redirect', rawurlencode( get_current_url() ), $url );
}

function fetch_redirect_url( $default ) {
	hp\get_array_value( $_GET, 'redirect', $default );
}

function get_class_parents($class) {
	return array_reverse(array_merge([$class], class_parents($class)));
}
