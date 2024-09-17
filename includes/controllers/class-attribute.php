<?php
/**
 * Attribute controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages model attributes.
 */
final class Attribute extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					'forms_resource'      => [
						'path' => '/forms',
						'rest' => true,
					],

					'form_resource'       => [
						'base'   => 'forms_resource',
						'path'   => '/(?P<form_name>[a-z0-9_]+)',
						'method' => 'POST',
						'action' => [ $this, 'get_form' ],
						'rest'   => true,
					],

					'meta_boxes_resource' => [
						'path' => '/meta-boxes',
						'rest' => true,
					],

					'meta_box_resource'   => [
						'base'   => 'meta_boxes_resource',
						'path'   => '/(?P<meta_box_name>[a-z0-9_]+)',
						'method' => 'POST',
						'action' => [ $this, 'get_meta_box' ],
						'rest'   => true,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Gets form.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function get_form( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Check form model.
		$form_name = sanitize_key( $request->get_param( 'form_name' ) );

		$model_name = sanitize_key( $request->get_param( '_model' ) );
		$model_id   = absint( $request->get_param( '_id' ) );

		if ( 'vendor_submit' === $form_name && 'user' === $model_name ) {
			$model_name = 'vendor';
			$model_id   = Models\Vendor::query()->filter(
				[
					'status' => [ 'auto-draft', 'draft', 'publish' ],
					'user'   => $model_id,
				]
			)->get_first_id();
		}

		if ( ! in_array( $model_name, hivepress()->attribute->get_models() ) || $form_name !== $model_name . '_submit' ) {
			return hp\rest_error( 400 );
		}

		// Get model.
		$model = hivepress()->model->get_model_object( $model_name, $model_id );

		if ( ! $model ) {
			return hp\rest_error( 404 );
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_others_posts' ) && ( get_current_user_id() !== $model->get_user__id() || ! in_array( $model->get_status(), [ 'auto-draft', 'draft', 'publish' ] ) ) ) {
			return hp\rest_error( 403 );
		}

		// Update categories.
		$model->set_categories( $request->get_param( 'categories' ) )->save_categories();

		// Create form.
		$form = null;

		if ( 'vendor_submit' === $form_name ) {
			$form = hp\create_class_instance( '\HivePress\Forms\User_Update_Profile', [ [ 'model' => $model->get_user() ] ] );
		} else {
			$form = hp\create_class_instance( '\HivePress\Forms\\' . $form_name, [ [ 'model' => $model ] ] );
		}

		if ( ! $form || ! in_array( $form::get_meta( 'model' ), [ $model_name, 'user' ] ) ) {
			return hp\rest_error( 404 );
		}

		// Render form.
		$output = $form->set_values( $request->get_params(), true )->render();

		return hp\rest_response(
			200,
			[
				'html' => $output,
			]
		);
	}

	/**
	 * Gets meta box.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function get_meta_box( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Check meta box model.
		$meta_box   = sanitize_key( $request->get_param( 'meta_box_name' ) );
		$model_name = sanitize_key( $request->get_param( '_model' ) );

		$model_names = array_merge(
			array_map(
				function( $name ) {
					return $name . '_attribute';
				},
				hivepress()->attribute->get_models()
			),
			hivepress()->attribute->get_models( 'post' )
		);

		if ( ! in_array( $model_name, $model_names ) || ! in_array( $meta_box, [ $model_name . '_attributes', $model_name . '_edit', $model_name . '_search' ] ) ) {
			return hp\rest_error( 400 );
		}

		// Get post.
		global $post;

		$post = get_post( absint( $request->get_param( '_id' ) ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		if ( ! $post || hp\prefix( $model_name ) !== $post->post_type ) {
			return hp\rest_error( 404 );
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return hp\rest_error( 403 );
		}

		if ( $model_name . '_attributes' === $meta_box ) {

			// Update category.
			$taxonomy = hp\prefix( $model_name . '_category' );

			if ( taxonomy_exists( $taxonomy ) ) {
				wp_set_post_terms( $post->ID, array_map( 'absint', (array) $request->get_param( 'hp_categories' ) ), $taxonomy );
			}
		} else {

			// Update field types.
			foreach ( [ 'edit', 'search' ] as $field_context ) {
				$field_name = hp\prefix( $field_context . '_field_type' );

				update_post_meta( $post->ID, $field_name, sanitize_key( $request->get_param( $field_name ) ) );
			}
		}

		// Render meta box.
		$output = hivepress()->admin->render_meta_box(
			$post,
			[
				'id'       => hp\prefix( $meta_box ),
				'defaults' => $request->get_params(),
				'echo'     => false,
			]
		);

		return hp\rest_response(
			200,
			[
				'html' => $output,
			]
		);
	}
}
