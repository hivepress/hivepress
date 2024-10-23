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
 * Handles model attributes.
 */
final class Attribute extends Component {

	/**
	 * Model parameters.
	 *
	 * @var array
	 */
	protected $models = [];

	/**
	 * Model attributes.
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
					'listing' => [],
					'vendor'  => [],
					'user'    => [],
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

		// Register models.
		add_action( 'hivepress/v1/setup', [ $this, 'register_models' ] );

		// Register post types.
		add_filter( 'hivepress/v1/post_types', [ $this, 'register_post_types' ], 1 );

		// Register taxonomies.
		add_filter( 'hivepress/v1/taxonomies', [ $this, 'register_taxonomies' ], 1 );

		// Register attributes.
		add_action( 'init', [ $this, 'register_attributes' ], 100 );

		// Import attribute.
		add_filter( 'wxr_importer.pre_process.term', [ $this, 'import_attribute' ] );

		// Add settings.
		add_filter( 'hivepress/v1/settings', [ $this, 'add_settings' ] );

		// Manage meta boxes.
		add_filter( 'hivepress/v1/meta_boxes', [ $this, 'add_meta_boxes' ], 1 );
		add_action( 'add_meta_boxes', [ $this, 'remove_meta_boxes' ], 100 );

		// Redirect archive page.
		add_action( 'template_redirect', [ $this, 'redirect_archive_page' ] );

		if ( ! is_admin() ) {

			// Set search query.
			add_action( 'pre_get_posts', [ $this, 'set_search_query' ] );

			// Disable Jetpack search.
			add_filter( 'jetpack_search_should_handle_query', [ $this, 'disable_jetpack_search' ], 10, 2 );
		}
	}

	/**
	 * Gets attribute name.
	 *
	 * @param string $slug Attribute slug.
	 * @param string $prefix Attribute prefix.
	 * @return string
	 */
	public function get_attribute_name( $slug, $prefix = '' ) {
		if ( $prefix ) {
			$prefix .= '_';
		}

		return substr( hp\sanitize_key( urldecode( $slug ) ), 0, 32 - strlen( hp\prefix( $prefix ) ) );
	}

	/**
	 * Gets attributes.
	 *
	 * @param string $model Model name.
	 * @param array  $category_ids Category IDs.
	 * @return array
	 */
	public function get_attributes( $model, $category_ids = null ) {
		$attributes = hp\get_array_value( $this->attributes, $model, [] );

		if ( ! is_null( $category_ids ) ) {
			$attributes = array_filter(
				$attributes,
				function( $attribute ) use ( $category_ids ) {
					return empty( $attribute['categories'] ) || array_intersect( (array) $category_ids, $attribute['categories'] );
				}
			);
		}

		return $attributes;
	}

	/**
	 * Gets attribute fields.
	 *
	 * @param string $model Model name.
	 * @param array  $values Attribute values.
	 * @return array
	 */
	protected function get_attribute_fields( $model, $values ) {
		$attribute_fields = [];

		// Get category ID.
		$category_id = isset( $values['_category'] ) ? absint( $values['_category'] ) : null;

		// Get attributes.
		$attributes = $this->get_attributes( $model, (array) $category_id );

		// Get fields.
		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( $attribute['searchable'] || $attribute['filterable'] ) {

				// Get field arguments.
				$field_args = $attribute['search_field'];

				if ( isset( $field_args['options'] ) && ! isset( $field_args['_external'] ) ) {
					$field_args['name'] = hp\prefix( $model . '_' . $attribute_name );
				} else {
					$field_args['name'] = hp\prefix( $attribute_name );
				}

				// Create field.
				$field = hp\create_class_instance( '\HivePress\Fields\\' . $field_args['type'], [ $field_args ] );

				if ( $field && $field::get_meta( 'filterable' ) ) {

					// Set field value.
					$field->set_value( hp\get_array_value( $values, $attribute_name ) );

					if ( $field->validate() ) {

						// Check range values.
						if ( 'number_range' === $field::get_meta( 'name' ) && ! array_diff( (array) $field->get_value(), $this->get_range_values( $model, $attribute_name ) ) ) {
							continue;
						}

						// Add field.
						$attribute_fields[ $attribute_name ] = $field;
					}
				}
			}
		}

