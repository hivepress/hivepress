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
		$parts = explode( '\\', str_replace( '_', '-', strtolower( $class ) ) );

		if ( count( $parts ) > 1 && reset( $parts ) === 'hivepress' ) {
			$filename = 'class-' . end( $parts ) . '.php';

			array_shift( $parts );
			array_pop( $parts );

			$filepath = rtrim( __DIR__ . '/' . implode( '/', $parts ), '/' ) . '/' . $filename;

			if ( file_exists( $filepath ) ) {
				require_once $filepath;
			}
		}
	}
);
