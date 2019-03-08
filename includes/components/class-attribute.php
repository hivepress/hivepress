<?php
/**
 * Attribute component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Attribute component class.
 *
 * @class Attribute
 */
final class Attribute {

	/**
	 * Array of models.
	 *
	 * @var array
	 */
	private $models = [ 'listing' ];

	/**
	 * Array of attributes.
	 *
	 * @var array
	 */
	private $attributes = [];

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Register attributes.
		add_action( 'wp_loaded', [ $this, 'register_attributes' ] );

		if ( is_admin() ) {

			// Disable quick edit.
			add_filter( 'post_row_actions', [ $this, 'disable_quick_edit' ], 10, 2 );

			// Remove meta boxes.
			add_action( 'admin_notices', [ $this, 'remove_meta_boxes' ] );
		}
	}

	// todo.
	public function register_attributes() {

		// Get attributes.
		$attributes = get_posts(
			[
				'post_type'      => hp\prefix(
					array_map(
						function( $model ) {
							return $model . '_attribute';
						},
						$this->models
					)
				),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			]
		);

		foreach ( $attributes as $attribute ) {
			$attribute_model = hp\unprefix( preg_replace( '/_attribute$/', '', $attribute->post_type ) );

			$attribute_args = [
				'editable'   => (bool) $attribute->hp_editable,
				'searchable' => (bool) $attribute->hp_searchable,
				'filterable' => (bool) $attribute->hp_filterable,
				'sortable'   => (bool) $attribute->hp_sortable,
			];

			// Get categories.
			$category_ids = wp_get_post_terms( $attribute->ID, hp\prefix( $attribute_model . '_category' ), [ 'fields' => 'ids' ] );

			foreach ( $category_ids as $category_id ) {
				$category_ids = array_merge( $category_ids, get_term_children( $category_id, hp\prefix( $attribute_model . '_category' ) ) );
			}

			$attribute_args['categories'] = array_unique( $category_ids );

			// Get edit field.
			$field_args = [
				'label' => $attribute->post_title,
				'order' => 100 + absint( $attribute->menu_order ),
			];

			// todo.
			$attribute_args['edit_field'] = array_merge(
				$field_args,
				[
					'type'    => sanitize_key( $attribute->hp_edit_field_type ),
					// todo.
					'options' => [],
				]
			);

			// Get search field.
			$attribute_args['search_field'] = array_merge(
				$field_args,
				[
					'type' => sanitize_key( $attribute->hp_search_field_type ),
				]
			);

			// Get attribute name.
			$attribute_name = substr( hp\sanitize_key( urldecode( $attribute->post_name ) ), 0, 64 - strlen( hp\prefix( '' ) ) );

			if ( isset( $attribute_args['edit_field']['options'] ) ) {
				$attribute_name = substr( $attribute_name, 0, 32 - strlen( hp\prefix( $attribute_model ) ) );
			}

			// Register taxonomy.
			if ( isset( $attribute_args['edit_field']['options'] ) ) {
				register_taxonomy(
					hp\prefix( $attribute_model . '_' . $attribute_name ),
					hp\prefix( $attribute_model ),
					[
						'label'        => 'todo',
						'hierarchical' => true,
						'public'       => false,
						'show_ui'      => true,
						'show_in_menu' => false,
						'rewrite'      => false,
					]
				);
			}

			$this->attributes[ $attribute_name ] = $attribute_args;
		}

		foreach ( $this->models as $model ) {
			add_filter( 'hivepress/meta_boxes/' . $model . '_attributes', [ $this, 'add_edit_fields' ] );

			add_filter( 'hivepress/forms/' . $model . '_update', [ $this, 'add_edit_fields' ] );
			add_filter( 'hivepress/forms/' . $model . '_search', [ $this, 'add_search_fields' ] );
			add_filter( 'hivepress/forms/' . $model . '_filter', [ $this, 'add_search_fields' ] );
			add_filter( 'hivepress/forms/' . $model . '_sort', [ $this, 'add_sort_options' ] );

			add_filter( 'hivepress/meta_boxes/' . $model . '_attribute_edit', [ $this, 'add_todo_fields' ] );
			add_filter( 'hivepress/meta_boxes/' . $model . '_attribute_search', [ $this, 'add_todo2_fields' ] );
		}
	}

	public function add_todo_fields( $meta_box ) {
		$meta_box['fields']['todo'] = [
			'label' => 'todo',
			'type'  => 'text',
			'order' => 100,
		];

		return $meta_box;
	}

	public function add_todo2_fields( $meta_box ) {
		$meta_box['fields']['todo'] = [
			'label' => 'todo',
			'type'  => 'text',
			'order' => 100,
		];

		return $meta_box;
	}

	/**
	 * Adds edit fields.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function add_edit_fields( $form ) {

		// Filter attributes.
		$category_ids = wp_get_post_terms( 'todo', hp\prefix( 'listing_category' ), [ 'fields' => 'ids' ] );

		$attributes = array_filter(
			$this->attributes,
			function( $attribute ) use ( $category_ids ) {
				return empty( $attribute['categories'] ) || count( array_intersect( $category_ids, $attribute['categories'] ) ) > 0;
			}
		);

		// Add fields.
		foreach ( $this->attributes as $attribute_name => $attribute ) {
			if ( ! isset( $form['fields'][ $attribute_name ] ) && ( ( is_admin() && 'listing_attributes' === $form['name'] && ! isset( $attribute['edit_field']['options'] ) ) || ( $attribute['editable'] && in_array( $form['name'], [ 'listing_submit', 'listing_update' ], true ) ) ) ) {
				$form['fields'][ $attribute_name ] = $attribute['edit_field'];
			}
		}

		return $form;
	}

	/**
	 * Adds search fields.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function add_search_fields( $form ) {

		// Filter attributes.
		$category_id = $this->get_category_id();

		$attributes = array_filter(
			$this->attributes,
			function( $attribute ) use ( $category_id ) {
				return empty( $attribute['categories'] ) || in_array( $category_id, $attribute['categories'], true );
			}
		);

		// Add fields.
		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( ! isset( $form['fields'][ $attribute_name ] ) && ( ( $attribute['searchable'] && 'listing_search' === $form['name'] ) || ( $attribute['filterable'] && 'listing_filter' === $form['name'] ) ) ) {
				$form['fields'][ $attribute_name ] = $attribute['search_field'];
			}
		}

		return $form;
	}

	/**
	 * Adds sort options.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function add_sort_options( $form ) {

		// Add defaults.
		if ( is_search() ) {
			$form['fields']['sort']['options']['relevance'] = esc_html__( 'Relevance', 'hivepress' );
		} else {
			$form['fields']['sort']['options']['date'] = esc_html__( 'Date', 'hivepress' );
		}

		// Filter attributes.
		$category_id = $this->get_category_id();

		$attributes = array_filter(
			$this->attributes,
			function( $attribute ) use ( $category_id ) {
				return empty( $attribute['categories'] ) || in_array( $category_id, $attribute['categories'], true );
			}
		);

		// Add options.
		foreach ( $this->attributes as $attribute_name => $attribute ) {
			if ( ! isset( $form['fields']['sort']['options'][ $attribute_name ] ) && $attribute['sortable'] ) {
				$form['fields']['sort']['options'][ $attribute_name ] = 'todo';
			}
		}

		return $form;
	}

	/**
	 * Gets current category ID.
	 *
	 * @return int
	 */
	private function get_category_id() {
		$category_id = hp\get_array_value( $_GET, 'category' );

		if ( is_tax( hp\prefix( 'listing_category' ) ) ) {
			$category_id = get_queried_object_id();
		}

		return absint( $category_id );
	}

	/**
	 * Disables quick edit.
	 *
	 * @param array   $actions Post actions.
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	public function disable_quick_edit( $actions, $post ) {
		if ( in_array( $post->post_type, hp\prefix( $this->models ), true ) ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		return $actions;
	}

	/**
	 * Removes meta boxes.
	 */
	public function remove_meta_boxes() {
		global $pagenow, $post;

		if ( 'post.php' === $pagenow && in_array( $post->post_type, hp\prefix( $this->models ), true ) ) {
			$category_ids = wp_get_post_terms( $post->ID, $post->post_type . '_category', [ 'fields' => 'ids' ] );

			// todo below.
			foreach ( $this->attributes as $attribute_name => $attribute ) {
				if ( isset( $attribute['edit_field']['options'] ) && ! empty( $attribute['categories'] ) && count( array_intersect( $category_ids, $attribute['categories'] ) ) === 0 ) {
					remove_meta_box( hp\prefix( 'listing_' . $attribute_name ) . 'div', hp\prefix( 'listing' ), 'side' );
				}
			}
		}
	}
}
