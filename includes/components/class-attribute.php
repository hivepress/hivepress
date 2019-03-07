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

		// todo.
		add_action( 'wp_loaded', [ $this, 'init_attributes' ] );
	}

	// todo.
	public function init_attributes() {

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
					'type' => 'text',
				]
			);

			// Get search field.
			$attribute_args['search_field'] = array_merge(
				$field_args,
				[
					'type' => 'text',
				]
			);

			$this->attributes[ hp\sanitize_key( urldecode( $attribute_post->post_name ) ) ] = $attribute_args;
		}

		// todo.
		add_filter( 'hivepress/meta_boxes/listing_attributes', [ $this, 'add_edit_fields' ] );
		add_filter( 'hivepress/forms/listing_update', [ $this, 'add_edit_fields' ] );
		add_filter( 'hivepress/forms/listing_search', [ $this, 'add_search_fields' ] );
		add_filter( 'hivepress/forms/listing_filter', [ $this, 'add_search_fields' ] );
		add_filter( 'hivepress/forms/listing_sort', [ $this, 'add_sort_options' ] );
	}

	public function add_sort_options( $form ) {
		foreach ( $this->attributes as $attribute_name => $attribute ) {
			if ( ! isset( $form['fields']['sort']['options'][ $attribute_name ] ) ) {
				$form['fields']['sort']['options'][ $attribute_name ] = 'todo';
			}
		}

		return $form;
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
}
