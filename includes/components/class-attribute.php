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
final class Attribute extends Component {

	/**
	 * Array of models.
	 *
	 * @var array
	 */
	protected $models = [];

	/**
	 * Array of attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'models' => [
					'listing',
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps component properties.
	 */
	protected function boot() {

		// Register attributes.
		add_action( 'init', [ $this, 'register_attributes' ], 10000 );

		// Import attribute.
		add_filter( 'wxr_importer.pre_process.term', [ $this, 'import_attribute' ] );

		foreach ( $this->models as $model ) {

			// Add field settings.
			add_filter( 'hivepress/v1/meta_boxes/' . $model . '_attribute_edit', [ $this, 'add_field_settings' ] );
			add_filter( 'hivepress/v1/meta_boxes/' . $model . '_attribute_search', [ $this, 'add_field_settings' ] );

			// Add admin fields.
			add_filter( 'hivepress/v1/meta_boxes/' . $model . '_attributes', [ $this, 'add_admin_fields' ] );

			// Add model fields.
			add_filter( 'hivepress/v1/models/' . $model . '/fields', [ $this, 'add_model_fields' ], 10, 2 );

			// Add edit fields.
			add_filter( 'hivepress/v1/forms/' . $model . '_update', [ $this, 'add_edit_fields' ], 10, 2 );

			// Add search fields.
			add_filter( 'hivepress/v1/forms/' . $model . '_search', [ $this, 'add_search_fields' ], 10, 2 );
			add_filter( 'hivepress/v1/forms/' . $model . '_filter', [ $this, 'add_search_fields' ], 10, 2 );
			add_filter( 'hivepress/v1/forms/' . $model . '_sort', [ $this, 'add_search_fields' ], 10, 2 );

			// Add sort options.
			add_filter( 'hivepress/v1/forms/' . $model . '_sort', [ $this, 'add_sort_options' ], 10, 2 );

			// Add category options.
			add_filter( 'hivepress/v1/forms/' . $model . '_filter', [ $this, 'add_category_options' ], 10, 2 );

			// Set category value.
			add_filter( 'hivepress/v1/forms/' . $model . '_filter', [ $this, 'set_category_value' ], 10, 2 );
			add_filter( 'hivepress/v1/forms/' . $model . '_sort', [ $this, 'set_category_value' ], 10, 2 );

			// Set range values.
			add_filter( 'hivepress/v1/forms/' . $model . '_search', [ $this, 'set_range_values' ], 10, 2 );
			add_filter( 'hivepress/v1/forms/' . $model . '_filter', [ $this, 'set_range_values' ], 10, 2 );
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

			// Get cache group.
			$cache_group = hivepress()->model->get_cache_group( 'post', hp\prefix( $model . '_attribute' ) );

			// Get cached attributes.
			$attributes = hivepress()->cache->get_cache( array_merge( $query_args, [ 'format' => 'attributes' ] ), $cache_group );

			if ( is_null( $attributes ) ) {
				$attributes = [];

				// Get attribute objects.
				$attribute_objects = get_posts( $query_args );

				foreach ( $attribute_objects as $attribute_object ) {

					// Set defaults.
					$attribute_args = [
						'label'          => $attribute_object->post_title,
						'display_areas'  => (array) $attribute_object->hp_display_areas,
						'display_format' => (string) $attribute_object->hp_display_format,
						'editable'       => (bool) $attribute_object->hp_editable,
						'moderated'      => (bool) $attribute_object->hp_moderated,
						'searchable'     => (bool) $attribute_object->hp_searchable,
						'filterable'     => (bool) $attribute_object->hp_filterable,
						'sortable'       => (bool) $attribute_object->hp_sortable,
						'categories'     => [],
						'edit_field'     => [],
						'search_field'   => [],
					];

					// Get categories.
					$category_ids = wp_get_post_terms( $attribute_object->ID, hp\prefix( $model . '_category' ), [ 'fields' => 'ids' ] );

					foreach ( $category_ids as $category_id ) {
						$category_ids = array_merge( $category_ids, get_term_children( $category_id, hp\prefix( $model . '_category' ) ) );
					}

					$attribute_args['categories'] = array_unique( $category_ids );

					// Get fields.
					$field_contexts = [ 'edit', 'search' ];

					foreach ( $field_contexts as $field_context ) {

						// Set defaults.
						$field_args = [
							'label'  => $attribute_args['label'],
							'type'   => 'text',
							'_order' => 100 + absint( $attribute_object->menu_order ),
						];

						// Get field type.
						$field_type = sanitize_key( get_post_meta( $attribute_object->ID, hp\prefix( $field_context . '_field_type' ), true ) );

						if ( $field_type ) {

							// Get field settings.
							$field_settings = hp\call_class_method( '\HivePress\Fields\\' . $field_type, 'get_meta', [ 'settings' ] );

							if ( $field_settings ) {

								// Set field type.
								$field_args['type'] = $field_type;

								// Set field settings.
								foreach ( $field_settings as $settings_field_name => $settings_field ) {

									// Set field value.
									$settings_field->set_value( get_post_meta( $attribute_object->ID, hp\prefix( $field_context . '_field_' . $settings_field_name ), true ) );

									// Get field value.
									if ( $settings_field->validate() ) {
										$field_args[ $settings_field_name ] = $settings_field->get_value();
									}
								}
							}
						}

						// Add field.
						$attribute_args[ $field_context . '_field' ] = $field_args;
					}

					// Get attribute name.
					$attribute_name = $this->get_attribute_name( $attribute_object->post_name );

					if ( array_key_exists( 'options', $attribute_args['edit_field'] ) ) {
						$attribute_name = $this->get_attribute_name( $attribute_object->post_name, $model );

						// Set field options.
						foreach ( $field_contexts as $field_context ) {
							$attribute_args[ $field_context . '_field' ]['options']     = 'terms';
							$attribute_args[ $field_context . '_field' ]['option_args'] = [ 'taxonomy' => hp\prefix( $model . '_' . $attribute_name ) ];
						}
					}

					// Add attribute.
					$attributes[ $attribute_name ] = $attribute_args;
				}

				// Cache attributes.
				if ( count( $attributes ) <= 100 ) {
					hivepress()->cache->set_cache( array_merge( $query_args, [ 'format' => 'attributes' ] ), $cache_group, $attributes );
				}
			}

			// Register taxonomies.
			foreach ( $attributes as $attribute_name => $attribute_args ) {
				if ( isset( $attribute_args['edit_field']['options'] ) ) {
					register_taxonomy(
						hp\prefix( $model . '_' . $attribute_name ),
						hp\prefix( $model ),
						[
							'hierarchical' => true,
							'public'       => false,
							'show_ui'      => true,
							'show_in_menu' => false,
							'rewrite'      => false,

							'labels'       => [
								'name'          => $attribute_args['label'],
								'singular_name' => $attribute_args['label'],
								'add_new_item'  => esc_html__( 'Add Option', 'hivepress' ),
								'edit_item'     => esc_html__( 'Edit Option', 'hivepress' ),
								'update_item'   => esc_html__( 'Update Option', 'hivepress' ),
								'parent_item'   => esc_html__( 'Parent Option', 'hivepress' ),
								'search_items'  => esc_html__( 'Search Options', 'hivepress' ),
								'not_found'     => esc_html__( 'No options found.', 'hivepress' ),
							],
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
			 * @param array $attributes Model attributes.
			 */
			$attributes = apply_filters( 'hivepress/v1/models/' . $model . '/attributes', $attributes );

			// Set attributes.
			$this->attributes[ $model ] = array_map(
				function( $args ) {
					return array_merge(
						[
							'label'          => '',
							'display_areas'  => [],
							'display_format' => '',
							'protected'      => false,
							'editable'       => false,
							'moderated'      => false,
							'searchable'     => false,
							'filterable'     => false,
							'sortable'       => false,
							'categories'     => [],
							'edit_field'     => [],
							'search_field'   => [],
						],
						$args
					);
				},
				$attributes
			);
		}
	}

	/**
	 * Imports attribute.
	 *
	 * @param array $term Term arguments.
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

		// Get model.
		$model = $meta_box['model'];

		// Get field context.
		$field_context = end( ( explode( '_', $meta_box['name'] ) ) );

		// Get field type.
		$field_type = sanitize_key( get_post_meta( get_the_ID(), hp\prefix( $field_context . '_field_type' ), true ) );

		if ( $field_type ) {

			// Get field settings.
			$field_settings = hp\call_class_method( '\HivePress\Fields\\' . $field_type, 'get_meta', [ 'settings' ] );

			// Add field settings.
			if ( $field_settings ) {
				foreach ( $field_settings as $field_name => $field ) {
					if ( 'edit' === $field_context || ! in_array( $field_name, [ 'required', 'options' ], true ) ) {

						// Get field arguments.
						$field_args = $field->get_args();

						// Set field arguments.
						if ( 'options' === $field_name ) {
							$field_args = array_merge(
								$field_args,
								[
									'label'      => esc_html__( 'Edit Options', 'hivepress' ),
									'type'       => 'button',

									'attributes' => [
										'data-component' => 'link',
										'data-url'       => esc_url(
											admin_url(
												'edit-tags.php?' . http_build_query(
													[
														'taxonomy' => hp\prefix( $model . '_' . $this->get_attribute_name( get_post_field( 'post_name' ), $model ) ),
														'post_type' => hp\prefix( $model ),
													]
												)
											)
										),
									],
								]
							);
						}

						if ( 'required' !== $field_name ) {
							$field_args['_order'] = hp\get_array_value( $field_args, '_order', 10 ) + 100;
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
	 * Adds admin fields.
	 *
	 * @param array $meta_box Meta box arguments.
	 * @return array
	 */
	public function add_admin_fields( $meta_box ) {

		// Get model.
		$model = $meta_box['model'];

		// Get category IDs.
		$category_ids = wp_get_post_terms( get_the_ID(), hp\prefix( $model . '_category' ), [ 'fields' => 'ids' ] );

		// Add fields.
		foreach ( $this->get_attributes( $model, $category_ids ) as $attribute_name => $attribute ) {
			if ( ! $attribute['protected'] && ! isset( $meta_box['fields'][ $attribute_name ] ) && ! isset( $attribute['edit_field']['options'] ) ) {
				$meta_box['fields'][ $attribute_name ] = $attribute['edit_field'];
			}
		}

		return $meta_box;
	}

	/**
	 * Adds model fields.
	 *
	 * @param array  $fields Model fields.
	 * @param object $object Model object.
	 * @return array
	 */
	public function add_model_fields( $fields, $object ) {

		// Get model.
		$model = $object::_get_meta( 'name' );

		// Get category IDs.
		$category_ids = hivepress()->cache->get_post_cache( $object->get_id(), [ 'fields' => 'ids' ], 'models/' . $model . '_category' );

		if ( is_null( $category_ids ) ) {
			$category_ids = wp_get_post_terms( $object->get_id(), hp\prefix( $model . '_category' ), [ 'fields' => 'ids' ] );

			if ( is_array( $category_ids ) && count( $category_ids ) <= 100 ) {
				hivepress()->cache->set_post_cache( $object->get_id(), [ 'fields' => 'ids' ], 'models/' . $model . '_category', $category_ids );
			}
		}

		// Get attributes.
		$attributes = $this->get_attributes( $model, $category_ids );

		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( $attribute['editable'] && ! isset( $fields[ $attribute_name ] ) ) {

				// Get field arguments.
				$field_args = array_merge(
					$attribute['edit_field'],
					[
						'display_template' => $attribute['display_format'],
						'_display_areas'   => $attribute['display_areas'],
					]
				);

				if ( isset( $field_args['options'] ) ) {
					$field_args = array_merge(
						$field_args,
						[
							'_model'    => 'listing_category',
							'_alias'    => hp\prefix( $model . '_' . $attribute_name ),
							'_relation' => 'many_to_many',
						]
					);
				} else {
					$field_args['_external'] = true;
				}

				// Add field.
				$fields[ $attribute_name ] = $field_args;
			}
		}

		return $fields;
	}

	/**
	 * Adds edit fields.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function add_edit_fields( $form_args, $form ) {

		// Get model.
		$model = $form::get_meta( 'model' );

		// Get category IDs.
		$category_ids = $form->get_model()->get_categories__id();

		// Get attributes.
		$attributes = $this->get_attributes( $model, $category_ids );

		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( $attribute['editable'] && ! isset( $form_args['fields'][ $attribute_name ] ) ) {

				// Get field arguments.
				$field_args = $attribute['edit_field'];

				if ( $attribute['moderated'] && $model . '_update' === $form::get_meta( 'name' ) ) {
					$field_args = hp\merge_arrays(
						$field_args,
						[
							'statuses' => [ 'moderated' => esc_html_x( 'requires review', 'field', 'hivepress' ) ],
						]
					);
				}

				// Add field.
				$form_args['fields'][ $attribute_name ] = $field_args;
			}
		}

		return $form_args;
	}

	/**
	 * Adds search fields.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function add_search_fields( $form_args, $form ) {

		// Get form context.
		$form_context = end( ( explode( '_', $form::get_meta( 'name' ) ) ) );

		// Get model.
		$model = $form::get_meta( 'model' );

		// Get category ID.
		$category_id = $this->get_category_id( $model );

		// Get attributes.
		$attributes = $this->get_attributes( $model, $category_id );

		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( ( ( $attribute['searchable'] || $attribute['filterable'] ) && 'sort' === $form_context ) || ( $attribute['searchable'] && 'search' === $form_context ) || ( $attribute['filterable'] && 'filter' === $form_context ) ) {

				// Get field arguments.
				$field_args = hp\merge_arrays(
					$attribute['search_field'],
					[
						'statuses' => [ 'optional' => null ],
					]
				);

				if ( ( ! $attribute['filterable'] && 'filter' === $form_context ) || 'sort' === $form_context ) {
					$field_args['display_type'] = 'hidden';
				}

				// Add field.
				$form_args['fields'][ $attribute_name ] = $field_args;
			}
		}

		return $form_args;
	}

	/**
	 * Adds sort options.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function add_sort_options( $form_args, $form ) {

		// Get model.
		$model = $form::get_meta( 'model' );

		// Get category ID.
		$category_id = $this->get_category_id( $model );

		// Get attributes.
		$attributes = $this->get_attributes( $model, $category_id );

		// Add default option.
		$options = [];

		if ( is_search() ) {
			$options[''] = esc_html__( 'Relevance', 'hivepress' );
		} else {
			$options[''] = esc_html__( 'Date', 'hivepress' );
		}

		// Add attribute options.
		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( $attribute['sortable'] ) {

				// Get sort order.
				$order = hp\call_class_method( '\HivePress\Fields\\' . $attribute['edit_field']['type'], 'get_meta', [ 'sortable' ] );

				if ( $order ) {

					// Get option label.
					$label = $attribute['edit_field']['label'];

					// Add option.
					if ( is_bool( $order ) ) {
						$options[ $attribute_name . '__asc' ]  = sprintf( '%s &uarr;', $label );
						$options[ $attribute_name . '__desc' ] = sprintf( '%s &darr;', $label );
					} else {
						$options[ $attribute_name . '__' . strtolower( $order ) ] = $label;
					}
				}
			}
		}

		// Set options.
		$form_args['fields']['_sort']['options'] = array_merge( $form_args['fields']['_sort']['options'], $options );

		return $form_args;
	}

	/**
	 * Adds category options.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function add_category_options( $form_args, $form ) {

		// Get model.
		$model = $form::get_meta( 'model' );

		// Get category ID.
		$category_id = $this->get_category_id( $model );

		// Set query arguments.
		$query_args = [
			'taxonomy'   => hp\prefix( $model . '_category' ),
			'parent'     => $category_id,
			'fields'     => 'ids',
			'hide_empty' => false,
		];

		// Get cached IDs.
		$category_ids = hivepress()->cache->get_cache( array_merge( $query_args, [ 'include_tree' => true ] ), 'models/' . $model . '_category' );

		if ( is_null( $category_ids ) ) {
			$category_ids = get_terms( $query_args );

			if ( ! empty( $category_id ) ) {
				$category_ids = array_merge( $category_ids, [ $category_id ], get_ancestors( $category_id, hp\prefix( $model . '_category' ), 'taxonomy' ) );
			}

			// Cache IDs.
			if ( count( $category_ids ) <= 1000 ) {
				hivepress()->cache->set_cache( array_merge( $query_args, [ 'include_tree' => true ] ), 'models/' . $model . '_category', $category_ids );
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
		$form_args['fields']['_category']['options'] = $options;

		return $form_args;
	}

	/**
	 * Sets category value.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function set_category_value( $form_args, $form ) {

		// Get model.
		$model = $form::get_meta( 'model' );

		// Set value.
		$form_args['fields']['_category']['default'] = $this->get_category_id( $model );

		return $form_args;
	}

	/**
	 * Sets range values.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function set_range_values( $form_args, $form ) {

		// Get model.
		$model = $form::get_meta( 'model' );

		// Filter fields.
		foreach ( $form_args['fields'] as $field_name => $field_args ) {
			if ( 'number_range' === $field_args['type'] ) {

				// Set query arguments.
				$query_args = [
					'post_type'      => hp\prefix( $model ),
					'post_status'    => 'publish',
					'meta_key'       => hp\prefix( $field_name ),
					'orderby'        => 'meta_value_num',
					'posts_per_page' => 1,
					'fields'         => 'ids',
				];

				// Get cached range.
				$range = hivepress()->cache->get_cache(
					array_merge(
						$query_args,
						[
							'fields' => 'meta_values',
							'format' => 'range',
						]
					),
					'models/' . $model
				);

				if ( is_null( $range ) ) {

					// Get range.
					$range = [
						floatval( get_post_meta( reset( ( get_posts( array_merge( $query_args, [ 'order' => 'ASC' ] ) ) ) ), hp\prefix( $field_name ), true ) ),
						floatval( get_post_meta( reset( ( get_posts( array_merge( $query_args, [ 'order' => 'DESC' ] ) ) ) ), hp\prefix( $field_name ), true ) ),
					];

					// Cache range.
					hivepress()->cache->set_cache(
						array_merge(
							$query_args,
							[
								'fields' => 'meta_values',
								'format' => 'range',
							]
						),
						'models/' . $model,
						$range
					);
				}

				// Set range values.
				if ( reset( $range ) !== end( $range ) ) {
					$form_args['fields'][ $field_name ]['min_value'] = reset( $range );
					$form_args['fields'][ $field_name ]['max_value'] = end( $range );
				}
			}
		}

		return $form_args;
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
		global $pagenow;

		if ( in_array( $pagenow, [ 'post.php', 'post-new.php' ], true ) && in_array( get_post_type(), hp\prefix( $this->models ), true ) ) {

			// Get model.
			$model = hp\unprefix( get_post_type() );

			// Get category IDs.
			$category_ids = wp_get_post_terms( get_the_ID(), hp\prefix( $model . '_category' ), [ 'fields' => 'ids' ] );

			// Get attributes.
			$attributes = $this->get_attributes( $model, $category_ids );

			foreach ( $this->attributes[ $model ] as $attribute_name => $attribute ) {
				if ( ! isset( $attributes[ $attribute_name ] ) && isset( $attribute['edit_field']['options'] ) ) {
					remove_meta_box( hp\prefix( $model . '_' . $attribute_name . 'div' ), hp\prefix( $model ), 'side' );
				}
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

		// Get model.
		$model = null;

		foreach ( $this->models as $model_name ) {
			if ( is_post_type_archive( hp\prefix( $model_name ) ) || is_tax( hp\prefix( $model_name . '_category' ) ) ) {
				$model = $model_name;

				break;
			}
		}

		if ( empty( $model ) ) {
			return;
		}

		// Get meta and taxonomy queries.
		$meta_query = (array) $query->get( 'meta_query' );
		$tax_query  = (array) $query->get( 'tax_query' );

		// Paginate results.
		$query->set( 'posts_per_page', absint( get_option( hp\prefix( $model . 's_per_page' ) ) ) );

		// Get category ID.
		$category_id = $this->get_category_id( $model );

		// Get attributes.
		$attributes = $this->get_attributes( $model, $category_id );

		// Sort results.
		$sort_form = hp\create_class_instance( '\HivePress\Forms\\' . $model . '_sort' );

		if ( $sort_form ) {

			// Set form values.
			$sort_form->set_values( $_GET );

			if ( $sort_form->validate() ) {

				// Get sort parameter.
				$sort_param = $sort_form->get_value( '_sort' );

				// Get sort order.
				$sort_order = 'ASC';

				if ( strpos( $sort_param, '__' ) ) {
					list($sort_param, $sort_order) = explode( '__', $sort_param );
				}

				if ( isset( $attributes[ $sort_param ] ) ) {

					// Get sort type.
					$sort_type = hp\call_class_method( '\HivePress\Fields\\' . $attributes[ $sort_param ]['edit_field']['type'], 'get_meta', [ 'type' ] );

					if ( $sort_type ) {

						// Add meta clause.
						$meta_query[ $sort_param . '__order' ] = [
							'key'     => hp\prefix( $sort_param ),
							'compare' => 'EXISTS',
							'type'    => $sort_type,
						];

						// Set sort parameter.
						$query->set( 'orderby', $sort_param . '__order' );

						// Set sort order.
						$query->set( 'order', strtoupper( $sort_order ) );
					}
				}
			}
		}

		// Filter results.
		if ( $query->is_search ) {

			// Set category ID.
			if ( $category_id ) {
				$tax_query[] = [
					[
						'taxonomy' => hp\prefix( $model . '_category' ),
						'terms'    => $category_id,
					],
				];
			}

			// Get attribute fields.
			$attribute_fields = [];

			foreach ( $attributes as $attribute_name => $attribute ) {
				if ( $attribute['searchable'] || $attribute['filterable'] ) {

					// Get field arguments.
					$field_args = $attribute['search_field'];

					if ( isset( $field_args['options'] ) ) {
						$field_args['name'] = hp\prefix( $model . '_' . $attribute_name );
					} else {
						$field_args['name'] = hp\prefix( $attribute_name );
					}

					// Create field.
					$field = hp\create_class_instance( '\HivePress\Fields\\' . $field_args['type'], [ $field_args ] );

					if ( $field && $field::get_meta( 'filterable' ) ) {

						// Set field value.
						$field->set_value( hp\get_array_value( $_GET, $attribute_name ) );

						// Add field.
						if ( $field->validate() ) {
							$attribute_fields[ $attribute_name ] = $field;
						}
					}
				}
			}

			// Set attribute filters.
			foreach ( $attribute_fields as $field ) {

				// Get parent field.
				$parent_field = hp\get_array_value( $fields, $field->get_arg( '_parent' ) );

				if ( $parent_field ) {

					// Set parent value.
					$field->set_parent_value( $parent_field->get_value() );

					// Update field filter.
					$field->update_filter();
				}

				// Get field filter.
				$field_filter = $field->get_filter();

				if ( $field_filter ) {
					if ( isset( $field_args['options'] ) ) {

						// Set taxonomy filter.
						$field_filter = array_combine(
							array_map(
								function( $key ) {
									return hp\get_array_value(
										[
											'name'  => 'taxonomy',
											'value' => 'terms',
										],
										$key,
										$key
									);
								},
								array_keys( $field_filter )
							),
							$field_filter
						);

						unset( $field_filter['type'] );

						$field_filter['include_children'] = false;

						// Add taxonomy clause.
						$tax_query[] = $field_filter;
					} else {

						// Set meta filter.
						$field_filter = array_combine(
							array_map(
								function( $key ) {
									return hp\get_array_value(
										[
											'name'     => 'key',
											'operator' => 'compare',
										],
										$key,
										$key
									);
								},
								array_keys( $field_filter )
							),
							$field_filter
						);

						// Add meta clause.
						$meta_query[] = $field_filter;
					}
				}
			}
		}

		// Get featured results.
		$featured_count = absint( get_option( hp\prefix( $model . 's_featured_per_page' ) ) );

		if ( $featured_count ) {

			// Get featured IDs.
			$featured_ids = get_posts(
				array_merge(
					$query->query_vars,
					[
						'post_status'    => 'publish',
						'meta_key'       => 'hp_featured',
						'meta_value'     => '1',
						'posts_per_page' => $featured_count,
						'paged'          => 1,
						'orderby'        => 'rand',
						'fields'         => 'ids',
						'meta_query'     => $meta_query,
						'tax_query'      => $tax_query,
					]
				)
			);

			if ( $featured_ids ) {

				// Exclude featured IDs.
				$query->set( 'post__not_in', $featured_ids );

				// Set request context.
				hivepress()->request->set_context( 'featured_ids', $featured_ids );
			}
		}

		// Set meta and taxonomy queries.
		$query->set( 'meta_query', $meta_query );
		$query->set( 'tax_query', $tax_query );
	}

	/**
	 * Gets current category ID.
	 *
	 * @param string $model Model name.
	 * @return mixed
	 */
	protected function get_category_id( $model ) {
		$category_id = null;

		if ( isset( $_GET['_category'] ) ) {
			$category_id = absint( $_GET['_category'] );
		} elseif ( is_tax( hp\prefix( $model . '_category' ) ) ) {
			$category_id = get_queried_object_id();
		}

		return $category_id;
	}

	/**
	 * Gets attributes.
	 *
	 * @param string $model Model name.
	 * @param array  $category_ids Category IDs.
	 * @return array
	 */
	protected function get_attributes( $model, $category_ids ) {
		return array_filter(
			$this->attributes[ $model ],
			function( $attribute ) use ( $category_ids ) {
				return empty( $attribute['categories'] ) || array_intersect( (array) $category_ids, $attribute['categories'] );
			}
		);
	}

	/**
	 * Gets attribute name.
	 *
	 * @param string $slug Attribute slug.
	 * @param string $prefix Attribute prefix.
	 * @return string
	 */
	protected function get_attribute_name( $slug, $prefix = '' ) {
		if ( $prefix ) {
			$prefix .= '_';
		}

		return substr( hp\sanitize_key( urldecode( $slug ) ), 0, 32 - strlen( hp\prefix( $prefix ) ) );
	}
}
