<?php
/**
 * Autoloading function.
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Register autoloading function.
spl_autoload_register(
	function ( $class ) {
		$dirs = explode( '\\', str_replace( '_', '-', strtolower( $class ) ) );

		if ( count( $dirs ) > 1 && reset( $dirs ) === 'hivepress' ) {
			$filename = 'class-' . end( $dirs ) . '.php';

			array_shift( $dirs );
			array_pop( $dirs );

			$filepath = rtrim( __DIR__ . '/' . implode( '/', $dirs ), '/' ) . '/' . $filename;

			if ( file_exists( $filepath ) ) {
				require_once $filepath;
			}
		}
	}
);
