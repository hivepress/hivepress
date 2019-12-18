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
			[
				'routes' => [
					[
						'path'   => '/templates/(?P<template_name>[a-z\-]+)/blocks',
						'rest'   => true,

						'routes' => [
							[
								'path'   => '/(?P<block_name>[a-z\-]+)',
								'method' => 'GET',
								'action' => [ $this, 'get_block' ],
							],
						],
					],
				],
			],
			$args
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

		// Get blocks.
		$blocks = hp\call_class_method( '\HivePress\Templates\\' . $request->get_param( 'template' ), 'get_blocks' );

		if ( is_null( $blocks ) ) {
			return hp\rest_error( 404 );
		}

		// Get block.
		$block_args = hp\search_array_value( [ 'blocks' => $blocks ], [ 'blocks', $request->get_param( 'block' ) ] );

		if ( is_null( $block_args ) ) {
			return hp\rest_error( 404 );
		}

		// Render block.
		$data = $block_args;

		if ( $request->get_param( 'render' ) ) {
			$block = hp\create_class_instance( '\HivePress\Blocks\\' . $block_args['type'], [ hp\merge_arrays( [ 'context' => $request->get_params() ], $block_args ) ] );

			if ( is_null( $block ) ) {
				return hp\rest_error( 400 );
			}

			$data['html'] = $block->render();
		}

		return new \WP_Rest_Response(
			[
				'data' => $data,
			],
			200
		);
	}
}
