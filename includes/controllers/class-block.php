<?php
/**
 * Block controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Block controller class.
 *
 * @class Block
 */
class Block extends Controller {

	/**
	 * Controller name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Controller routes.
	 *
	 * @var array
	 */
	protected static $routes = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Controller arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					[
						'path'      => '/templates/(?P<template_name>[a-z\-]+)/blocks',
						'rest'      => true,
						'endpoints' => [
							[
								'path'    => '/(?P<block_name>[a-z\-]+)',
								'methods' => 'GET',
								'action'  => 'get_block',
							],
						],
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Gets block.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function get_block( $request ) {

		// Check authentication.
		$nonce = hp\get_array_value( $request->get_params(), '_wpnonce', $request->get_header( 'X-WP-Nonce' ) );

		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return hp\rest_error( 401 );
		}

		// Get template.
		$template_args = hivepress()->get_config( 'templates/' . str_replace( '-', '_', $request->get_param( 'template' ) ) );

		if ( is_null( $template_args ) ) {
			return hp\rest_error( 404 );
		}

		// Get block.
		$block_args = hp\search_array_value( $template_args, [ 'blocks', str_replace( '-', '_', $request->get_param( 'block_name' ) ) ] );

		if ( is_null( $block_args ) ) {
			return hp\rest_error( 404 );
		}

		// Render block.
		$data = $block_args;

		if ( $request->get_param( 'render' ) ) {
			$block_class = '\HivePress\Blocks\\' . $block_args['type'];

			if ( ! class_exists( $block_class ) ) {
				return hp\rest_error( 400, esc_html__( 'Error rendering block', 'hivepress' ) );
			}

			$data['html'] = ( new $block_class( hp\merge_arrays( [ 'context' => $request->get_params() ], $block_args ) ) )->render();
		}

		return new \WP_Rest_Response(
			[
				'data' => $data,
			],
			200
		);
	}
}
