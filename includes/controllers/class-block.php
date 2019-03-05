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
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
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
			]
		);

		parent::__construct( $args );
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
		$template_args = hp\get_array_value( hivepress()->get_config( 'templates' ), str_replace( '-', '_', $request->get_param( 'template_name' ) ) );

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

			$data['html'] = ( new $block_class( $block_args ) )->render();
		}

		return new \WP_Rest_Response(
			[
				'data' => $data,
			],
			200
		);
	}
}
