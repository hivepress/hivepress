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
		}
	}

	// todo.
	public function register_attributes() {

		// Get attribute posts.
		$attribute_posts = get_posts(
			[
				'post_type'      => hp\prefix( 'listing_attribute' ),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			]
		);

		foreach ( $attribute_posts as $attribute_post ) {
			$attribute_args = [
				'editable'   => (bool) $attribute_post->hp_editable,
				'searchable' => (bool) $attribute_post->hp_searchable,
				'filterable' => (bool) $attribute_post->hp_filterable,
				'sortable'   => (bool) $attribute_post->hp_sortable,
			];

			// Get categories.
			$category_ids = wp_get_post_terms( $attribute_post->ID, hp\prefix( 'listing_category' ), [ 'fields' => 'ids' ] );

			foreach ( $category_ids as $category_id ) {
				$category_ids = array_merge( $category_ids, get_term_children( $category_id, hp\prefix( 'listing_category' ) ) );
			}

			$attribute_args['categories'] = array_unique( $category_ids );

			// Get edit field.
			$field_args = [
				'label' => $attribute_post->post_title,
				'order' => 100 + absint( $attribute_post->menu_order ),
			];

			// todo.
			$attribute_args['edit_field'] = array_merge(
				$field_args,
				[
					'type'    => sanitize_key( $attribute_post->hp_edit_field_type ),
					// todo.
					'options' => [],
				]
			);

			// Get search field.
			$attribute_args['search_field'] = array_merge(
				$field_args,
				[
					'type' => sanitize_key( $attribute_post->hp_search_field_type ),
				]
			);

			// Get attribute name.
			$attribute_name = substr( hp\sanitize_key( urldecode( $attribute_post->post_name ) ), 0, 64 - strlen( hp\prefix( '' ) ) );

			if ( isset( $attribute_args['edit_field']['options'] ) ) {
				$attribute_name = substr( $attribute_name, 0, 32 - strlen( hp\prefix( 'listing' ) ) );
			}

			$this->attributes[ $attribute_name ] = $attribute_args;
		}

		// todo.
		foreach ( $this->attributes as $attribute_name => $attribute ) {
			if ( isset( $attribute['edit_field']['options'] ) ) {
				register_taxonomy(
					hp\prefix( 'listing_' . $attribute_name ),
					hp\prefix( 'listing' ),
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
		}

		// todo.
		add_filter( 'hivepress/meta_boxes/listing_attributes', [ $this, 'add_edit_fields' ] );
		add_filter( 'hivepress/forms/listing_update', [ $this, 'add_edit_fields' ] );
		add_filter( 'hivepress/forms/listing_search', [ $this, 'add_search_fields' ] );
		add_filter( 'hivepress/forms/listing_filter', [ $this, 'add_search_fields' ] );
		add_filter( 'hivepress/forms/listing_sort', [ $this, 'add_sort_options' ] );

		// todo.
		add_filter( 'hivepress/meta_boxes/listing_attribute_edit', [ $this, 'add_todo_fields' ] );
		add_filter( 'hivepress/meta_boxes/listing_attribute_search', [ $this, 'add_todo2_fields' ] );
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
			if ( ! isset( $form['fields'][ $attribute_name ] ) && ( ( is_admin() && 'listing_attributes' === $form['name'] ) || ( $attribute['editable'] && in_array( $form['name'], [ 'listing_submit', 'listing_update' ], true ) ) ) ) {
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
		if ( hp\prefix( 'listing' ) === $post->post_type ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		return $actions;
	}
}