		return $attribute_fields;
	}

	/**
	 * Gets model names.
	 *
	 * @param string $type Model type.
	 * @return array
	 */
	public function get_models( $type = null ) {
		$models = array_keys( $this->models );

		if ( 'post' === $type ) {
			$models = array_filter(
				$models,
				function( $model ) {
					return 'user' !== $model;
				}
			);
		}

		return $models;
	}

	/**
	 * Gets category model name.
	 *
	 * @param string $model Model name.
	 * @return string
	 */
	protected function get_category_model( $model ) {
		return ( isset( $this->models[ $model ]['category_model'] ) ? $this->models[ $model ]['category_model'] : $model ) . '_category';
	}

	/**
	 * Checks if category model required.
	 *
	 * @param string $model Model name.
	 * @return bool
	 */
	protected function requires_category_model( $model ) {
		$taxonomy = hp\prefix( $this->get_category_model( $model ) );

		return taxonomy_exists( $taxonomy ) && get_terms(
			[
				'taxonomy'   => $taxonomy,
				'number'     => 1,
				'fields'     => 'ids',
				'hide_empty' => false,
			]
		);
	}

	/**
	 * Gets category model IDs.
	 *
	 * @param string $model Model name.
	 * @param object $object Model object.
	 * @return array
	 */
	protected function get_category_ids( $model, $object = null ) {

		// Check model.
		if ( 'user' === $model ) {
			return;
		}

		// Check object.
		if ( ! $object ) {
			return;
		}

		if ( is_object( $object ) && ! $object->get_id() ) {

			// @todo fix for cases when category model is set.
			return $object->get_categories__id();
		}

		// Get object ID.
		$id = is_object( $object ) ? $object->get_id() : $object;

		if ( isset( $this->models[ $model ]['category_model'] ) ) {

			// @todo remove temporary solution, check model fields instead.
			$id = absint( get_post_field( 'post_parent', $id ) );
		}

		if ( ! $id ) {
			return;
		}

		// Get category IDs.
		$category_ids = wp_get_post_terms( $id, hp\prefix( $this->get_category_model( $model ) ), [ 'fields' => 'ids' ] );

		return $category_ids;
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
		} elseif ( is_tax( hp\prefix( $this->get_category_model( $model ) ) ) ) {
			$category_id = get_queried_object_id();
		}

		return $category_id;
	}

	/**
	 * Gets current term ID.
	 *
	 * @param string $model Model name.
	 * @return mixed
	 */
	protected function get_term_id( $model ) {
		$term_id = null;

		if ( is_tax() && strpos( get_queried_object()->taxonomy, hp\prefix( $model . '_' ) ) === 0 ) {
			$term_id = get_queried_object_id();
		}

		return $term_id;
	}

	/**
	 * Gets query arguments.
	 *
	 * @param string $model Model name.
	 * @param array  $values Attribute values.
	 * @return array
	 */
	public function get_query_args( $model, $values = [] ) {

		// Set default arguments.
		$query_args = [
			'meta_query' => [],
			'tax_query'  => [],
		];

		// Get attribute fields.
		if ( is_array( $model ) ) {

			// @todo replace temporary fix.
			$attribute_fields = $model;
		} else {
			$attribute_fields = $this->get_attribute_fields( $model, $values );
		}

		// Set attribute filters.
		foreach ( $attribute_fields as $attribute_name => $field ) {
			if ( $field->get_arg( '_parent' ) ) {

				// Get parent field.
				$parent_field = hp\get_array_value( $attribute_fields, $field->get_arg( '_parent' ) );

				if ( $parent_field ) {

					// Set parent value.
					$field->set_parent_value( $parent_field->get_value() );

					// Update field filter.
					$field->update_filter();
				}
			}

			// Get field filter.
			$field_filter = $field->get_filter();

			if ( $field_filter ) {
				if ( ! is_null( $field->get_arg( 'options' ) ) && ! $field->get_arg( '_external' ) ) {

					// Set taxonomy filter.
					$field_filter = array_combine(
						array_map(
							function( $param ) {
								return hp\get_array_value(
									[
										'name'  => 'taxonomy',
										'value' => 'terms',
									],
									$param,
									$param
								);
							},
							array_keys( $field_filter )
						),
						$field_filter
					);

					unset( $field_filter['type'] );

					// Add taxonomy clause.
					$query_args['tax_query'][ $attribute_name ] = $field_filter;
				} else {

					// Set meta filter.
					$field_filter = array_combine(
						array_map(
							function( $param ) {
								return hp\get_array_value(
									[
										'name'     => 'key',
										'operator' => 'compare',
									],
									$param,
									$param
								);
							},
							array_keys( $field_filter )
						),
						$field_filter
					);

					// Add meta clause.
					$query_args['meta_query'][ $attribute_name ] = $field_filter;
				}
			}
		}

		return $query_args;
	}

	/**
	 * Registers models.
	 */
	public function register_models() {

		/**
		 * Filters the attribute-enabled models. If you add a new model name to the filtered array, HivePress will register all the required callbacks for handling the model attributes (e.g. custom fields, search filters, sorting options).
		 *
		 * @hook hivepress/v1/components/attribute/models
		 * @param {array} $models Model names.
		 * @return {array} Model names.
		 */
		$this->models = apply_filters( 'hivepress/v1/components/attribute/models', $this->models );

		// Convert for compatibility.
		foreach ( $this->models as $index => $model ) {
			if ( is_string( $model ) ) {
				unset( $this->models[ $index ] );

				$this->models[ $model ] = [];
			}
		}

		foreach ( $this->get_models() as $model ) {

			// Set defaults.
			$this->models[ $model ]['searchable'] = true;

			// @todo check post type config instead.
			if ( ! in_array( $model, [ 'listing', 'vendor', 'request' ] ) ) {
				$this->models[ $model ]['searchable'] = false;
			}

			// Add block settings.
			add_filter( 'hivepress/v1/blocks/' . $model . 's/meta', [ $this, 'add_block_settings' ], 100 );

			// Add field settings.
			add_filter( 'hivepress/v1/meta_boxes/' . $model . '_attribute_edit', [ $this, 'add_field_settings' ], 100 );
			add_filter( 'hivepress/v1/meta_boxes/' . $model . '_attribute_search', [ $this, 'add_field_settings' ], 100 );
			add_filter( 'hivepress/v1/meta_boxes/' . $model . '_attribute_display', [ $this, 'add_field_settings' ], 100 );

			// Add model fields.
			add_filter( 'hivepress/v1/models/' . $model . '/fields', [ $this, 'add_model_fields' ], 100, 2 );

			// Add edit fields.
			add_filter( 'hivepress/v1/forms/' . $model . '_update', [ $this, 'add_edit_fields' ], 100, 2 );

			// Add admin fields.
			add_filter( 'hivepress/v1/meta_boxes/' . $model . '_attributes', [ $this, 'add_admin_fields' ], 100 );

			if ( 'user' === $model ) {

				// Add register fields.
				add_filter( 'hivepress/v1/forms/user_register', [ $this, 'add_register_fields' ], 100 );
			} else {

				// Update attribute.
				add_action( 'save_post_hp_' . $model . '_attribute', [ $this, 'update_attribute' ] );

				// Update model snippet.
				add_action( 'hivepress/v1/models/' . $model . '/create', [ $this, 'update_model_snippet' ], 100, 2 );
				add_action( 'hivepress/v1/models/' . $model . '/update', [ $this, 'update_model_snippet' ], 100, 2 );

				// Add submit fields.
				add_filter( 'hivepress/v1/forms/' . $model . '_submit', [ $this, 'add_submit_fields' ], 100, 2 );

				// Add search fields.
				add_filter( 'hivepress/v1/forms/' . $model . '_search', [ $this, 'add_search_fields' ], 100, 2 );
				add_filter( 'hivepress/v1/forms/' . $model . '_filter', [ $this, 'add_search_fields' ], 100, 2 );
				add_filter( 'hivepress/v1/forms/' . $model . '_sort', [ $this, 'add_search_fields' ], 100, 2 );

				// Add sort options.
				add_filter( 'hivepress/v1/forms/' . $model . '_sort', [ $this, 'add_sort_options' ], 100, 2 );

				// Add category options.
				add_filter( 'hivepress/v1/forms/' . $model . '_filter', [ $this, 'add_category_options' ], 100, 2 );

				// Set category value.
				add_filter( 'hivepress/v1/forms/' . $model . '_search', [ $this, 'set_category_value' ], 100, 2 );
				add_filter( 'hivepress/v1/forms/' . $model . '_filter', [ $this, 'set_category_value' ], 100, 2 );
				add_filter( 'hivepress/v1/forms/' . $model . '_sort', [ $this, 'set_category_value' ], 100, 2 );

				// Set range values.
				add_filter( 'hivepress/v1/forms/' . $model . '_filter', [ $this, 'set_range_values' ], 100, 2 );
			}
		}
	}

	/**
	 * Registers post types.
	 *
	 * @param array $post_types Post types.
	 * @return array
	 */
	public function register_post_types( $post_types ) {
		foreach ( $this->get_models() as $model ) {
			$post_types[ $model . '_attribute' ] = [
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'user' === $model ? 'users.php' : 'edit.php?post_type=' . hp\prefix( $model ),
				'supports'     => [ 'title', 'page-attributes' ],

				'labels'       => [
					'name'               => hivepress()->translator->get_string( 'attributes' ),
					'singular_name'      => hivepress()->translator->get_string( 'attribute' ),
					'add_new'            => hivepress()->translator->get_string( 'add_new_attribute' ),
					'add_new_item'       => hivepress()->translator->get_string( 'add_attribute' ),
					'edit_item'          => hivepress()->translator->get_string( 'edit_attribute' ),
					'new_item'           => hivepress()->translator->get_string( 'add_attribute' ),
					'all_items'          => hivepress()->translator->get_string( 'attributes' ),
					'search_items'       => hivepress()->translator->get_string( 'search_attributes' ),
					'not_found'          => hivepress()->translator->get_string( 'no_attributes_found' ),
					'not_found_in_trash' => hivepress()->translator->get_string( 'no_attributes_found' ),
				],
			];
		}

		return $post_types;
	}

	/**
	 * Registers taxonomies.
	 *
	 * @param array $taxonomies Taxonomies.
	 * @return array
	 */
	public function register_taxonomies( $taxonomies ) {
		foreach ( $this->get_models( 'post' ) as $model ) {
			$taxonomy = $this->get_category_model( $model );

			if ( isset( $taxonomies[ $taxonomy ] ) ) {
				$taxonomies[ $taxonomy ] = hp\merge_arrays(
					$taxonomies[ $taxonomy ],
					[
						'post_type'          => [ $model . '_attribute' ],
						'show_in_quick_edit' => false,
					]
				);
			}
		}

		return $taxonomies;
	}

	/**
	 * Registers attributes.
	 */
	public function register_attributes() {
		foreach ( $this->get_models() as $model ) {

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
						'id'             => $attribute_object->ID,
						'label'          => $attribute_object->post_title,
						'display_areas'  => array_filter( (array) $attribute_object->hp_display_areas ),
						'display_format' => (string) $attribute_object->hp_display_format,
						'public'         => (bool) $attribute_object->hp_public,
						'editable'       => (bool) $attribute_object->hp_editable,
						'synced'         => (bool) $attribute_object->hp_synced,
						'moderated'      => (bool) $attribute_object->hp_moderated,
						'indexable'      => (bool) $attribute_object->hp_indexable,
						'searchable'     => (bool) $attribute_object->hp_searchable,
						'filterable'     => (bool) $attribute_object->hp_filterable,
						'sortable'       => (bool) $attribute_object->hp_sortable,
						'categories'     => [],
						'edit_field'     => [],
						'search_field'   => [],
					];

					// Set icon.
					$icon = $attribute_object->hp_icon;

					if ( $icon ) {
						$icon = '<i class="hp-icon fas fa-fw fa-' . esc_attr( $icon ) . '"></i>';
					}

					$attribute_args['display_format'] = str_replace( '%icon%', $icon, $attribute_args['display_format'] );

					// Set categories.
					if ( taxonomy_exists( hp\prefix( $this->get_category_model( $model ) ) ) ) {
						$attribute_args['categories'] = wp_get_post_terms( $attribute_object->ID, hp\prefix( $this->get_category_model( $model ) ), [ 'fields' => 'ids' ] );
					}

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

								// @todo replace temporary fix.
								$field_settings['description'] = hp\create_class_instance(
									'\HivePress\Fields\Textarea',
									[
										[
											'max_length' => 2048,
											'html'       => true,
										],
									]
								);

								// Set field settings.
								// @todo remove array filtering.
								foreach ( array_filter( $field_settings ) as $settings_field_name => $settings_field ) {

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

					if ( array_key_exists( 'options', $attribute_args['edit_field'] ) && ! isset( $attribute_args['edit_field']['_external'] ) ) {
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
				if ( isset( $attribute_args['edit_field']['options'] ) && ! isset( $attribute_args['edit_field']['_external'] ) ) {
					$taxonomy_name = hp\prefix( $model . '_' . $attribute_name );
					$taxonomy_type = hp\prefix( $model );

					$taxonomy_args = [
						'hierarchical'       => true,
						'public'             => false,
						'show_ui'            => true,
						'meta_box_cb'        => false,
						'show_in_quick_edit' => false,
						'show_in_menu'       => false,
						'rewrite'            => false,

						'labels'             => [
							'name'          => $attribute_args['label'],
							'singular_name' => $attribute_args['label'],
							'add_new_item'  => esc_html__( 'Add Option', 'hivepress' ),
							'edit_item'     => esc_html__( 'Edit Option', 'hivepress' ),
							'update_item'   => esc_html__( 'Update Option', 'hivepress' ),
							'parent_item'   => esc_html__( 'Parent Option', 'hivepress' ),
							'search_items'  => esc_html__( 'Search Options', 'hivepress' ),
							'not_found'     => esc_html__( 'No options found.', 'hivepress' ),
						],
					];

					if ( 'user' === $model ) {
						$taxonomy_type = hp\prefix( 'vendor' );
					} elseif ( hp\get_array_value( $attribute_args, 'public' ) ) {
						$taxonomy_args['public'] = true;

						$taxonomy_args['rewrite'] = [
							'slug' => hp\sanitize_slug( $model . '_' . $attribute_name ),
						];
					}

					if ( ! taxonomy_exists( $taxonomy_name ) ) {
						register_taxonomy( $taxonomy_name, $taxonomy_type, $taxonomy_args );
					}
				}
			}

			/**
			 * Filters model attributes. By adding a new attribute to the filtered array, you can add a new field to the model forms and meta boxes, enable the search filter and a sorting option for it. The dynamic part of the hook refers to the model name (e.g. `listing`, `vendor`).
			 *
			 * @hook hivepress/v1/models/{model_name}/attributes
			 * @param {array} $attributes Attribute configurations.
			 * @return {array} Attribute configurations.
			 */
			$attributes = apply_filters( 'hivepress/v1/models/' . $model . '/attributes', $attributes );

			// Set categories.
			foreach ( $attributes as $attribute_name => $attribute_args ) {
				$taxonomy_name = hp\prefix( $this->get_category_model( $model ) );

				if ( ! taxonomy_exists( $taxonomy_name ) ) {
					continue;
				}

				$category_ids = hp\get_array_value( $attribute_args, 'categories' );

				if ( ! $category_ids ) {
					continue;
				}

				foreach ( $category_ids as $category_id ) {

					// @todo cache category IDs.
					$category_ids = array_merge( $category_ids, get_term_children( $category_id, $taxonomy_name ) );
				}

				$attributes[ $attribute_name ]['categories'] = array_unique( $category_ids );
			}

			// Set attributes.
			$this->attributes[ $model ] = array_map(
				function( $args ) {
					if ( ! isset( $args['label'] ) && isset( $args['edit_field']['label'] ) ) {
						$args['label'] = $args['edit_field']['label'];
					}

					return array_merge(
						[
							'id'             => null,
							'label'          => '',
							'display_areas'  => [],
							'display_format' => '%value%',
							'protected'      => false,
							'editable'       => false,
							'synced'         => false,
							'moderated'      => false,
							'indexable'      => false,
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
			register_taxonomy( $term['taxonomy'], hp\prefix( $this->get_models( 'post' ) ) );
		}

		return $term;
	}

	/**
	 * Updates attribute.
	 *
	 * @param int $attribute_id Attribute ID.
	 */
	public function update_attribute( $attribute_id ) {

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $attribute_id ) ) {
			return;
		}

		// Check action.
		if ( hp\get_array_value( $_POST, 'action' ) !== 'editpost' ) {
			return;
		}

		// Check autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check post ID.
		if ( get_the_ID() !== $attribute_id ) {
			return;
		}

		// Refresh permalinks.
		hivepress()->router->flush_rewrite_rules();
	}

	/**
	 * Adds block settings.
	 *
	 * @param array $meta Block meta.
	 * @return array
	 */
	public function add_block_settings( $meta ) {

		// Get attributes.
		$attributes = $this->get_attributes( substr( $meta['name'], 0, -1 ), [] );

		// Add settings.
		foreach ( $attributes as $attribute_name => $attribute_args ) {

			// @todo remove type check when supported.
			if ( ( $attribute_args['searchable'] || $attribute_args['filterable'] ) && ! isset( $meta['settings'][ $attribute_name ] ) && in_array( $attribute_args['search_field']['type'], [ 'text', 'number', 'select', 'checkbox' ] ) ) {
				$meta['settings'][ $attribute_name ] = $attribute_args['search_field'];
			}
		}

		return $meta;
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
		$field_context = hp\get_last_array_value( explode( '_', $meta_box['name'] ) );

		// Get field type.
		$field_type = sanitize_key( get_post_meta( get_the_ID(), hp\prefix( ( 'display' === $field_context ? 'edit' : $field_context ) . '_field_type' ), true ) );

		if ( $field_type ) {

			// Get field settings.
			$field_settings = hp\call_class_method( '\HivePress\Fields\\' . $field_type, 'get_meta', [ 'settings' ] );

			// Add field settings.
			if ( $field_settings ) {
				foreach ( $field_settings as $field_name => $field ) {
					if ( ( 'edit' === $field_context && 'search' !== $field->get_arg( '_context' ) ) || ( 'search' === $field_context && 'edit' !== $field->get_arg( '_context' ) ) ) {

						// Get field arguments.
						$field_args = $field->get_args();

						// Set field arguments.
						if ( 'options' === $field_name ) {
							if ( get_post_status() === 'publish' ) {
								$field_args = array_merge(
									$field_args,
									[
										'caption'      => esc_html__( 'Edit Options', 'hivepress' ),
										'type'         => 'button',
										'display_type' => 'button',

										'attributes'   => [
											'data-component' => 'link',
											'data-url' => esc_url(
												admin_url(
													'edit-tags.php?' . http_build_query(
														[
															'taxonomy' => hp\prefix( $model . '_' . $this->get_attribute_name( get_post_field( 'post_name' ), $model ) ),
															'post_type' => hp\prefix( 'user' === $model ? 'vendor' : $model ),
														]
													)
												)
											),
										],
									]
								);
							} else {
								$field_args = array_merge(
									$field_args,
									[
										'disabled'     => true,
										'display_type' => 'hidden',
									]
								);
							}
						}

						if ( 'required' !== $field_name ) {
							$field_args['_order'] = hp\get_array_value( $field_args, '_order', 10 ) + 100;
						}

						// Add field.
						$meta_box['fields'][ $field_context . '_field_' . $field_name ] = $field_args;
					}
				}

				// @todo replace temporary fix.
				if ( 'edit' === $field_context ) {
					$meta_box['fields'][ $field_context . '_field_description' ] = [
						'label'      => hivepress()->translator->get_string( 'description' ),
						'type'       => 'textarea',
						'max_length' => 2048,
						'html'       => true,
						'_order'     => 120,
					];
				} elseif ( 'search' === $field_context && in_array( $field_type, [ 'select', 'number', 'date', 'date_range' ], true ) ) {
					$meta_box['fields']['searchable'] = [
						'label'   => esc_html_x( 'Searchable', 'attribute', 'hivepress' ),
						'caption' => esc_html__( 'Display in the search form', 'hivepress' ),
						'type'    => 'checkbox',
						'_order'  => 5,
					];
				} elseif ( 'display' === $field_context && isset( $field_settings['options'] ) && 'user' !== $model ) {
					$meta_box['fields']['public'] = [
						'label'   => esc_html__( 'Pages', 'hivepress' ),
						'caption' => esc_html__( 'Create a page for each attribute option', 'hivepress' ),
						'type'    => 'checkbox',
						'_order'  => 5,
					];
				}
			}
		}

		if ( 'edit' === $field_context ) {

			// Get field name.
			$field_name = get_post_field( 'post_name' );

			if ( ! $field_name || preg_match( '/^[a-z]{1}[a-z0-9_-]*$/', $field_name ) ) {

				// Set field arguments.
				$field_args = [
					'label'       => esc_html__( 'Field Name', 'hivepress' ),
					'description' => esc_html__( 'Set the field name used for storing the attribute values.', 'hivepress' ) . ' ' . esc_html__( 'Use lowercase letters, numbers, and underscores only.', 'hivepress' ),
					'type'        => 'text',
					'pattern'     => '[a-z]{1}[a-z0-9_]*',
					'max_length'  => 64,
					'required'    => true,
					'_alias'      => 'post_name',
					'_order'      => 99,
				];

				if ( get_post_status() === 'publish' ) {
					$field_args['readonly'] = true;
				}

				// Add field.
				$meta_box['fields']['edit_field_name'] = $field_args;
			}

			// @todo replace temporary fix.
			if ( 'attachment_upload' === $field_type ) {
				unset( $meta_box['fields']['moderated'] );
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
		$category_ids = $this->get_category_ids( $model, get_the_ID() );

		// Add fields.
		foreach ( $this->get_attributes( $model, $category_ids ) as $attribute_name => $attribute ) {
			if ( ! $attribute['protected'] && ! isset( $meta_box['fields'][ $attribute_name ] ) ) {
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
		$category_ids = null;

		if ( 'user' !== $model ) {
			$category_ids = null;

			if ( $object->get_id() ) {
				$category_ids = hivepress()->cache->get_post_cache( $object->get_id(), [ 'fields' => 'ids' ], 'models/' . $this->get_category_model( $model ) );
			}

			if ( is_null( $category_ids ) ) {
				$category_ids = $this->get_category_ids( $model, $object );

				if ( $object->get_id() && is_array( $category_ids ) && count( $category_ids ) <= 100 ) {
					hivepress()->cache->set_post_cache( $object->get_id(), [ 'fields' => 'ids' ], 'models/' . $this->get_category_model( $model ), $category_ids );
				}
			}
		}

		// Get attributes.
		$attributes = $this->get_attributes( $model, $category_ids );

		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( ! isset( $fields[ $attribute_name ] ) ) {

				// Get field arguments.
				$field_args = array_merge(
					$attribute['edit_field'],
					[
						'display_template' => $attribute['display_format'],
						'_display_areas'   => $attribute['display_areas'],
					]
				);

				// Set field context.
				if ( $attribute['display_areas'] ) {
					$field_args['context'][ $model ] = $object;
				}

				// Set required flag.
				if ( ! $attribute['editable'] ) {
					$field_args['required'] = false;
				}

				// Set indexable flag.
				if ( $attribute['indexable'] ) {
					$field_args['_indexable'] = true;
				}

				// Set field relation.
				if ( ! isset( $field_args['options'] ) || 'user' === $model ) {
					$field_args['_external'] = true;
				} elseif ( ! isset( $field_args['_external'] ) ) {
					$field_args = array_merge(
						$field_args,
						[
							'_model'    => $this->get_category_model( $model ),
							'_alias'    => hp\prefix( $model . '_' . $attribute_name ),
							'_relation' => 'many_to_many',
						]
					);
				}

				// Add field.
				$fields[ $attribute_name ] = $field_args;
			}
		}

		// Add snippet field.
		if ( 'user' !== $model ) {
			$fields['snippet'] = [
				'type'       => 'textarea',
				'max_length' => 10240,
				'html'       => true,
				'_alias'     => 'post_excerpt',
			];
		}

		return $fields;
	}

	/**
	 * Updates model search snippet.
	 *
	 * @param int    $model_id Model ID.
	 * @param object $model Model object.
	 */
	public function update_model_snippet( $model_id, $model ) {

		// Remove action.
		remove_action( 'hivepress/v1/models/' . $model::_get_meta( 'name' ) . '/update', [ $this, 'update_model_snippet' ], 100 );

		// Get snippet.
		$snippet = '';

		foreach ( $model->_get_fields() as $field ) {
			if ( $field->get_arg( '_indexable' ) && ! is_null( $field->get_value() ) ) {
				if ( $field->get_label() ) {
					$snippet .= $field->get_label() . ': ';
				}

				$snippet .= $field->get_display_value() . '; ';
			}
		}

		if ( $model::_get_meta( 'name' ) === 'listing' && $model->get_vendor__id() ) {
			$snippet .= hivepress()->translator->get_string( 'vendor' ) . ': ' . $model->get_vendor__name() . '; ';
		}

		$snippet = ltrim( rtrim( $snippet, '; ' ) . '.', '.' );

		// Update snippet.
		if ( $model->get_snippet() !== $snippet ) {
			$model->set_snippet( $snippet )->save_snippet();
		}
	}

	/**
	 * Adds register fields.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function add_register_fields( $form ) {
		foreach ( $this->get_attributes( 'user' ) as $attribute_name => $attribute ) {

			// Get required flag.
			$required = hp\get_array_value( $attribute['edit_field'], 'required' );

			if ( $attribute['editable'] && $required && 'attachment_upload' !== $attribute['edit_field']['type'] && ! isset( $form['fields'][ $attribute_name ] ) ) {

				// Add field.
				$form['fields'][ $attribute_name ] = $attribute['edit_field'];
			}
		}

		return $form;
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
		$category_ids = $this->get_category_ids( $model, $form->get_model() );

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
							'statuses'   => [ 'moderated' => esc_html_x( 'requires review', 'field', 'hivepress' ) ],
							'_moderated' => true,
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
	 * Adds submit fields.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function add_submit_fields( $form_args, $form ) {
		if ( $this->requires_category_model( $form::get_meta( 'model' ) ) ) {
			$form_args['fields']['categories'] = [
				'multiple'   => false,
				'required'   => true,
				'_order'     => 5,

				'attributes' => [
					'data-multistep' => 'true',
					'data-render'    => hivepress()->router->get_url( 'form_resource', [ 'form_name' => $form::get_meta( 'name' ) ] ),
				],
			];
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
		$form_context = hp\get_last_array_value( explode( '_', $form::get_meta( 'name' ) ) );

		// Get model.
		$model = $form::get_meta( 'model' );

		// Get category ID.
		$category_id = $this->get_category_id( $model );

		if ( ! empty( $form_args['values']['_category'] ) ) {
			$category_id = absint( $form_args['values']['_category'] );
		}

		// Get attributes.
		$attributes = $this->get_attributes( $model, (array) $category_id );

		foreach ( $attributes as $attribute_name => $attribute ) {
			if ( ! isset( $form_args['fields'][ $attribute_name ] ) && ( ( ( $attribute['searchable'] || $attribute['filterable'] ) && in_array( $form_context, [ 'sort', 'filter' ], true ) ) || ( $attribute['searchable'] && 'search' === $form_context ) ) ) {

				// Get field arguments.
				$field_args = hp\merge_arrays(
					$attribute['search_field'],
					[
						'statuses' => [ 'optional' => null ],
					]
				);

				if ( 'sort' === $form_context || ( ! $attribute['filterable'] && 'filter' === $form_context ) ) {
					$field_args['display_type'] = 'hidden';
				}

				// Add field.
				$form_args['fields'][ $attribute_name ] = $field_args;
			}
		}

		// Set default fields.
		$default_fields = (array) get_option( hp\prefix( $model . '_search_fields' ), [ 'keyword' ] );

		if ( ! in_array( 'keyword', $default_fields, true ) && 'search' === $form_context ) {
			$form_args['fields']['s']['display_type'] = 'hidden';
		}

		if ( in_array( 'category', $default_fields, true ) ) {
			if ( 'search' === $form_context ) {
				$form_args['fields']['_category']['display_type'] = 'select';
				$form_args['fields']['_category']['_order']       = 30;
			} elseif ( 'filter' === $form_context ) {
				$form_args['fields']['_category']['display_type'] = 'hidden';
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
		$attributes = $this->get_attributes( $model, (array) $category_id );

		// Add attribute options.
		$options = [];

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

		// Set default option.
		$default = get_option( hp\prefix( $model . '_default_order' ) );

		if ( $default ) {
			$form_args['fields']['_sort']['default'] = $default;
		}

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

		// Check category option.
		if ( ! taxonomy_exists( hp\prefix( $this->get_category_model( $model ) ) ) || in_array( 'category', (array) get_option( hp\prefix( $model . '_search_fields' ) ), true ) ) {
			return $form_args;
		}

		// Get category ID.
		$category_id = $this->get_category_id( $model );

		// Set query arguments.
		$query_args = [
			'taxonomy'   => hp\prefix( $this->get_category_model( $model ) ),
			'parent'     => $category_id,
			'fields'     => 'ids',
			'hide_empty' => false,
		];

		// Get cached options.
		$options = hivepress()->cache->get_cache(
			array_merge(
				$query_args,
				[
					'fields' => 'names',
					'format' => 'tree',
				]
			),
			'models/' . $this->get_category_model( $model )
		);

		if ( is_null( $options ) ) {
			$options = [];

			// Get category IDs.
			$category_ids = get_terms( $query_args );

			if ( $category_id ) {
				$category_ids = array_merge( $category_ids, [ $category_id ], get_ancestors( $category_id, hp\prefix( $this->get_category_model( $model ) ), 'taxonomy' ) );
			}

			if ( $category_ids ) {

				// Set custom order.
				if ( get_terms(
					array_merge(
						$query_args,
						[
							'number'     => 1,
							'meta_query' => [
								[
									'key'     => 'hp_sort_order',
									'value'   => 0,
									'compare' => '>',
									'type'    => 'NUMERIC',
								],
							],
						]
					)
				) ) {

					// Get categories.
					$categories = get_terms(
						array_merge(
							$query_args,
							[
								'parent'   => '',
								'fields'   => 'all',
								'include'  => $category_ids,
								'meta_key' => 'hp_sort_order',
								'orderby'  => 'meta_value_num',
							]
						)
					);
				} else {

					// Get categories.
					$categories = get_terms(
						array_merge(
							$query_args,
							[
								'parent'  => '',
								'fields'  => 'all',
								'include' => $category_ids,
								'orderby' => 'name',
							]
						)
					);
				}

				// Add options.
				$options[0] = [
					'label'  => hivepress()->translator->get_string( 'all_categories' ),
					'parent' => null,
				];

				foreach ( $categories as $category ) {
					$options[ $category->term_id ] = [
						'label'  => $category->name,
						'parent' => $category->parent,
					];
				}
			}

			// Cache options.
			if ( count( $options ) <= 1000 ) {
				hivepress()->cache->set_cache(
					array_merge(
						$query_args,
						[
							'fields' => 'names',
							'format' => 'tree',
						]
					),
					'models/' . $this->get_category_model( $model ),
					$options
				);
			}
		}

		if ( $options ) {

			// Set options.
			$form_args['fields']['_category']['options'] = $options;
		} else {

			// Remove field.
			unset( $form_args['fields']['_category'] );
		}

		return $form_args;
	}

	/**
	 * Sets category field value.
	 *
	 * @param array  $form_args Form arguments.
	 * @param object $form Form object.
	 * @return array
	 */
	public function set_category_value( $form_args, $form ) {

		// Get model.
		$model = $form::get_meta( 'model' );

		// Set category ID.
		if ( isset( $form_args['fields']['_category'] ) ) {
			$form_args['fields']['_category']['default'] = 0;
		}

		// Get term ID.
		$term_id = $this->get_term_id( $model );

		if ( $term_id ) {

			// Get field name.
			$field_name = substr( get_queried_object()->taxonomy, strlen( hp\prefix( $model . '_' ) ) );

			if ( 'category' === $field_name ) {
				$field_name = '_' . $field_name;
			}

			// Set field value.
			if ( isset( $form_args['fields'][ $field_name ] ) ) {
				$form_args['fields'][ $field_name ]['default'] = $term_id;
			}
		}

		return $form_args;
	}

	/**
	 * Gets number range field values.
	 *
	 * @param string $model Model name.
	 * @param string $field Field name.
	 * @return array
	 */
	protected function get_range_values( $model, $field ) {

		// Set query arguments.
		$query_args = [
			'post_type'      => hp\prefix( $model ),
			'post_status'    => 'publish',
			'meta_key'       => hp\prefix( $field ),
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
				floor(
					floatval(
						get_post_meta(
							hp\get_first_array_value( get_posts( array_merge( $query_args, [ 'order' => 'ASC' ] ) ) ),
							hp\prefix( $field ),
							true
						)
					)
				),
				ceil(
					floatval(
						get_post_meta(
							hp\get_first_array_value( get_posts( array_merge( $query_args, [ 'order' => 'DESC' ] ) ) ),
							hp\prefix( $field ),
							true
						)
					)
				),
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

		return $range;
	}

	/**
	 * Sets number range field values.
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

				// Get range values.
				$range = $this->get_range_values( $model, $field_name );

				// Set range values.
				if ( hp\get_first_array_value( $range ) !== hp\get_last_array_value( $range ) ) {
					$form_args['fields'][ $field_name ]['min_value'] = hp\get_first_array_value( $range );
					$form_args['fields'][ $field_name ]['max_value'] = hp\get_last_array_value( $range );
				}
			}
		}

		return $form_args;
	}

	/**
	 * Adds settings.
	 *
	 * @param array $settings Settings configuration.
	 * @return array
	 */
	public function add_settings( $settings ) {
		foreach ( $this->models as $model_name => $model_args ) {

			// Check model.
			if ( ! $model_args['searchable'] ) {
				continue;
			}

			// Create sort form.
			$sort_form = hp\create_class_instance( '\HivePress\Forms\\' . $model_name . '_sort' );

			if ( ! $sort_form ) {
				continue;
			}

			// Get sort field.
			$sort_field = hp\get_array_value( $sort_form->get_fields(), '_sort' );

			if ( ! $sort_field ) {
				continue;
			}

			// Get sort options.
			$sort_options = $sort_field->get_arg( 'options' );

			if ( ! $sort_options ) {
				continue;
			}

			// Add field.
			if ( isset( $settings[ $model_name . 's' ]['sections']['search'] ) ) {
				$settings[ $model_name . 's' ]['sections']['search']['fields'][ $model_name . '_default_order' ] = [
					'label'   => esc_html__( 'Default Sorting', 'hivepress' ),
					'type'    => 'select',
					'options' => $sort_options,
					'_order'  => 20,
				];
			}
		}

		return $settings;
	}

	/**
	 * Adds meta boxes.
	 *
	 * @param array $meta_boxes Meta box arguments.
	 * @return array
	 */
	public function add_meta_boxes( $meta_boxes ) {

		// Set defaults.
		$meta_box_args = [
			'attributes'        => [
				'title'  => esc_html__( 'Attributes', 'hivepress' ),
				'fields' => [],
			],

			'attribute_edit'    => [
				'title'  => hivepress()->translator->get_string( 'editing' ),

				'fields' => [
					'editable'        => [
						'label'   => esc_html_x( 'Editable', 'attribute', 'hivepress' ),
						'caption' => esc_html__( 'Allow front-end editing', 'hivepress' ),
						'type'    => 'checkbox',
						'_order'  => 1,
					],

					'edit_field_type' => [
						'label'       => esc_html__( 'Field Type', 'hivepress' ),
						'type'        => 'select',
						'options'     => 'fields',
						'option_args' => [ 'editable' => true ],
						'required'    => true,
						'_order'      => 100,
					],
				],
			],

			'attribute_search'  => [
				'title'  => hivepress()->translator->get_string( 'search_noun' ),

				'fields' => [
					'filterable'        => [
						'label'   => esc_html_x( 'Filterable', 'attribute', 'hivepress' ),
						'caption' => esc_html__( 'Display in the filter form', 'hivepress' ),
						'type'    => 'checkbox',
						'_order'  => 10,
					],

					'indexable'         => [
						'label'   => esc_html_x( 'Indexable', 'attribute', 'hivepress' ),
						'caption' => esc_html__( 'Include in keyword search', 'hivepress' ),
						'type'    => 'checkbox',
						'_order'  => 15,
					],

					'sortable'          => [
						'label'   => esc_html_x( 'Sortable', 'attribute', 'hivepress' ),
						'caption' => esc_html__( 'Display as a sorting option', 'hivepress' ),
						'type'    => 'checkbox',
						'_order'  => 20,
					],

					'search_field_type' => [
						'label'       => esc_html__( 'Field Type', 'hivepress' ),
						'type'        => 'select',
						'options'     => 'fields',
						'option_args' => [ 'filterable' => true ],
						'_order'      => 100,
					],
				],
			],

			'attribute_display' => [
				'title'  => hivepress()->translator->get_string( 'display_noun' ),

				'fields' => [
					'display_areas'  => [
						'label'       => esc_html__( 'Areas', 'hivepress' ),
						'description' => esc_html__( 'Choose the template areas where you want to display this attribute.', 'hivepress' ),
						'type'        => 'select',
						'multiple'    => true,
						'_order'      => 10,

						'options'     => [
							'view_block_primary'   => esc_html__( 'Block', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'primary', 'area', 'hivepress' ) ),
							'view_block_secondary' => esc_html__( 'Block', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'secondary', 'area', 'hivepress' ) ),
							'view_block_ternary'   => esc_html__( 'Block', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'ternary', 'area', 'hivepress' ) ),
							'view_page_primary'    => esc_html__( 'Page', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'primary', 'area', 'hivepress' ) ),
							'view_page_secondary'  => esc_html__( 'Page', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'secondary', 'area', 'hivepress' ) ),
							'view_page_ternary'    => esc_html__( 'Page', 'hivepress' ) . ' ' . sprintf( '(%s)', esc_html_x( 'ternary', 'area', 'hivepress' ) ),
						],
					],

					'icon'           => [
						'label'   => esc_html__( 'Icon', 'hivepress' ),
						'type'    => 'select',
						'options' => 'icons',
						'_parent' => 'display_areas[]',
						'_order'  => 20,
					],

					'display_format' => [
						'label'       => esc_html__( 'Format', 'hivepress' ),
						'description' => esc_html__( 'Set the attribute display format.', 'hivepress' ) . ' ' . sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '%label%, %icon%, %value%, %parent_value%' ),
						'type'        => 'textarea',
						'max_length'  => 2048,
						'default'     => '%value%',
						'html'        => true,
						'_parent'     => 'display_areas[]',
						'_order'      => 30,
					],
				],
			],

			'option_settings'   => [
				'screen' => [],

				'fields' => [
					'sort_order' => [
						'label'     => esc_html_x( 'Order', 'sort priority', 'hivepress' ),
						'type'      => 'number',
						'min_value' => 0,
						'default'   => 0,
						'required'  => true,
						'_order'    => 10,
					],
				],
			],
		];

		// Add meta boxes.
		foreach ( $this->get_models() as $model ) {
			foreach ( $meta_box_args as $meta_box_name => $meta_box ) {
				if ( strpos( $meta_box_name, 'attribute' ) === 0 ) {

					// Skip adding meta box.
					if ( 'attribute_search' === $meta_box_name && ! $this->models[ $model ]['searchable'] ) {
						continue;
					}

					// Set screen and model.
					$meta_box['model'] = $model;

					if ( 'attributes' === $meta_box_name ) {
						$meta_box['screen'] = $model;

						if ( ! isset( $this->models[ $model ]['category_model'] ) && $this->requires_category_model( $model ) ) {
							$meta_box['fields']['categories'] = [
								'label'       => hivepress()->translator->get_string( 'category' ),
								'type'        => 'select',
								'options'     => 'terms',
								'option_args' => [ 'taxonomy' => hp\prefix( $model . '_category' ) ],
								'required'    => true,
								'_order'      => 1,

								'attributes'  => [
									'data-multistep' => 'true',
									'data-render'    => hivepress()->router->get_url( 'meta_box_resource', [ 'meta_box_name' => $model . '_' . $meta_box_name ] ),
								],
							];
						}
					} else {
						$meta_box['screen'] = $model . '_attribute';

						foreach ( [ 'edit', 'search' ] as $field_context ) {
							if ( isset( $meta_box['fields'][ $field_context . '_field_type' ] ) ) {
								$meta_box['fields'][ $field_context . '_field_type' ]['attributes'] = [
									'data-render' => hivepress()->router->get_url( 'meta_box_resource', [ 'meta_box_name' => $model . '_' . $meta_box_name ] ),
								];
							}
						}
					}

					// @todo replace temporary fix.
					if ( 'listing' === $model && 'attribute_edit' === $meta_box_name ) {
						$meta_box['fields']['synced'] = [
							'label'       => esc_html_x( 'Synced', 'attribute', 'hivepress' ),
							'caption'     => esc_html__( 'Sync with the vendor field', 'hivepress' ),
							'description' => esc_html__( 'Check this option to sync the value with the vendor field of the same name.', 'hivepress' ),
							'type'        => 'checkbox',
							'_order'      => 20,
						];

						$meta_box['fields']['moderated'] = [
							'label'   => esc_html_x( 'Moderated', 'attribute', 'hivepress' ),
							'caption' => esc_html__( 'Manually approve changes', 'hivepress' ),
							'type'    => 'checkbox',
							'_parent' => 'editable',
							'_order'  => 30,
						];
					}
				} elseif ( 'option_settings' === $meta_box_name ) {
					foreach ( $this->attributes[ $model ] as $attribute_name => $attribute ) {
						if ( isset( $attribute['edit_field']['options'] ) && ! isset( $attribute['edit_field']['_external'] ) ) {

							// Get screen.
							$screen = $model . '_' . $attribute_name;

							// Add screen.
							if ( ! post_type_exists( hp\prefix( $screen ) ) ) {
								$meta_box['screen'][] = $screen;
							}
						}
					}
				}

				// Add meta box.
				$meta_boxes[ $model . '_' . $meta_box_name ] = $meta_box;
			}
		}

		return $meta_boxes;
	}

	/**
	 * Removes meta boxes.
	 */
	public function remove_meta_boxes() {
		global $pagenow;

		if ( in_array( $pagenow, [ 'post.php', 'post-new.php' ], true ) ) {

			// Get post type.
			$post_type = get_post_type();

			if ( in_array( $post_type, hp\prefix( $this->get_models( 'post' ) ), true ) ) {

				// Get model.
				$model = hp\unprefix( $post_type );

				// Get category IDs.
				$category_ids = $this->get_category_ids( $model, get_the_ID() );

				// Get attributes.
				$attributes = $this->get_attributes( $model, $category_ids );

				// Remove meta boxes.
				remove_meta_box( hp\prefix( $model . '_categorydiv' ), hp\prefix( $model ), 'side' );

				foreach ( $this->attributes[ $model ] as $attribute_name => $attribute ) {
					if ( ! isset( $attributes[ $attribute_name ] ) && isset( $attribute['edit_field']['options'] ) && ! isset( $attribute['edit_field']['_external'] ) ) {
						remove_meta_box( hp\prefix( $model . '_' . $attribute_name . 'div' ), hp\prefix( $model ), 'side' );
					}
				}
			}
		}
	}

	/**
	 * Redirects archive page.
	 */
	public function redirect_archive_page() {

		// Check page.
		if ( ! is_post_type_archive( hp\prefix( $this->get_models( 'post' ) ) ) || is_search() || is_feed() ) {
			return;
		}

		// Get model.
		$model = hp\unprefix( get_post_type() );

		// Get page ID.
		$page_id = absint( get_option( hp\prefix( 'page_' . $model . 's' ) ) );

		if ( ! $page_id ) {
			return;
		}

		// Get page slug.
		$page_slug = get_post_field( 'post_name', $page_id );

		if ( hp\get_array_value( get_queried_object()->rewrite, 'slug' ) === $page_slug ) {
			return;
		}

		// Redirect page.
		wp_safe_redirect( get_permalink( $page_id ) );

		exit;
	}

	/**
	 * Sets WP search query.
	 *
	 * @param WP_Query $query Search query.
	 */
	public function set_search_query( $query ) {

		// Check query.
		if ( ! $query->is_main_query() && ! $query->get( 'hp_main' ) ) {
			return;
		}

		// Get model.
		$model = null;

		foreach ( $this->get_models( 'post' ) as $model_name ) {
			if ( is_post_type_archive( hp\prefix( $model_name ) ) || $this->get_term_id( $model_name ) ) {
				$model = $model_name;

				break;
			}
		}

		if ( empty( $model ) ) {
			return;
		}

		if ( $query->is_main_query() ) {

			// Set post type.
			$query->set( 'post_type', hp\prefix( $model ) );

			// Set status.
			$query->set( 'post_status', 'publish' );

			// Paginate results.
			$query->set( 'posts_per_page', absint( get_option( hp\prefix( $model . 's_per_page' ) ) ) );
		}

		// Get meta and taxonomy queries.
		$meta_query = array_filter( (array) $query->get( 'meta_query' ) );
		$tax_query  = array_filter( (array) $query->get( 'tax_query' ) );

		// Get category ID.
		$category_id = null;

		if ( $query->is_main_query() ) {
			$category_id = $this->get_category_id( $model );

			if ( $category_id ) {

				// Set category ID.
				$tax_query[] = [
					[
						'taxonomy' => hp\prefix( $this->get_category_model( $model ) ),
						'terms'    => $category_id,
					],
				];
			} else {

				// Set term ID.
				$term_id = $this->get_term_id( $model );

				if ( $term_id ) {
					$tax_query[] = [
						[
							'taxonomy' => get_queried_object()->taxonomy,
							'terms'    => $term_id,
						],
					];
				}
			}
		}

		// Get attributes.
		$attributes = $this->get_attributes( $model, (array) $category_id );

		// Sort results.
		if ( $query->is_main_query() || $query->get( 'hp_archive' ) ) {

			// Create sort form.
			$sort_form = hp\create_class_instance( '\HivePress\Forms\\' . $model . '_sort' );

			if ( $sort_form ) {

				// Set form values.
				$sort_form->set_values( $_GET, true );

				if ( $sort_form->validate() ) {

					// Get sort parameter.
					$sort_param = $sort_form->get_value( '_sort' );

					if ( 'title' === $sort_param ) {

						// Set sort order.
						$query->set( 'orderby', 'title' );
						$query->set( 'order', 'ASC' );
					} else {

						// Get sort order.
						$sort_order = 'ASC';

						if ( strpos( $sort_param, '__' ) ) {
							list($sort_param, $sort_order) = explode( '__', $sort_param );
						}

						// Get sort attribute.
						$sort_attribute = hp\get_array_value( $attributes, $sort_param );

						if ( $sort_attribute && $sort_attribute['sortable'] ) {

							// Get sort field.
							$sort_field = hp\create_class_instance( '\HivePress\Fields\\' . $sort_attribute['edit_field']['type'], [ $sort_attribute['edit_field'] ] );

							if ( $sort_field && $sort_field::get_meta( 'sortable' ) ) {

								// Update field filter.
								$sort_field->update_filter( true );

								// Set sort filter.
								$sort_filter = [
									'key'  => hp\prefix( $sort_param ),
									'type' => hp\get_array_value( $sort_field->get_filter(), 'type' ),
								];

								// Add meta clause.
								$meta_query[] = [
									'relation' => 'OR',

									$sort_param . '__order' => array_merge(
										$sort_filter,
										[
											'compare' => 'NOT EXISTS',
										]
									),

									array_merge(
										$sort_filter,
										[
											'compare' => 'EXISTS',
										]
									),
								];

								// Set sort order.
								$query->set( 'orderby', $sort_param . '__order' );
								$query->set( 'order', strtoupper( $sort_order ) );
							}
						}
					}
				}
			}
		}

		// Filter results.
		if ( $query->is_search() ) {

			// Get attribute fields.
			$attribute_fields = $this->get_attribute_fields( $model, $_GET );

			// Get query arguments.
			$query_args = $this->get_query_args( $attribute_fields );

			$meta_query = array_merge( $meta_query, $query_args['meta_query'] );
			$tax_query  = array_merge( $tax_query, $query_args['tax_query'] );
		}

		// Set meta and taxonomy queries.
		$query->set( 'meta_query', $meta_query );
		$query->set( 'tax_query', $tax_query );

		if ( $query->is_search() ) {

			/**
			 * Fires when models are being searched. The dynamic part of the hook refers to the model name (e.g. `listing`, `vendor`).
			 *
			 * @hook hivepress/v1/models/{model_name}/search
			 * @param {WP_Query} $query Search query.
			 * @param {array} $fields Search fields.
			 */
			do_action( 'hivepress/v1/models/' . $model . '/search', $query, $attribute_fields );
		}

		if ( $query->is_main_query() ) {

			// Get featured results.
			$featured_count = absint( get_option( hp\prefix( $model . 's_featured_per_page' ) ) );

			if ( $featured_count ) {

				// Get featured IDs.
				$featured_ids = get_posts(
					[
						'post_type'        => hp\prefix( $model ),
						'post_status'      => 'publish',
						's'                => $query->get( 's' ),
						'tax_query'        => $query->get( 'tax_query' ),
						'meta_query'       => $query->get( 'meta_query' ),
						'meta_key'         => 'hp_featured',
						'posts_per_page'   => $featured_count,
						'orderby'          => 'rand',
						'fields'           => 'ids',
						'suppress_filters' => false,
					]
				);

				if ( $featured_ids ) {

					// Exclude featured IDs.
					$query->set( 'post__not_in', $featured_ids );

					// Set request context.
					hivepress()->request->set_context( 'featured_ids', $featured_ids );
				}
			}
		}
	}

	/**
	 * Disables Jetpack search.
	 *
	 * @param mixed    $enabled Is search enabled?
	 * @param WP_Query $query Search query.
	 * @return bool
	 */
	public function disable_jetpack_search( $enabled, $query ) {
		if ( $query->is_main_query() && $query->is_search() && strpos( $query->get( 'post_type' ), 'hp_' ) === 0 ) {
			$enabled = false;
		}

		return $enabled;
	}
}
