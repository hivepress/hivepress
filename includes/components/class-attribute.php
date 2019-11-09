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

		// Import attribute.
		add_filter( 'wxr_importer.pre_process.term', [ $this, 'import_attribute' ] );

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

			// Add sort options.
			add_filter( 'hivepress/v1/forms/' . $model . '_sort', [ $this, 'add_sort_options' ] );

			// Add category options.
			add_filter( 'hivepress/v1/forms/' . $model . '_filter', [ $this, 'add_category_options' ] );

			// Set category value.
			add_filter( 'hivepress/v1/forms/' . $model . '_filter', [ $this, 'set_category_value' ] );
			add_filter( 'hivepress/v1/forms/' . $model . '_sort', [ $this, 'set_category_value' ] );

			// Set range values.
			add_filter( 'hivepress/v1/forms/' . $model . '_filter', [ $this, 'set_range_values' ] );
		}

		if ( is_admin() ) {

			// Disable quick edit.
			add_filter( 'post_row_actions', [ $this, 'disable_quick_edit' ], 10, 2 );

			// Remove term boxes.
			add_action( 'admin_notices', [ $this, 'remove_term_boxes' ] );
		} else {

			// Set search query.
			add_action( 'pre_get_posts', [ $this, 'set_search_query' ] );
		}
	}

	/**
	 * Registers attributes.
	 */
	public function register_attributes() {
		foreach ( $this->models as $model ) {

			// Set query arguments.
			$query_args = [
				'post_type'      => hp\prefix( $model . '_attribute' ),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			];

			// Get cached attributes.
			$attributes = hivepress()->cache->get_cache( array_merge( $query_args, [ 'fields' => 'args' ] ), 'post/' . $model . '_attribute' );

			if ( is_null( $attributes ) ) {
				$attributes = [];

				// Get attribute posts.
				$attribute_posts = get_posts( $query_args );

				foreach ( $attribute_posts as $attribute_post ) {

					// Set defaults.
					$attribute_args = [
						'label'          => $attribute_post->post_title,
						'display_areas'  => (array) $attribute_post->hp_display_areas,
						'display_format' => $attribute_post->hp_display_format,
						'editable'       => (bool) $attribute_post->hp_editable,
						'searchable'     => (bool) $attribute_post->hp_searchable,
						'filterable'     => (bool) $attribute_post->hp_filterable,
						'sortable'       => (bool) $attribute_post->hp_sortable,
						'categories'     => [],
					];

					// Get categories.
					$category_ids = wp_get_post_terms( $attribute_post->ID, hp\prefix( $model . '_category' ), [ 'fields' => 'ids' ] );

					foreach ( $category_ids as $category_id ) {
						$category_ids = array_merge( $category_ids, get_term_children( $category_id, hp\prefix( $model . '_category' ) ) );
					}

					$attribute_args['categories'] = array_unique( $category_ids );

					// Get fields.
					$field_contexts = [ 'edit', 'search' ];

					foreach ( $field_contexts as $field_context ) {

						// Set defaults.
						$field_args = [
							'label' => $attribute_args['label'],
							'type'  => 'text',
							'order' => 100 + absint( $attribute_post->menu_order ),
						];

						// Get field type.
						$field_type = sanitize_key( get_post_meta( $attribute_post->ID, hp\prefix( $field_context . '_field_type' ), true ) );

						if ( ! empty( $field_type ) ) {

							// Get field class.
							$field_class = '\HivePress\Fields\\' . $field_type;

							// Get field settings.
							if ( class_exists( $field_class ) ) {
								$field_args['type'] = $field_type;

								foreach ( $field_class::get_settings() as $field_name => $field ) {
									$field->set_value( get_post_meta( $attribute_post->ID, hp\prefix( $field_context . '_field_' . $field_name ), true ) );
									$field_args[ $field_name ] = $field->get_value();
								}
							}
						}

						// Add field.
						$attribute_args[ $field_context . '_field' ] = $field_args;
					}

					// Get attribute name.
					$attribute_name = substr( hp\sanitize_key( urldecode( $attribute_post->post_name ) ), 0, 32 - strlen( hp\prefix( '' ) ) );

					if ( array_key_exists( 'options', $attribute_args['edit_field'] ) ) {
						$attribute_name = substr( $attribute_name, 0, 32 - strlen( hp\prefix( $model ) . '_' ) );

						// Set field options.
						foreach ( $field_contexts as $field_context ) {
							$attribute_args[ $field_context . '_field' ]['options']  = 'terms';
							$attribute_args[ $field_context . '_field' ]['taxonomy'] = hp\prefix( $model . '_' . $attribute_name );
						}
					}

					// Add attribute.
					$attributes[ $attribute_name ] = $attribute_args;
				}

				// Cache attributes.
				if ( count( $attributes ) <= 100 ) {
					hivepress()->cache->set_cache( array_merge( $query_args, [ 'fields' => 'args' ] ), 'post/' . $model . '_attribute', $attributes, DAY_IN_SECONDS );
				}
			}

			// Register taxonomies.
			foreach ( $attributes as $attribute_name => $attribute_args ) {
				if ( array_key_exists( 'options', $attribute_args['edit_field'] ) ) {
					register_taxonomy(
						hp\prefix( $model . '_' . $attribute_name ),
						hp\prefix( $model ),
						[
							'label'        => $attribute_args['label'],
							'hierarchical' => true,
							'public'       => false,
							'show_ui'      => true,
							'show_in_menu' => false,
							'rewrite'      => false,
						]
					);
				}
			}

			/**
			 * Filters model attributes.
			 *
			 * @filter /models/{$name}/attributes
			 * @description Filters model attributes.
			 * @param string $name Model name.
			 * @param array $atts Model attributes.
			 */
			$attributes = apply_filters( 'hivepress/v1/models/' . $model . '/attributes', $attributes );

			// Set attributes.
			$this->attributes[ $model ] = array_map(
				function( $attribute_args ) {
					return hp\merge_arrays(
						[
							'label'          => '',
							'display_areas'  => [],
							'display_format' => '',
							'editable'       => false,
							'searchable'     => false,
							'filterable'     => false,
							'sortable'       => false,
							'categories'     => [],
						],
						$attribute_args
					);
				},
				$attributes
			);
		}
	}

	/**
	 * Imports attribute.
	 *
	 * @param array $term Term object.
	 * @return array
	 */
	public function import_attribute( $term ) {
		if ( strpos( $term['taxonomy'], 'hp_' ) === 0 && ! taxonomy_exists( $term['taxonomy'] ) ) {
			register_taxonomy( $term['taxonomy'], hp\prefix( $this->models ) );
		}

		return $term;
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
					if ( 'edit' === $field_context || ! in_array( $field_name, [ 'required', 'options' ], true ) ) {
						$field_args = $field->get_args();

						if ( 'required' !== $field_name ) {
							$field_args['order'] = hp\get_array_value( $field_args, 'order', 10 ) + 100;
						}

						// Add field.
						$meta_box['fields'][ $field_context . '_field_' . $field_name ] = $field_args;
					}
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

		// Add fields.
		foreach ( $this->attributes[ $model['name'] ] as $attribute_name => $attribute ) {
			if ( ! isset( $model['fields'][ $attribute_name ] ) ) {
				$model['fields'][ $attribute_name ] = array_merge(
					$attribute['edit_field'],
					[
						'required'       => false,
						'display_areas'  => $attribute['display_areas'],
						'display_format' => $attribute['display_format'],
					]
				);

				if ( array_key_exists( 'options', $attribute['edit_field'] ) ) {
					$model['relations'][ $model['name'] . '_' . $attribute_name ] = $attribute_name;
				}
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

		// Get instance ID.
		$instance_id = get_query_var( hp\prefix( $model . '_id' ) ) ? absint( get_query_var( hp\prefix( $model . '_id' ) ) ) : get_the_ID();

		// Filter attributes.
		$category_ids = wp_get_post_terms( $instance_id, hp\prefix( $model . '_category' ), [ 'fields' => 'ids' ] );

		$attributes = array_filter(
			$this->attributes[ $model ],
			function( $attribute ) use ( $category_ids ) {
				return empty( $attribute['categories'] ) || count( array_intersect( $category_ids, $attribute['categories'] ) ) > 0;
			}
		);

		// Add fields.
		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( ! isset( $form['fields'][ $attribute_name ] ) && ( ( ! array_key_exists( 'options', $attribute['edit_field'] ) && $model . '_attributes' === $form['name'] ) || ( $attribute['editable'] && in_array( $form['name'], [ $model . '_submit', $model . '_update' ], true ) ) ) ) {
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
			$this->attributes[ $model ],
			function( $attribute ) use ( $category_id ) {
				return empty( $attribute['categories'] ) || in_array( $category_id, $attribute['categories'], true );
			}
		);

		// Add fields.
		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( ! isset( $form['fields'][ $attribute_name ] ) ) {
				if ( ( $attribute['searchable'] && $model . '_search' === $form['name'] ) || ( $attribute['filterable'] && $model . '_filter' === $form['name'] ) ) {

					// Add field.
					$form['fields'][ $attribute_name ] = $attribute['search_field'];
				} elseif ( ( $attribute['searchable'] || $attribute['filterable'] ) && in_array( $form['name'], [ $model . '_filter', $model . '_sort' ], true ) ) {

					// Get field class.
					$field_class = '\HivePress\Fields\\' . $attribute['search_field']['type'];

					if ( class_exists( $field_class ) ) {

						// Create field.
						$field = new $field_class( $attribute['search_field'] );

						$field->set_value( hp\get_array_value( $_GET, $attribute_name ) );

						if ( $field->validate() ) {
							$field_args  = array_merge( $attribute['search_field'], [ 'type' => 'hidden' ] );
							$field_value = $field->get_value();

							// Add field.
							if ( is_array( $field_value ) ) {
								foreach ( $field_value as $option_name => $option_value ) {
									$form['fields'][ $attribute_name . '[' . $option_name . ']' ] = array_merge( $field_args, [ 'default' => $option_value ] );
								}
							} else {
								$form['fields'][ $attribute_name ] = $field_args;
							}
						}
					}
				}
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
			$form['fields']['sort']['options'][''] = esc_html__( 'Relevance', 'hivepress' );
		} else {
			$form['fields']['sort']['options'][''] = esc_html__( 'Date', 'hivepress' );
		}

		// Get model.
		$model = explode( '_', $form['name'] );
		$model = reset( $model );

		// Filter attributes.
		$category_id = $this->get_category_id( $model );

		$attributes = array_filter(
			$this->attributes[ $model ],
			function( $attribute ) use ( $category_id ) {
				return empty( $attribute['categories'] ) || in_array( $category_id, $attribute['categories'], true );
			}
		);

		// Add options.
		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( ! isset( $form['fields']['sort']['options'][ $attribute_name ] ) && $attribute['sortable'] ) {
				$form['fields']['sort']['options'][ $attribute_name . '__asc' ]  = sprintf( '%s &uarr;', $attribute['search_field']['label'] );
				$form['fields']['sort']['options'][ $attribute_name . '__desc' ] = sprintf( '%s &darr;', $attribute['search_field']['label'] );
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

		// Get category ID.
		$category_id = $this->get_category_id( $model );

		// Set query arguments.
		$query_args = [
			'parent'         => $category_id,
			'include_parent' => true,
			'fields'         => 'ids',
		];

		// Get cached IDs.
		$category_ids = hivepress()->cache->get_cache( $query_args, 'term/' . $model . '_category' );

		if ( is_null( $category_ids ) ) {
			$category_ids = [];

			// Get category IDs.
			if ( 0 === $category_id ) {

				// Get top-level categories.
				$category_ids = get_terms( hp\prefix( $model . '_category' ), $query_args );
			} else {

				// Get parent categories.
				$category_ids = array_merge( [ $category_id ], get_ancestors( $category_id, hp\prefix( $model . '_category' ), 'taxonomy' ) );

				// Get child categories.
				$category_ids = array_merge( $category_ids, get_terms( hp\prefix( $model . '_category' ), $query_args ) );
			}

			// Cache IDs.
			if ( count( $category_ids ) <= 1000 ) {
				hivepress()->cache->set_cache( $query_args, 'term/' . $model . '_category', $category_ids, DAY_IN_SECONDS );
			}
		}

		// Get categories.
		$categories = get_terms(
			[
				'taxonomy'   => hp\prefix( $model . '_category' ),
				'include'    => array_merge( [ 0 ], $category_ids ),
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
	 * Sets category value.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function set_category_value( $form ) {

		// Get model.
		$model = explode( '_', $form['name'] );
		$model = reset( $model );

		// Set value.
		$form['fields']['category']['value'] = $this->get_category_id( $model );

		return $form;
	}

	/**
	 * Sets range values.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function set_range_values( $form ) {

		// Get model.
		$model = explode( '_', $form['name'] );
		$model = reset( $model );

		// Filter fields.
		foreach ( $form['fields'] as $field_name => $field_args ) {
			if ( 'number_range' === $field_args['type'] ) {

				// Set query arguments.
				$query_args = [
					'post_type'   => hp\prefix( $model ),
					'post_status' => 'publish',
					'meta_key'    => hp\prefix( $field_name ),
					'orderby'     => 'meta_value_num',
				];

				// Get cached range.
				$range = hivepress()->cache->get_cache( array_merge( $query_args, [ 'cache_type' => 'number_range' ] ), 'post/' . $model );

				if ( is_null( $range ) ) {

					// Get range.
					$range = [
						floatval( get_post_meta( hp\get_post_id( array_merge( $query_args, [ 'order' => 'ASC' ] ) ), hp\prefix( $field_name ), true ) ),
						floatval( get_post_meta( hp\get_post_id( array_merge( $query_args, [ 'order' => 'DESC' ] ) ), hp\prefix( $field_name ), true ) ),
					];

					// Cache range.
					hivepress()->cache->set_cache( array_merge( $query_args, [ 'cache_type' => 'number_range' ] ), 'post/' . $model, $range, DAY_IN_SECONDS );
				}

				// Set range values.
				if ( count( array_filter( $range ) ) > 0 && reset( $range ) !== end( $range ) ) {
					$form['fields'][ $field_name ]['min_value'] = reset( $range );
					$form['fields'][ $field_name ]['max_value'] = end( $range );
				}
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
	 * Removes term boxes.
	 */
	public function remove_term_boxes() {
		global $pagenow, $post;

		if ( in_array( $pagenow, [ 'post.php', 'post-new.php' ], true ) && in_array( $post->post_type, hp\prefix( $this->models ), true ) ) {

			// Get model.
			$model = hp\unprefix( $post->post_type );

			// Filter attributes.
			$category_ids = wp_get_post_terms( $post->ID, $post->post_type . '_category', [ 'fields' => 'ids' ] );

			$attributes = array_filter(
				$this->attributes[ $model ],
				function( $attribute ) use ( $category_ids ) {
					return ! empty( $attribute['categories'] ) && count( array_intersect( $category_ids, $attribute['categories'] ) ) === 0;
				}
			);

			// Remove meta boxes.
			foreach ( array_keys( $attributes ) as $attribute_name ) {
				remove_meta_box( $post->post_type . '_' . $attribute_name . 'div', $post->post_type, 'side' );
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
		$model = null;

		foreach ( $this->models as $current_model ) {
			$page_id = absint( get_option( 'hp_page_' . $current_model . 's' ) );

			if ( ( 0 !== $page_id && get_queried_object_id() !== 0 && is_page( $page_id ) ) || is_post_type_archive( hp\prefix( $current_model ) ) || is_tax( hp\prefix( $current_model . '_category' ) ) ) {
				$model = $current_model;

				break;
			}
		}

		if ( is_null( $model ) ) {
			return;
		}

		// Filter attributes.
		$attributes = array_filter(
			$this->attributes[ $model ],
			function( $attribute ) {
				return $attribute['searchable'] || $attribute['filterable'] || $attribute['sortable'];
			}
		);

		// Paginate results.
		$query->set( 'posts_per_page', absint( get_option( hp\prefix( $model . 's_per_page' ) ) ) );

		// Sort results.
		$form_class = '\HivePress\Forms\\' . $model . '_sort';

		if ( class_exists( $form_class ) ) {

			// Create form.
			$form = new $form_class();

			$form->set_values( $_GET );

			if ( $form->validate() ) {

				// Get sort order.
				$sort_param = $form->get_value( 'sort' );
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
		}

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

		// Filter results.
		if ( $query->is_search ) {

			// Set attributes.
			foreach ( $attributes as $attribute_name => $attribute ) {
				if ( $attribute['searchable'] || $attribute['filterable'] ) {
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
			}
		}

		// Set meta and taxonomy queries.
		$query->set( 'meta_query', $meta_query );
		$query->set( 'tax_query', $tax_query );

		// Get featured IDs.
		$query_args = [
			'post_type'      => hp\prefix( $model ),
			'post_status'    => 'publish',
			'meta_key'       => 'hp_featured',
			'meta_value'     => '1',
			'posts_per_page' => absint( get_option( 'hp_' . $model . 's_featured_per_page' ) ),
			'orderby'        => 'rand',
			'fields'         => 'ids',
		];

		if ( ! is_page() ) {
			$query_args = array_merge( $query->query_vars, $query_args );
		}

		$featured_ids = get_posts( $query_args );

		if ( ! empty( $featured_ids ) ) {

			// Exclude featured IDs.
			$query->set( 'post__not_in', $featured_ids );

			// Set featured IDs.
			$query->set( 'hp_featured_ids', $featured_ids );
		}
	}
}
