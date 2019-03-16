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

		foreach ( $this->models as $model ) {

			// Add field settings.
			add_filter( 'hivepress/v1/meta_boxes/' . $model . '_attribute_edit', [ $this, 'add_field_settings' ] );
			add_filter( 'hivepress/v1/meta_boxes/' . $model . '_attribute_search', [ $this, 'add_field_settings' ] );

			// Add model fields.
			add_filter( 'hivepress/v1/models/' . $model, [ $this, 'add_model_fields' ] );

			// Add edit fields.
			add_filter( 'hivepress/v1/meta_boxes/' . $model . '_attributes', [ $this, 'add_edit_fields' ] );
			add_filter( 'hivepress/v1/forms/' . $model . '_submit', [ $this, 'add_edit_fields' ] );
			add_filter( 'hivepress/v1/forms/' . $model . '_update', [ $this, 'add_edit_fields' ] );

			// Add search fields.
			add_filter( 'hivepress/v1/forms/' . $model . '_search', [ $this, 'add_search_fields' ] );
			add_filter( 'hivepress/v1/forms/' . $model . '_filter', [ $this, 'add_search_fields' ] );
			add_filter( 'hivepress/v1/forms/' . $model . '_sort', [ $this, 'add_search_fields' ] );

			// Add category options.
			add_filter( 'hivepress/v1/forms/' . $model . '_filter', [ $this, 'add_category_options' ] );

			// Add sort options.
			add_filter( 'hivepress/v1/forms/' . $model . '_sort', [ $this, 'add_sort_options' ] );
		}

		if ( is_admin() ) {

			// Disable quick edit.
			add_filter( 'post_row_actions', [ $this, 'disable_quick_edit' ], 10, 2 );

			// Remove taxonomy boxes.
			add_action( 'admin_notices', [ $this, 'remove_taxonomy_boxes' ] );
		} else {

			// Set search query.
			add_action( 'pre_get_posts', [ $this, 'set_search_query' ] );
		}
	}

	/**
	 * Registers attributes.
	 */
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

			// Get model.
			$attribute_model = hp\unprefix( preg_replace( '/_attribute$/', '', $attribute->post_type ) );

			// Set defaults.
			$attribute_args = [
				'model'          => $attribute_model,
				'display_areas'  => (array) $attribute->hp_display_areas,
				'display_format' => $attribute->hp_display_format,
				'editable'       => (bool) $attribute->hp_editable,
				'searchable'     => (bool) $attribute->hp_searchable,
				'filterable'     => (bool) $attribute->hp_filterable,
				'sortable'       => (bool) $attribute->hp_sortable,
			];

			// Get categories.
			$category_ids = wp_get_post_terms( $attribute->ID, hp\prefix( $attribute_model . '_category' ), [ 'fields' => 'ids' ] );

			foreach ( $category_ids as $category_id ) {
				$category_ids = array_merge( $category_ids, get_term_children( $category_id, hp\prefix( $attribute_model . '_category' ) ) );
			}

			$attribute_args['categories'] = array_unique( $category_ids );

			// Get fields.
			$field_contexts = [ 'edit', 'search' ];

			foreach ( $field_contexts as $field_context ) {

				// Set defaults.
				$field_args = [
					'label' => $attribute->post_title,
					'type'  => 'text',
					'order' => 100 + absint( $attribute->menu_order ),
				];

				// Get field type.
				$field_type = sanitize_key( get_post_meta( $attribute->ID, hp\prefix( $field_context . '_field_type' ), true ) );

				if ( '' !== $field_type ) {

					// Get field class.
					$field_class = '\HivePress\Fields\\' . $field_type;

					// Get field settings.
					if ( class_exists( $field_class ) ) {
						$field_args['type'] = $field_type;

						foreach ( $field_class::get_settings() as $field_name => $field ) {
							$field->set_value( get_post_meta( $attribute->ID, hp\prefix( $field_context . '_field_' . $field_name ), true ) );
							$field_args[ $field_name ] = $field->get_value();
						}
					}
				}

				// Add field.
				$attribute_args[ $field_context . '_field' ] = $field_args;
			}

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
						'label'        => $attribute->post_title,
						'hierarchical' => true,
						'public'       => false,
						'show_ui'      => true,
						'show_in_menu' => false,
						'rewrite'      => false,
					]
				);
			}

			// Add attribute.
			$this->attributes[ $attribute_name ] = $attribute_args;
		}
	}

	/**
	 * Adds field settings.
	 *
	 * @param array $meta_box Meta box arguments.
	 * @return array
	 */
	public function add_field_settings( $meta_box ) {

		// Get field context.
		$field_context = explode( '_', $meta_box['name'] );
		$field_context = end( $field_context );

		// Get field type.
		$field_type = sanitize_key( get_post_meta( get_the_ID(), hp\prefix( $field_context . '_field_type' ), true ) );

		if ( '' !== $field_type ) {

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $field_type;

			// Add field settings.
			if ( class_exists( $field_class ) ) {
				foreach ( $field_class::get_settings() as $field_name => $field ) {
					$meta_box['fields'][ $field_context . '_field_' . $field_name ] = array_merge( $field->get_args(), [ 'order' => 100 + hp\get_array_value( $field->get_args(), 'order', 10 ) ] );
				}
			}
		}

		return $meta_box;
	}

	/**
	 * Adds model fields.
	 *
	 * @param array $model Model arguments.
	 * @return array
	 */
	public function add_model_fields( $model ) {

		// Filter attributes.
		$attributes = array_filter(
			$this->attributes,
			function( $attribute ) use ( $model ) {
				return $attribute['model'] === $model['name'];
			}
		);

		// Add fields.
		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( ! isset( $model['fields'][ $attribute_name ] ) ) {
				$model['fields'][ $attribute_name ] = array_merge(
					$attribute['edit_field'],
					[
						'display_areas'  => $attribute['display_areas'],
						'display_format' => $attribute['display_format'],
					]
				);
			}
		}

		return $model;
	}

	/**
	 * Adds edit fields.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function add_edit_fields( $form ) {

		// Get model.
		$model = explode( '_', $form['name'] );
		$model = reset( $model );

		// Filter attributes.
		$category_ids = wp_get_post_terms( get_the_ID(), hp\prefix( $model . '_category' ), [ 'fields' => 'ids' ] );

		$attributes = array_filter(
			$this->attributes,
			function( $attribute ) use ( $model, $category_ids ) {
				return $attribute['model'] === $model && ( empty( $attribute['categories'] ) || count( array_intersect( $category_ids, $attribute['categories'] ) ) > 0 );
			}
		);

		// Add fields.
		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( ! isset( $form['fields'][ $attribute_name ] ) && ( ( ! isset( $attribute['edit_field']['options'] ) && $model . '_attributes' === $form['name'] ) || ( $attribute['editable'] && in_array( $form['name'], [ $model . '_submit', $model . '_update' ], true ) ) ) ) {
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

		// Get model.
		$model = explode( '_', $form['name'] );
		$model = reset( $model );

		// Filter attributes.
		$category_id = $this->get_category_id( $model );

		$attributes = array_filter(
			$this->attributes,
			function( $attribute ) use ( $model, $category_id ) {
				return $attribute['model'] === $model && ( empty( $attribute['categories'] ) || in_array( $category_id, $attribute['categories'], true ) );
			}
		);

		// Add fields.
		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( ! isset( $form['fields'][ $attribute_name ] ) ) {
				if ( ( $attribute['searchable'] && $model . '_search' === $form['name'] ) || ( $attribute['filterable'] && $model . '_filter' === $form['name'] ) ) {
					$form['fields'][ $attribute_name ] = $attribute['search_field'];
				} elseif ( ( $attribute['searchable'] || $attribute['filterable'] ) && in_array( $form['name'], [ $model . '_filter', $model . '_sort' ], true ) ) {
					$form['fields'][ $attribute_name ] = array_merge( $attribute['search_field'], [ 'type' => 'hidden' ] );
				}
			}
		}

		return $form;
	}

	/**
	 * Adds category options.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function add_category_options( $form ) {

		// Get model.
		$model = explode( '_', $form['name'] );
		$model = reset( $model );

		// Get category IDs.
		$category_ids = [];

		$category_id = $this->get_category_id( $model );

		if ( 0 !== $category_id ) {

			// Get parent categories.
			$category_ids = array_merge( [ $category_id ], get_ancestors( $category_id, hp\prefix( $model . '_category' ), 'taxonomy' ) );

			// Get child categories.
			$category_ids = array_merge(
				$category_ids,
				get_terms(
					hp\prefix( $model . '_category' ),
					[
						'parent' => $category_id,
						'fields' => 'ids',
					]
				)
			);
		} else {

			// Get top-level categories.
			$category_ids = get_terms(
				hp\prefix( $model . '_category' ),
				[
					'parent' => 0,
					'fields' => 'ids',
				]
			);
		}

		// Get categories.
		$categories = get_terms(
			[
				'taxonomy'   => hp\prefix( $model . '_category' ),
				'include'    => $category_ids,
				'hide_empty' => false,
				'meta_key'   => 'hp_order',
				'orderby'    => 'meta_value_num',
				'order'      => 'ASC',
			]
		);

		// Add options.
		$options = [
			0 => [
				'label'  => esc_html__( 'All Categories', 'hivepress' ),
				'parent' => null,
			],
		];

		foreach ( $categories as $category ) {
			$options[ $category->term_id ] = [
				'label'  => $category->name,
				'parent' => $category->parent,
			];
		}

		// Set options.
		$form['fields']['category']['options'] = $options;

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

		// Get model.
		$model = explode( '_', $form['name'] );
		$model = reset( $model );

		// Filter attributes.
		$category_id = $this->get_category_id( $model );

		$attributes = array_filter(
			$this->attributes,
			function( $attribute ) use ( $model, $category_id ) {
				return $attribute['model'] === $model && ( empty( $attribute['categories'] ) || in_array( $category_id, $attribute['categories'], true ) );
			}
		);

		// Add options.
		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( ! isset( $form['fields']['sort']['options'][ $attribute_name ] ) && $attribute['sortable'] ) {
				$form['fields']['sort']['options'][ $attribute_name ] = $attribute['search_field']['label'];
			}
		}

		return $form;
	}

	/**
	 * Gets current category ID.
	 *
	 * @param string $model Model name.
	 * @return int
	 */
	private function get_category_id( $model ) {
		$category_id = hp\get_array_value( $_GET, 'category' );

		if ( is_tax( hp\prefix( $model . '_category' ) ) ) {
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
	 * Removes taxonomy boxes.
	 */
	public function remove_taxonomy_boxes() {
		global $pagenow, $post;

		if ( 'post.php' === $pagenow && in_array( $post->post_type, hp\prefix( $this->models ), true ) ) {

			// Get model.
			$model = hp\unprefix( $post->post_type );

			// Filter attributes.
			$category_ids = wp_get_post_terms( $post->ID, $post->post_type . '_category', [ 'fields' => 'ids' ] );

			$attributes = array_filter(
				$this->attributes,
				function( $attribute ) use ( $model, $category_ids ) {
					return $attribute['model'] === $model && ! empty( $attribute['categories'] ) && count( array_intersect( $category_ids, $attribute['categories'] ) ) === 0;
				}
			);

			// Remove meta boxes.
			foreach ( array_keys( $attributes ) as $attribute_name ) {
				remove_meta_box( $post->post_type . $attribute_name . 'div', $post->post_type, 'side' );
			}
		}
	}

	/**
	 * Sets search query.
	 *
	 * @param WP_Query $query Search query.
	 */
	public function set_search_query( $query ) {

		// Check query.
		if ( ! $query->is_main_query() ) {
			return;
		}

		// Check model.
		$model = hp\unprefix( hp\get_array_value( $query->query_vars, 'post_type' ) );

		if ( ! in_array( $model, $this->models, true ) ) {
			return;
		}

		// Check page.
		$page_id = absint( get_option( hp\prefix( 'page_' . $model . 's' ) ) );

		if ( ( 0 === $page_id || ! is_page( $page_id ) ) && ! is_post_type_archive( hp\prefix( $model ) ) && ! is_tax( hp\prefix( $model . '_category' ) ) ) {
			return;
		}

		// Filter attributes.
		$attributes = array_filter(
			$this->attributes,
			function( $attribute ) use ( $model ) {
				return $attribute['model'] === $model && ( $attribute['searchable'] || $attribute['filterable'] );
			}
		);

		// Paginate results.
		$query->set( 'posts_per_page', absint( get_option( hp\prefix( $model . 's_per_page' ) ) ) );

		// Sort results.
		$sort_form = new \HivePress\Forms\Listing_Sort();

		$sort_form->set_values( $_GET );

		if ( $sort_form->validate() ) {

			// Get sort order.
			$sort_param = $sort_form->get_value( 'sort' );
			$sort_order = null;

			if ( strpos( $sort_param, '__' ) !== false ) {
				list($sort_param, $sort_order) = explode( '__', $sort_param );
			}

			// Set sort order.
			if ( isset( $attributes[ $sort_param ] ) ) {
				$query->set( 'meta_key', hp\prefix( $sort_param ) );

				if ( ! is_null( $sort_order ) ) {
					$query->set( 'orderby', 'meta_value_num' );
					$query->set( 'order', strtoupper( $sort_order ) );
				} else {
					$query->set( 'orderby', 'meta_value' );
				}
			} else {
				$query->set( 'orderby', $sort_param );
			}
		}

		// Filter results.
		if ( $query->is_search ) {

			// Get meta and taxonomy queries.
			$meta_query = (array) $query->get( 'meta_query' );
			$tax_query  = (array) $query->get( 'tax_query' );

			// Set category.
			$category_id = $this->get_category_id( $model );

			if ( 0 !== $category_id ) {
				$tax_query[] = [
					[
						'taxonomy' => hp\prefix( $model . '_category' ),
						'terms'    => $category_id,
					],
				];
			}

			// Set attributes.
			foreach ( $attributes as $attribute_name => $attribute ) {
				$field_args = $attribute['search_field'];

				// Get field class.
				$field_class = '\HivePress\Fields\\' . $field_args['type'];

				if ( class_exists( $field_class ) ) {

					// Create field.
					$field = new $field_class( $field_args );

					$field->set_value( hp\get_array_value( $_GET, $attribute_name ) );

					// Get attribute value.
					$attribute_value = $field->get_value();

					if ( $field->validate() && ! is_null( $attribute_value ) ) {
						if ( isset( $field_args['options'] ) ) {

							// Set taxonomy filter.
							$tax_filter = [
								'taxonomy' => hp\prefix( $model . '_' . $attribute_name ),
								'terms'    => $attribute_value,
							];

							if ( hp\get_array_value( $field_args, 'multiple' ) ) {
								$tax_filter['operator'] = 'AND';
							}

							$tax_query[] = $tax_filter;
						} else {

							// Set meta filter.
							$meta_filter = [
								'key'   => hp\prefix( $attribute_name ),
								'value' => $attribute_value,
							];

							if ( is_array( $attribute_value ) ) {
								$meta_filter['type']    = 'NUMERIC';
								$meta_filter['compare'] = 'BETWEEN';

								$min_value = reset( $attribute_value );
								$max_value = end( $attribute_value );

								if ( is_null( $min_value ) ) {
									$meta_filter['compare'] = '<=';
									$meta_filter['value']   = $max_value;
								} elseif ( is_null( $max_value ) ) {
									$meta_filter['compare'] = '>=';
									$meta_filter['value']   = $min_value;
								}
							} elseif ( is_float( $attribute_value ) ) {
								$meta_filter['type'] = 'NUMERIC';
							} else {
								$meta_filter['compare'] = 'LIKE';
							}

							$meta_query[] = $meta_filter;
						}
					}
				}
			}

			// Set meta and taxonomy queries.
			$query->set( 'meta_query', $meta_query );
			$query->set( 'tax_query', $tax_query );
		}
	}
}
