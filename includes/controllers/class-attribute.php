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
					'forms_resource'            => [
						'path' => '/forms',
						'rest' => true,
					],

					'form_resource'             => [
						'base'   => 'forms_resource',
						'path'   => '/(?P<form_name>[a-z0-9_]+)',
						'method' => 'POST',
						'action' => [ $this, 'get_form' ],
						'rest'   => true,
					],

					'model_categories_resource' => [
						'base'   => 'forms_resource',
						'path'   => '/categories',
						'method' => 'GET',
						'action' => [ $this, 'get_model_categories' ],
						'rest'   => true,
					],

					'meta_boxes_resource'       => [
						'path' => '/meta-boxes',
						'rest' => true,
					],

					'meta_box_resource'         => [
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
	 * Gets model categories.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function get_model_categories( $request ) {

		// Get taxonomy.
		$taxonomy = $request->get_param( 'taxonomy' );

		if ( ! $taxonomy ) {
			return hp\rest_error( 400 );
		}

		$terms_args = [
			'taxonomy'   => $taxonomy,
			'parent'     => 0,
			'fields'     => 'id=>name',
			'hide_empty' => false,
		];

		// Set results.
		$results = null;

		// Set parent categories.
		$parent_categories = [];

		$children_categories = [];

		// Get categories.
		$categories_id = $request->get_param( 'categories_id' );

		if ( $categories_id ) {

			// Get children categories.
			$children_categories = explode( ', ', $categories_id );

			// Get current category.
			$current_category = (array) array_shift( $children_categories );

			// Get parent categories.
			$parent_categories = get_ancestors( $current_category[0], $taxonomy, 'taxonomy' );

			// Merge all categories.
			$categories_id = array_merge( $children_categories, $parent_categories, $current_category );

			if ( $parent_categories || $children_categories ) {
				$results[] = [
					'id'   => 0,
					'text' => hivepress()->translator->get_string( 'all_categories' ),
				];

				$terms_args['include'] = implode( ', ', $categories_id );
				$terms_args['orderby'] = 'id';

				unset( $terms_args['parent'] );
			}
		}

		// Get terms.
		$terms = get_terms( $terms_args );

		if ( ! $terms ) {
			return hp\rest_error( 400 );
		}

		// Set children options.
		$children = [];

		// Set option groups.
		$groups = [];

		foreach ( $terms as $term_id => $term_name ) {
			if ( in_array( $term_id, $parent_categories ) || ( in_array( $term_id, $current_category ) && $children_categories ) ) {

				// Add group.
				$groups[] = [
					'option' => [
						'id'   => $term_id,
						'text' => $term_name,
					],
					'group'  => [
						'children' => [],
					],
				];
			} else {

				// Add children option.
				$children[] = [
					'id'   => $term_id,
					'text' => $term_name,
				];
			}
		}

		if ( $children ) {

			// Add group with children.
			$groups[] = [
				'group' => [
					'children' => $children,
				],
			];
		}

		for ( $i = count( $groups ) - 1; $i >= 0; $i-- ) {
			if ( count( $groups ) - 1 === $i ) {
				if ( ! $groups[ $i ]['group']['children'] ) {

					// Add option to group children.
					array_unshift( $children, $groups[ $i ]['option'] );
					$groups[ $i ]['group']['children'] = $children;
				}

				// Add group to results.
				$results[ $i + 1 ] = $groups[ $i ]['group'];
			} else {

				// Get copy group.
				$child_group = [ $results[ $i + 2 ] ];

				// Add option to copy group.
				array_unshift( $child_group, $groups[ $i ]['option'] );

				// Remove copy group from results.
				unset( $results[ $i + 2 ] );

				// Add copy group to parent group.
				$groups[ $i ]['group']['children'] = $child_group;

				// Add parent group to results.
				$results[ $i + 1 ] = $groups[ $i ]['group'];
			}
		}

		if ( ! $parent_categories && ! $children_categories ) {
			$results = $children;
		}

		return hp\rest_response( 200, $results );
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

		$model_names = array_map(
			function( $name ) {
				return $name . '_attribute';
			},
			hivepress()->attribute->get_models()
		);

		if ( ! in_array( $model_name, $model_names ) || ! in_array( $meta_box, [ $model_name . '_edit', $model_name . '_search' ] ) ) {
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

		// Update field types.
		foreach ( [ 'edit', 'search' ] as $field_context ) {
			$field_name = hp\prefix( $field_context . '_field_type' );

			update_post_meta( $post->ID, $field_name, sanitize_key( $request->get_param( $field_name ) ) );
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
