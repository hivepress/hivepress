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
 * Gets first array item value.
 *
 * @since 1.3.1
 * @param array $array Source array.
 * @param mixed $default Default value.
 * @return mixed
 */
function get_first_array_value( $array, $default = null ) {
	$value = $default;

	if ( is_array( $array ) && $array ) {
		$value = reset( $array );
	}

	return $value;
}

/**
 * Gets last array item value.
 *
 * @since 1.3.1
 * @param array $array Source array.
 * @param mixed $default Default value.
 * @return mixed
 */
function get_last_array_value( $array, $default = null ) {
	$value = $default;

	if ( is_array( $array ) && $array ) {
		$value = end( $array );
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
			if ( get_last_array_value( $keys ) === $key ) {
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
				} else {
					$merged[ $key ] = $value;
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
function merge_trees( $parent_tree, $child_tree, $tree_key = null, $node_key = null ) {
	if ( is_null( $tree_key ) ) {
		if ( $parent_tree ) {
			$tree_key = get_first_array_value( array_keys( $parent_tree ) );
		} elseif ( $child_tree ) {
			$tree_key = get_first_array_value( array_keys( $child_tree ) );
		}
	}

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
 * Sorts array by custom order.
 *
 * @param array $array Source array.
 * @return array
 */
function sort_array( $array ) {
	foreach ( $array as $key => $value ) {
		if ( is_array( $value ) ) {

			// @deprecated since version 1.3.0.
			if ( isset( $value['order'] ) && is_int( $value['order'] ) ) {
				$array[ $key ]['_order'] = $value['order'];
			} elseif ( ! isset( $value['_order'] ) ) {
				$array[ $key ]['_order'] = 0;
			}
		}
	}

	return wp_list_sort( $array, '_order', 'ASC', true );
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

/**
 * Gets class parents.
 *
 * @param string $class Class name.
 * @return array
 */
function get_class_parents( $class ) {
	return array_reverse( array_merge( [ $class ], class_parents( $class ) ) );
}

/**
 * Checks object class.
 *
 * @param object $object Class object.
 * @param string $class Class name.
 * @return bool
 */
function is_class_instance( $object, $class ) {
	return is_object( $object ) && strtolower( get_class( $object ) ) === ltrim( strtolower( $class ), '\\' );
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
		$instance = null;

		if ( empty( $args ) ) {
			$instance = new $class();
		} else {
			$instance = new $class( ...$args );
		}

		return $instance;
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
 * Replaces tokens with values.
 *
 * @param array  $tokens Array of tokens.
 * @param string $text Text to be processed.
 * @return string
 */
function replace_tokens( $tokens, $text ) {
	foreach ( $tokens as $name => $value ) {
		if ( is_object( $value ) && strpos( get_class( $value ), 'HivePress\Models\\' ) === 0 ) {
			preg_match_all( '/%' . $name . '\.([a-z0-9_]+)%/', $text, $matches );

			$fields = get_last_array_value( $matches );

			if ( $fields ) {
				foreach ( $value->_get_fields() as $field ) {
					if ( in_array( $field->get_name(), $fields, true ) ) {
						$text = str_replace( '%' . $name . '.' . $field->get_name() . '%', $field->display(), $text );
					}
				}
			}
		} elseif ( ! is_array( $value ) ) {
			$text = str_replace( '%' . $name . '%', $value, $text );
		}
	}

	return $text;
}

/**
 * Renders HTML attributes.
 *
 * @param array $attributes Array of attributes.
 * @return string
 */
function html_attributes( $attributes ) {
	$output = '';

	if ( is_array( $attributes ) ) {
		foreach ( $attributes as $name => $value ) {
			if ( true === $value ) {
				$value = $name;
			} elseif ( is_array( $value ) ) {
				$value = implode( ' ', $value );
			}

			$output .= esc_attr( $name ) . '="' . esc_attr( $value ) . '" ';
		}
	}

	return rtrim( $output );
}

/**
 * Sanitizes HTML.
 *
 * @param string $html HTML to sanitize.
 * @param array  $tags Allowed HTML tags.
 * @return string
 */
function sanitize_html( $html, $tags = [] ) {
	if ( empty( $tags ) ) {
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
	} elseif ( true === $tags ) {
		$tags = 'post';
	}

	return wp_kses( $html, $tags );
}

/**
 * Sanitizes slug.
 *
 * @param string $text Text to sanitize.
 * @return string
 */
function sanitize_slug( $text ) {
	return str_replace( '_', '-', \sanitize_key( $text ) );
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

	if ( empty( $key ) ) {
		$key = 'a' . substr( md5( $text ), 0, 31 );
	}

	return $key;
}

/**
 * Formats number.
 *
 * @param float $number Number.
 * @return string
 */
function format_number( $number ) {
	return number_format_i18n( $number, strlen( substr( strrchr( (string) $number, '.' ), 1 ) ) );
}

/**
 * Checks plugin status.
 *
 * @param string $name Plugin name.
 * @return bool
 */
function is_plugin_active( $name ) {
	return class_exists( $name ) || function_exists( $name );
}

/**
 * Checks REST request.
 *
 * @return bool
 */
function is_rest() {
	return defined( 'REST_REQUEST' ) && REST_REQUEST;
}

/**
 * Gets REST API response.
 *
 * @param int   $code Error code.
 * @param array $data Response data.
 * @return WP_Rest_Response
 */
function rest_response( $code, $data = null ) {
	$response = new \WP_Rest_Response( (object) [], $code );

	if ( ! is_null( $data ) ) {
		$response = new \WP_Rest_Response(
			[
				'data' => $data,
			],
			$code
		);
	}

	return $response;
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

	if ( $errors ) {
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

/**
 * Gets column width.
 *
 * @param int $number Columns number.
 * @return int
 */
function get_column_width( $number ) {
	$number = absint( $number );
	$width  = 12;

	if ( $number > 0 && $number <= 12 ) {
		$width = round( $width / $number );
	}

	return $width;
}
