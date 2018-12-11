<?php
namespace HivePress;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract entity class.
 *
 * @class Entity
 */
abstract class Entity extends Component {

	/**
	 * Array of listing data.
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Array of listing attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Class constructor.
	 *
	 * @param array $settings
	 */
	public function __construct( $settings ) {
		parent::__construct( $settings );

		// Manage attributes.
		add_action( 'wp_loaded', [ $this, 'init_attributes' ] );
		add_action( 'wp_loaded', [ $this, 'add_attributes' ] );
		add_action( 'before_delete_post', [ $this, 'delete_attribute' ] );

		// Submit listing.
		add_filter( 'hivepress/form/form_values/' . $this->name . '__submit', [ $this, 'set_data' ] );
		add_filter( 'hivepress/form/form_fields/' . $this->name . '__submit', [ $this, 'add_terms_checkbox' ] );
		add_action( 'hivepress/form/submit_form/' . $this->name . '__submit', [ $this, 'submit' ] );
		add_filter( 'hivepress/form/form_args/' . $this->name . '__submit', [ $this, 'redirect_submission_form' ] );

		// Moderate listing.
		add_action( 'transition_post_status', [ $this, 'moderate' ], 10, 3 );

		// Update listing.
		add_filter( 'hivepress/form/form_values/' . $this->name . '__update', [ $this, 'set_data' ] );
		add_action( 'hivepress/form/submit_form/' . $this->name . '__update', [ $this, 'update' ] );

		// Report listing.
		add_filter( 'hivepress/form/form_values/' . $this->name . '__report', [ $this, 'set_data' ] );
		add_action( 'hivepress/form/submit_form/' . $this->name . '__report', [ $this, 'report' ] );

		// Delete listing.
		add_filter( 'hivepress/form/form_values/' . $this->name . '__delete', [ $this, 'set_data' ] );
		add_action( 'hivepress/form/submit_form/' . $this->name . '__delete', [ $this, 'delete' ] );

		// Count listings.
		add_action( 'save_post_' . hp_prefix( $this->name ), [ $this, 'count' ], 10, 2 );

		// Update featured image.
		add_action( 'hivepress/form/upload_file/' . $this->name . '__images', [ $this, 'update_image' ] );
		add_action( 'hivepress/form/delete_file/' . $this->name . '__images', [ $this, 'update_image' ] );
		add_action( 'hivepress/form/sort_files/' . $this->name . '__images', [ $this, 'update_image' ] );

		if ( ! is_admin() ) {

			// Initialize listing data.
			add_action( 'the_post', [ $this, 'init_data' ] );

			// Set search query.
			add_action( 'pre_get_posts', [ $this, 'set_search_query' ] );

			// Redirect submissions.
			add_action( 'hivepress/template/redirect_page/' . $this->name . '__submission', [ $this, 'redirect_submission' ] );
			add_action( 'hivepress/template/redirect_page/' . $this->name . '__submission_category', [ $this, 'redirect_submission_category' ] );
			add_action( 'hivepress/template/redirect_page/' . $this->name . '__submission_details', [ $this, 'redirect_submission_details' ] );
			add_action( 'hivepress/template/redirect_page/' . $this->name . '__submission_review', [ $this, 'redirect_submission_review' ] );

			// Redirect edit page.
			add_action( 'hivepress/template/redirect_page/' . $this->name . '__update', [ $this, 'redirect_edit' ] );

			// Redirect vendor page.
			add_action( 'hivepress/template/redirect_page/' . $this->name . '__vendor', [ $this, 'redirect_vendor' ] );

			// Set archive context.
			add_action( 'hivepress/template/template_context/' . $this->name . '_archive', [ $this, 'set_archive_context' ] );

			// Set view context.
			add_action( 'hivepress/template/template_context/' . $this->name . '_edits', [ $this, 'set_view_context' ] );

			// Set category context.
			add_action( 'hivepress/template/template_context/category_archive', [ $this, 'set_category_archive_context' ] );
			add_action( 'hivepress/template/template_context/' . $this->name . '_submission_category', [ $this, 'set_category_archive_context' ] );

			// Set vendor context.
			add_action( 'hivepress/template/template_context/archive_vendor', [ $this, 'set_vendor_context' ] );
			add_action( 'hivepress/template/template_context/single_vendor', [ $this, 'set_vendor_context' ] );

			// Set featured image.
			add_filter( 'post_thumbnail_html', [ $this, 'set_image' ], 10, 2 );
		} else {

			// Manage admin columns.
			add_action( 'manage_' . hp_prefix( $this->name ) . '_posts_columns', [ $this, 'add_admin_columns' ] );
			add_action( 'manage_' . hp_prefix( $this->name ) . '_attribute_posts_columns', [ $this, 'add_admin_columns' ] );
			add_action( 'manage_' . hp_prefix( $this->name ) . '_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );
			add_action( 'manage_' . hp_prefix( $this->name ) . '_attribute_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );

			// Set admin actions.
			add_filter( 'post_row_actions', [ $this, 'set_admin_actions' ], 10, 2 );

			// Remove meta boxes.
			add_action( 'admin_notices', [ $this, 'remove_meta_boxes' ] );
		}
	}

	/**
	 * Initializes listing data.
	 *
	 * @param WP_Post $post
	 */
	public function init_data( $post ) {
		if ( hp_prefix( $this->name ) === $post->post_type ) {
			$this->data = $this->get_data( $post->ID );
		}
	}

	/**
	 * Gets listing data.
	 *
	 * @param int $id
	 * @return array
	 */
	public function get_data( $id ) {
		$data = [];

		// Get attributes.
		foreach ( $this->get_attributes( $id ) as $attribute_id => $attribute ) {
			if ( 'taxonomy' === $attribute['type'] ) {
				$data[ $attribute_id ] = wp_get_post_terms( $id, hp_prefix( $this->name . '_' . $attribute_id ), [ 'fields' => 'ids' ] );
			} else {
				$data[ $attribute_id ] = trim( get_post_meta( $id, hp_prefix( $attribute_id ), true ) );
			}
		}

		// Get other data.
		$data = array_merge(
			$data,
			[
				'id'          => $id,
				'title'       => get_post_field( 'post_title', $id ),
				'description' => get_post_field( 'post_content', $id ),
				'images'      => wp_list_pluck( get_attached_media( 'image', $id ), 'ID' ),
			]
		);

		return $data;
	}

	/**
	 * Routes component functions.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __call( $name, $args ) {
		parent::__call( $name, $args );

		// Get listing data.
		if ( strpos( $name, 'get_' ) === 0 ) {
			return hp_get_array_value( $this->data, str_replace( 'get_', '', $name ) );
		}
	}

	/**
	 * Sets listing data.
	 *
	 * @param array $values
	 * @return array
	 */
	public function set_data( $values ) {

		// Get listing ID.
		$listing_id = $this->get_id();

		if ( get_query_var( 'hp_' . $this->name . '_edit' ) ) {
			$listing_id = absint( get_query_var( 'hp_' . $this->name . '_edit' ) );
		} elseif ( get_query_var( 'hp_' . $this->name . '_submission_details' ) ) {
			$listing_id = $this->get_submission_id();
		}

		// Add listing data.
		$values = array_merge( $values, $this->get_data( $listing_id ) );

		// Set listing ID.
		$values['post_id'] = $values['id'];

		unset( $values['id'] );

		return $values;
	}

	/**
	 * Updates listing data.
	 *
	 * @param int   $id
	 * @param array $values
	 */
	protected function update_data( $id, $values ) {

		// Update attributes.
		foreach ( $this->get_attributes( $id ) as $attribute_id => $attribute ) {
			if ( $attribute['editable'] ) {
				if ( 'taxonomy' === $attribute['type'] ) {
					if ( is_array( $values[ $attribute_id ] ) ) {
						$values[ $attribute_id ] = array_map( 'absint', $values[ $attribute_id ] );
					} else {
						$values[ $attribute_id ] = absint( $values[ $attribute_id ] );
					}

					wp_set_object_terms( $id, $values[ $attribute_id ], hp_prefix( $this->name . '_' . $attribute_id ) );
				} else {
					update_post_meta( $id, hp_prefix( $attribute_id ), $values[ $attribute_id ] );
				}
			}
		}

		// Update other data.
		wp_update_post(
			[
				'ID'           => $id,
				'post_title'   => $values['title'],
				'post_content' => $values['description'],
			]
		);
	}

	/**
	 * Initializes attributes.
	 */
	public function init_attributes() {

		// Get attribute posts.
		$attribute_posts = get_posts(
			[
				'post_type'      => hp_prefix( $this->name . '_attribute' ),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			]
		);

		foreach ( $attribute_posts as $attribute_post ) {

			// Get attribute ID.
			$attribute_id = substr( hp_sanitize_id( urldecode( $attribute_post->post_name ) ), 0, 64 - strlen( 'hp_' ) );

			// Set attribute defaults.
			$attribute = [
				'type'       => 'meta',
				'name'       => $attribute_post->post_title,
				'order'      => absint( $attribute_post->menu_order ),
				'format'     => $attribute_post->hp_format,
				'areas'      => (array) $attribute_post->hp_areas,
				'editable'   => (bool) $attribute_post->hp_editable,
				'required'   => (bool) $attribute_post->hp_required,
				'moderated'  => (bool) $attribute_post->hp_moderated,
				'filterable' => (bool) $attribute_post->hp_filterable,
				'sortable'   => (bool) $attribute_post->hp_sortable,
			];

			// Get attribute categories.
			$attribute['categories'] = [];

			if ( '' !== $attribute_post->hp_category ) {
				$category_id  = absint( $attribute_post->hp_category );
				$category_ids = get_term_children( $category_id, hp_prefix( $this->name . '_category' ) );

				$attribute['categories'] = array_merge( [ $category_id ], $category_ids );
			}

			if ( '' !== $attribute_post->hp_type ) {

				// Get field types.
				$field_types = explode( '__', $attribute_post->hp_type );

				// Set field defaults.
				$field = [
					'name'  => $attribute['name'],
					'order' => 100 + $attribute['order'],
				];

				switch ( reset( $field_types ) ) {

					case 'text':
						$field['max_length'] = 128;

						break;

					case 'number':
						if ( '' !== $attribute_post->hp_decimals ) {
							$field['decimals'] = absint( $attribute_post->hp_decimals );
						}

						break;

					case 'checkbox':
						$field['label'] = $attribute['name'];

						if ( '' !== $attribute_post->hp_label ) {
							$field['label'] = $attribute_post->hp_label;
						}

						break;
				}

				if ( in_array( reset( $field_types ), [ 'select', 'radio', 'checkboxes' ], true ) ) {
					$attribute_id = substr( $attribute_id, 0, 32 - strlen( hp_prefix( $this->name . '_' ) ) );

					$attribute['type'] = 'taxonomy';

					$field['options']  = 'terms';
					$field['taxonomy'] = hp_prefix( $this->name . '_' . $attribute_id );
				}

				// Set attribute fields.
				$attribute['edit_field'] = hp_merge_arrays(
					$field,
					[
						'type'     => reset( $field_types ),
						'required' => $attribute['required'],
					]
				);

				$attribute['search_field'] = hp_merge_arrays(
					$field,
					[
						'type' => end( $field_types ),
					]
				);
			}

			// Add attribute.
			$this->attributes[ $attribute_id ] = $attribute;
		}

		// Sort attributes.
		$this->attributes = hp_sort_array( $this->attributes );
	}

	/**
	 * Adds attributes.
	 */
	public function add_attributes() {

		// Get taxonomies.
		$taxonomies = [];

		foreach ( $this->attributes as $attribute_id => $attribute ) {
			if ( 'taxonomy' === $attribute['type'] ) {

				// Get taxonomy name.
				$taxonomy_name = hp_prefix( $this->name . '_' . $attribute_id );

				// Set taxonomy arguments.
				$taxonomy = [
					'label'        => $attribute['name'],
					'hierarchical' => true,
					'public'       => false,
					'show_ui'      => true,
					'show_in_menu' => false,
					'rewrite'      => false,
				];

				// Register taxonomy.
				register_taxonomy( $taxonomy_name, hp_prefix( $this->name ), $taxonomy );

				// Add taxonomy.
				$taxonomies[ $taxonomy_name ] = $taxonomy;
			}
		}

		// Add attribute fields.
		add_filter( 'hivepress/admin/meta_box_fields/' . $this->name . '__attributes', [ $this, 'add_attribute_fields' ], 10, 2 );
		add_filter( 'hivepress/form/form_fields/' . $this->name . '__submit', [ $this, 'add_attribute_fields' ], 10, 2 );
		add_filter( 'hivepress/form/form_fields/' . $this->name . '__update', [ $this, 'add_attribute_fields' ], 10, 2 );
		add_filter( 'hivepress/form/form_fields/' . $this->name . '__filter', [ $this, 'add_attribute_fields' ], 10, 2 );

		// Add sorting options.
		add_filter( 'hivepress/form/form_fields/' . $this->name . '__sort', [ $this, 'add_sorting_options' ] );
	}

	/**
	 * Deletes attribute.
	 *
	 * @param int $listing_id
	 */
	public function delete_attribute( $listing_id ) {
		if ( get_post_type( $listing_id ) === hp_prefix( $this->name . '_attribute' ) ) {

			// Get attribute ID.
			$attribute_id = hp_sanitize_id( str_replace( '__trashed', '', urldecode( get_post_field( 'post_name', $listing_id ) ) ) );
			$attribute_id = substr( $attribute_id, 0, 64 - strlen( 'hp_' ) );

			// Get attribute.
			$attribute = hp_get_array_value( $this->attributes, $attribute_id );

			if ( ! is_null( $attribute ) ) {

				// Delete terms.
				if ( 'taxonomy' === $attribute['type'] ) {
					$attribute_id = substr( $attribute_id, 0, 32 - strlen( hp_prefix( $this->name . '_' ) ) );

					$taxonomy = hp_prefix( $this->name . '_' . $attribute_id );

					if ( ! taxonomy_exists( $taxonomy ) ) {
						register_taxonomy( $taxonomy, hp_prefix( $this->name ) );
					}

					$terms = get_terms(
						[
							'taxonomy'   => $taxonomy,
							'hide_empty' => false,
						]
					);

					foreach ( $terms as $term ) {
						wp_delete_term( $term->term_id, $taxonomy );
					}

					// Delete meta.
				} else {
					$meta_key = hp_prefix( $attribute_id );

					$listing_ids = get_posts(
						[
							'post_type'      => hp_prefix( $this->name ),
							'post_status'    => 'any',
							'posts_per_page' => -1,
							'fields'         => 'ids',
						]
					);

					foreach ( $listing_ids as $listing_id ) {
						delete_post_meta( $listing_id, $meta_key );
					}
				}
			}
		}
	}

	/**
	 * Adds attribute fields.
	 *
	 * @param array $fields
	 * @param array $args
	 * @return array
	 */
	public function add_attribute_fields( $fields, $args ) {

		// Check current form.
		$is_meta_box    = strpos( current_filter(), '__attributes' ) !== false;
		$is_edit_form   = strpos( current_filter(), '__submit' ) !== false || strpos( current_filter(), '__update' ) !== false;
		$is_filter_form = strpos( current_filter(), '__filter' ) !== false;

		// Get attributes.
		$attributes = [];

		if ( isset( $args['post_id'] ) ) {
			$attributes = $this->get_attributes( $args['post_id'] );
		} else {

			// Get category ID.
			$category_id = $this->get_category_id();

			// Filter attributes.
			$attributes = array_filter(
				$this->attributes,
				function( $attribute ) use ( $category_id ) {
					return empty( $attribute['categories'] ) || in_array( $category_id, $attribute['categories'], true );
				}
			);
		}

		// Add fields.
		foreach ( $attributes as $attribute_id => $attribute ) {
			if ( ( $is_meta_box && 'meta' === $attribute['type'] ) || ( $is_edit_form && $attribute['editable'] ) || ( $is_filter_form && $attribute['filterable'] ) ) {
				if ( $is_filter_form ) {
					$fields[ $attribute_id ] = $attribute['search_field'];
				} else {
					$fields[ $attribute_id ] = $attribute['edit_field'];
				}
			}
		}

		return $fields;
	}

	/**
	 * Adds sorting options.
	 *
	 * @param array $fields
	 * @return array
	 */
	public function add_sorting_options( $fields ) {

		// Add default option.
		if ( is_search() ) {
			$fields['sort']['options']['relevance'] = esc_html__( 'Relevance', 'hivepress' );
		} else {
			$fields['sort']['options']['date'] = esc_html__( 'Date', 'hivepress' );
		}

		// Get category ID.
		$category_id = $this->get_category_id();

		// Filter attributes.
		$attributes = array_filter(
			$this->attributes,
			function( $attribute ) use ( $category_id ) {
				return empty( $attribute['categories'] ) || in_array( $category_id, $attribute['categories'], true );
			}
		);

		// Add attribute options.
		foreach ( $attributes as $attribute_id => $attribute ) {
			if ( $attribute['sortable'] ) {
				if ( 'number' === $attribute['edit_field']['type'] ) {
					$fields['sort']['options'][ $attribute_id . '__asc' ]  = sprintf( '%s &uarr;', $attribute['name'] );
					$fields['sort']['options'][ $attribute_id . '__desc' ] = sprintf( '%s &darr;', $attribute['name'] );
				} else {
					$fields['sort']['options'][ $attribute_id ] = $attribute['name'];
				}
			}
		}

		return $fields;
	}

	/**
	 * Gets attributes.
	 *
	 * @param int $id
	 * @return array
	 */
	private function get_attributes( $id ) {

		// Get category IDs.
		$category_ids = wp_get_post_terms( absint( $id ), hp_prefix( $this->name . '_category' ), [ 'fields' => 'ids' ] );

		// Filter attributes.
		$attributes = array_filter(
			$this->attributes,
			function( $attribute ) use ( $category_ids ) {
				return empty( $attribute['categories'] ) || count( array_intersect( $category_ids, $attribute['categories'] ) ) > 0;
			}
		);

		return $attributes;
	}

	/**
	 * Renders attributes.
	 *
	 * @param string $area_id
	 * @param array  $args
	 * @return string
	 */
	public function render_attributes( $area_id, $args = [] ) {
		$output = '';

		// Set default arguments.
		$args = hp_merge_arrays(
			[
				'before'           => '',
				'after'            => '',
				'before_attribute' => '',
				'after_attribute'  => '',
			],
			$args
		);

		$args['area_slug'] = preg_replace( '/[_]+/', '-', $area_id );

		// Get attributes.
		$attributes = array_filter(
			$this->get_attributes( $this->get_id() ),
			function( $attribute ) use ( $area_id ) {
				return in_array( $area_id, $attribute['areas'], true );
			}
		);

		// Render attributes.
		if ( ! empty( $attributes ) ) {
			$output .= hp_replace_placeholders( $args, $args['before'] );

			foreach ( $attributes as $attribute_id => $attribute ) {
				$attribute_value = hp_get_array_value( $this->data, $attribute_id, '' );

				if ( ! empty( $attribute_value ) || 0 === $attribute_value ) {
					$output .= hp_replace_placeholders( $attribute, $args['before_attribute'] );

					// Get attribute value.
					$value = '';

					if ( 'taxonomy' === $attribute['type'] ) {
						$value = wp_strip_all_tags( get_the_term_list( $this->get_id(), hp_prefix( $this->name . '_' . $attribute_id ), '', ', ' ) );
					} else {
						if ( 'checkbox' === $attribute['edit_field']['type'] ) {
							$value = $attribute['edit_field']['label'];
						} elseif ( 'number' === $attribute['edit_field']['type'] ) {
							$value = number_format_i18n( $attribute_value, $attribute['edit_field']['decimals'] );
						} else {
							$value = $attribute_value;
						}

						$value = esc_html( $value );
					}

					// Format attribute value.
					$output .= hp_replace_placeholders( [ 'value' => $value ], $attribute['format'] );

					$output .= hp_replace_placeholders( $attribute, $args['after_attribute'] );
				}
			}

			$output .= hp_replace_placeholders( $args, $args['after'] );
		}

		return $output;
	}

	/**
	 * Adds terms checkbox.
	 *
	 * @param array $fields
	 * @return array
	 */
	public function add_terms_checkbox( $fields ) {

		// Get page ID.
		$page_id = hp_get_post_id(
			[
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post__in'    => [ absint( get_option( 'hp_page_' . $this->name . '_submission_terms' ) ) ],
			]
		);

		// Add checkbox.
		if ( 0 !== $page_id ) {
			$fields['terms'] = [
				'label'    => sprintf( hp_sanitize_html( __( 'I agree to %s' ) ), '<a href="' . esc_url( get_permalink( $page_id ) ) . '" target="_blank">' . get_the_title( $page_id ) . '</a>' ),
				'type'     => 'checkbox',
				'required' => true,
				'order'    => 1000,
			];
		}

		return $fields;
	}

	/**
	 * Submits listing.
	 *
	 * @param array $values
	 */
	public function submit( $values ) {

		// Get listing ID.
		$listing_id = hp_get_post_id(
			[
				'post_type'   => hp_prefix( $this->name ),
				'post_status' => 'auto-draft',
				'author'      => get_current_user_id(),
				'post_parent' => 0,
			]
		);

		if ( 0 !== $listing_id ) {

			// Update data.
			$this->update_data( $listing_id, $values );

			// Set status.
			$status = 'publish';

			if ( get_option( 'hp_' . $this->name . '_enable_moderation' ) ) {
				$status = 'pending';
			}

			wp_update_post(
				[
					'ID'          => $listing_id,
					'post_status' => $status,
				]
			);

			// Update user role.
			if ( ! current_user_can( 'edit_posts' ) ) {
				wp_update_user(
					[
						'ID'   => get_current_user_id(),
						'role' => 'contributor',
					]
				);
			}

			// Get listing URL.
			$listing_url = admin_url( 'post.php?action=edit&post=' . $listing_id );

			if ( 'publish' === $status ) {
				$listing_url = get_permalink( $listing_id );
			}

			// Send email.
			hivepress()->email->send(
				$this->name . '__submit',
				[
					'to'           => get_option( 'admin_email' ),
					'placeholders' => [
						'listing_title' => get_the_title( $listing_id ),
						'listing_url'   => $listing_url,
					],
				]
			);
		}
	}

	/**
	 * Moderates listing.
	 *
	 * @param string  $new_status
	 * @param string  $old_status
	 * @param WP_Post $post
	 */
	public function moderate( $new_status, $old_status, $post ) {
		if ( hp_prefix( $this->name ) === $post->post_type && 'pending' === $old_status && in_array( $new_status, [ 'publish', 'trash' ], true ) ) {

			// Get action.
			$action = 'approve';

			if ( 'draft' === $new_status ) {
				$action = 'moderate';
			} elseif ( 'trash' === $new_status ) {
				$action = 'reject';
			}

			// Get user.
			$user = get_userdata( $post->post_author );

			// Send email.
			hivepress()->email->send(
				$this->name . '__' . $action,
				[
					'to'           => $user->user_email,
					'placeholders' => [
						'user_name'     => $user->display_name,
						'listing_title' => get_the_title( $post->ID ),
						'listing_url'   => get_permalink( $post->ID ),
						'review_text'   => $post->hp_feedback,
					],
				]
			);
		}
	}

	/**
	 * Updates listing.
	 *
	 * @param array $values
	 */
	public function update( $values ) {

		// Get listing ID.
		$listing_id = hp_get_post_id(
			[
				'post_type'   => hp_prefix( $this->name ),
				'post_status' => [ 'draft', 'publish' ],
				'post__in'    => [ absint( $values['post_id'] ) ],
				'author'      => get_current_user_id(),
			]
		);

		if ( 0 !== $listing_id ) {

			// Get listing data.
			$listing = $this->get_data( $listing_id );

			// Update listing data.
			$this->update_data( $listing_id, $values );

			// Check moderated attributes.
			$attributes = [];

			foreach ( $this->get_attributes( $listing_id ) as $attribute_id => $attribute ) {
				if ( $attribute['moderated'] && $values[ $attribute_id ] !== $listing[ $attribute_id ] ) {
					$attributes[] = $attribute['name'];
				}
			}

			if ( ! empty( $attributes ) ) {

				// Change listing status.
				wp_update_post(
					[
						'ID'          => $listing_id,
						'post_status' => 'pending',
					]
				);

				// Send email.
				hivepress()->email->send(
					$this->name . '__update',
					[
						'to'           => get_option( 'admin_email' ),
						'placeholders' => [
							'listing_title'   => get_the_title( $listing_id ),
							'listing_url'     => admin_url( 'post.php?action=edit&post=' . $listing_id ),
							'listing_changes' => implode( ', ', $attributes ),
						],
					]
				);
			}
		}
	}

	/**
	 * Reports listing.
	 *
	 * @param array $values
	 */
	public function report( $values ) {

		// Get listing ID.
		$listing_id = hp_get_post_id(
			[
				'post_type'   => hp_prefix( $this->name ),
				'post_status' => 'publish',
				'post__in'    => [ absint( $values['post_id'] ) ],
			]
		);

		if ( 0 !== $listing_id ) {

			// Send email.
			hivepress()->email->send(
				$this->name . '__report',
				[
					'to'           => get_option( 'admin_email' ),
					'placeholders' => [
						'listing_title' => get_the_title( $listing_id ),
						'listing_url'   => get_permalink( $listing_id ),
						'report_reason' => $values['reason'],
					],
				]
			);
		}
	}

	/**
	 * Deletes listing.
	 *
	 * @param array $values
	 */
	public function delete( $values ) {

		// Get listing ID.
		$listing_id = hp_get_post_id(
			[
				'post_type'   => hp_prefix( $this->name ),
				'post_status' => [ 'draft', 'publish' ],
				'post__in'    => [ absint( $values['post_id'] ) ],
				'author'      => get_current_user_id(),
			]
		);

		if ( 0 !== $listing_id ) {

			// Delete listing.
			wp_update_post(
				[
					'ID'          => $listing_id,
					'post_status' => 'trash',
				]
			);
		}
	}

	/**
	 * Counts listings.
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public function count( $post_id, $post ) {

		// Get vendor ID.
		$vendor_id = absint( $post->post_author );

		// Count listings.
		$count = count(
			get_posts(
				[
					'post_type'      => hp_prefix( $this->name ),
					'post_status'    => 'publish',
					'author'         => $vendor_id,
					'posts_per_page' => -1,
					'fields'         => 'ids',
				]
			)
		);

		// Update vendor.
		if ( $count > 0 ) {
			update_user_meta( $vendor_id, 'hp_' . $this->name . '_count', $count );
		} else {
			delete_user_meta( $vendor_id, 'hp_' . $this->name . '_count' );
		}
	}

	/**
	 * Sets search query.
	 *
	 * @param WP_Query $query
	 */
	public function set_search_query( $query ) {
		if ( $query->is_main_query() && $this->is_archive() ) {

			// Set results per page.
			$query->set( 'posts_per_page', absint( get_option( hp_prefix( $this->name . 's_per_page' ) ) ) );

			// Sort results.
			$sort_filters = hivepress()->form->validate_form( $this->name . '__sort' );

			if ( false !== $sort_filters ) {

				// Get attribute ID.
				$attribute_id = $sort_filters['sort'];

				if ( strpos( $sort_filters['sort'], '__' ) !== false ) {
					list($attribute_id, $sort_order) = explode( '__', $sort_filters['sort'] );
				}

				// Get attribute.
				$attribute = hp_get_array_value( $this->attributes, $attribute_id );

				if ( ! is_null( $attribute ) ) {
					$query->set( 'meta_key', hp_prefix( $attribute_id ) );

					if ( 'number' === $attribute['edit_field']['type'] ) {
						$query->set( 'orderby', 'meta_value_num' );
						$query->set( 'order', $sort_order );
					}
				} else {
					$query->set( 'orderby', $attribute_id );
				}
			}

			// Set category.
			$category_id = absint( hp_get_array_value( $_GET, 'category' ) );

			if ( 0 !== $category_id ) {
				$tax_query = (array) $query->get( 'tax_query' );

				$tax_query[] = [
					'taxonomy' => hp_prefix( $this->name . '_category' ),
					'terms'    => $category_id,
				];

				$query->set( 'tax_query', $tax_query );
			}

			// Filter results.
			if ( $query->is_search ) {

				// Get meta and tax query.
				$meta_query = (array) $query->get( 'meta_query' );
				$tax_query  = (array) $query->get( 'tax_query' );

				foreach ( $this->attributes as $attribute_id => $attribute ) {

					// Get attribute value.
					$attribute_value = hivepress()->form->validate_field( $attribute['search_field'], hp_get_array_value( $_GET, $attribute_id ) );

					if ( false !== $attribute_value && ( ( ! is_array( $attribute_value ) && '' !== $attribute_value ) || ( is_array( $attribute_value ) && ! empty( $attribute_value ) ) ) ) {

						// Set default filters.
						$meta_filters = [];
						$tax_filters  = [];

						$meta_filter = [
							'key'   => hp_prefix( $attribute_id ),
							'value' => $attribute_value,
						];

						$tax_filter = [
							'taxonomy' => hp_prefix( $this->name . '_' . $attribute_id ),
							'terms'    => $attribute_value,
						];

						// Add filters.
						switch ( $attribute['search_field']['type'] ) {

							// Text.
							case 'text':
							case 'email':
								$meta_filters[] = array_merge(
									$meta_filter,
									[
										'compare' => 'LIKE',
									]
								);

								break;

							// Number.
							case 'number':
								$meta_filters[] = array_merge(
									$meta_filter,
									[
										'type' => 'NUMERIC',
									]
								);

								break;

							// Number range.
							case 'number_range':
								if ( [ '', '' ] !== $meta_filter['value'] ) {
									$meta_filter = array_merge(
										$meta_filter,
										[
											'type'    => 'NUMERIC',
											'compare' => 'BETWEEN',
										]
									);

									if ( reset( $meta_filter['value'] ) === '' ) {
										$meta_filter['value']   = end( $meta_filter['value'] );
										$meta_filter['compare'] = '<=';
									} elseif ( end( $meta_filter['value'] ) === '' ) {
										$meta_filter['value']   = reset( $meta_filter['value'] );
										$meta_filter['compare'] = '>=';
									}

									$meta_filters[] = $meta_filter;
								}

								break;

							// Checkbox.
							case 'checkbox':
								$meta_filters[] = $meta_filter;

								break;

							// Other.
							case 'select':
							case 'radio':
							case 'checkboxes':
								if ( 'checkboxes' === $attribute['edit_field']['type'] ) {
									$tax_filter['operator'] = 'AND';
								}

								$tax_filters[] = $tax_filter;

								break;
						}

						// Add meta and tax query.
						$meta_query = array_merge( $meta_query, $meta_filters );
						$tax_query  = array_merge( $tax_query, $tax_filters );
					}
				}

				// Set meta and tax query.
				$query->set( 'meta_query', $meta_query );
				$query->set( 'tax_query', $tax_query );
			}
		}
	}

	/**
	 * Checks archive pages.
	 *
	 * @return bool
	 */
	public function is_archive() {
		$page_id = absint( get_option( 'hp_page_' . $this->name . 's' ) );

		return ( is_page() && get_queried_object_id() === $page_id ) || is_post_type_archive( hp_prefix( $this->name ) ) || is_tax( hp_prefix( $this->name . '_category' ), hp_prefix( $this->name . '_tag' ) );
	}

	/**
	 * Gets submission ID.
	 *
	 * @return int
	 */
	private function get_submission_id() {

		// Get listing ID.
		$listing_id = hp_get_post_id(
			[
				'post_type'   => hp_prefix( $this->name ),
				'post_status' => 'auto-draft',
				'author'      => get_current_user_id(),
				'post_parent' => 0,
			]
		);

		if ( 0 === $listing_id ) {

			// Add listing.
			$listing_id = wp_insert_post(
				[
					'post_type'   => hp_prefix( $this->name ),
					'post_status' => 'auto-draft',
					'post_author' => get_current_user_id(),
				]
			);
		} else {

			// Clear title.
			wp_update_post(
				[
					'ID'         => $listing_id,
					'post_title' => '',
				]
			);
		}

		return $listing_id;
	}

	/**
	 * Redirects submission page.
	 */
	public function redirect_submission() {
		$menu_items = hivepress()->template->get_menu( $this->name . '_submission' );

		if ( ! empty( $menu_items ) ) {
			$menu_item = reset( $menu_items );

			hp_redirect( $menu_item['url'] );
		}
	}

	/**
	 * Redirects submission category page.
	 */
	public function redirect_submission_category() {

		// Get listing categories.
		$categories = $this->get_categories();

		if ( empty( $categories ) ) {

			// Get current category ID.
			$category_id = absint( get_query_var( 'hp_' . $this->name . '_submission_category' ) );

			if ( $category_id > 1 ) {

				// Get submission ID.
				$listing_id = $this->get_submission_id();

				// Set submission category.
				wp_set_post_terms( $listing_id, [ $category_id ], hp_prefix( $this->name . '_category' ) );
			}

			hp_redirect( hivepress()->template->get_url( $this->name . '__submission_details' ) );
		}
	}

	/**
	 * Redirects submission details page.
	 */
	public function redirect_submission_details() {

		// Get listing categories.
		$categories = $this->get_categories();

		if ( ! empty( $categories ) ) {

			// Get submission category IDs.
			$category_ids = wp_get_post_terms( $this->get_submission_id(), hp_prefix( $this->name . '_category' ), [ 'fields' => 'ids' ] );

			if ( empty( $category_ids ) ) {
				hp_redirect( hivepress()->template->get_url( $this->name . '__submission_category' ) );
			}
		}
	}

	/**
	 * Redirects submission review page.
	 */
	public function redirect_submission_review() {

		// Get listing ID.
		$listing_id = hp_get_post_id(
			[
				'post_type'   => hp_prefix( $this->name ),
				'post_status' => [ 'pending', 'publish' ],
				'post__in'    => [ absint( get_query_var( 'hp_' . $this->name . '_submission_review' ) ) ],
				'author'      => get_current_user_id(),
			]
		);

		if ( 0 === $listing_id || 'publish' === get_post_status( $listing_id ) ) {
			$url = home_url();

			// Get listing URL.
			if ( 0 !== $listing_id ) {
				$url = get_permalink( $listing_id );
			}

			hp_redirect( $url );
		}
	}

	/**
	 * Redirects submission form.
	 *
	 * @param array $args
	 * @return array
	 */
	public function redirect_submission_form( $args ) {
		$args['success_redirect'] = hivepress()->template->get_url( $this->name . '__submission_review', [ $this->get_submission_id() ] );

		return $args;
	}

	/**
	 * Redirects edit page.
	 */
	public function redirect_edit() {

		// Get listing ID.
		$listing_id = hp_get_post_id(
			[
				'post_type'   => hp_prefix( $this->name ),
				'post_status' => [ 'draft', 'publish' ],
				'post__in'    => [ absint( get_query_var( 'hp_' . $this->name . '_edit' ) ) ],
				'author'      => get_current_user_id(),
			]
		);

		// Redirect user.
		if ( 0 === $listing_id ) {
			hp_redirect( hivepress()->template->get_url( $this->name . '__view' ) );
		}
	}

	/**
	 * Sets view context.
	 *
	 * @param array $context
	 * @return array
	 */
	public function set_view_context( $context ) {
		$context['listing_query'] = new \WP_Query(
			[
				'post_type'      => hp_prefix( $this->name ),
				'post_status'    => [ 'pending', 'draft', 'publish' ],
				'posts_per_page' => -1,
				'author'         => get_current_user_id(),
			]
		);

		return $context;
	}

	/**
	 * Redirects vendor page.
	 */
	public function redirect_vendor() {

		// Get vendor.
		$vendor = get_user_by( 'login', sanitize_user( get_query_var( 'hp_' . $this->name . '_vendor' ) ) );

		// Get listing ID.
		$listing_id = 0;

		if ( false !== $vendor ) {
			$listing_id = hp_get_post_id(
				[
					'post_type'   => hp_prefix( $this->name ),
					'post_status' => 'publish',
					'author'      => $vendor->ID,
				]
			);
		}

		// Redirect user.
		if ( 0 === $listing_id ) {
			hp_redirect( home_url() );
		}
	}

	/**
	 * Sets vendor context.
	 *
	 * @param array $context
	 * @return array
	 */
	public function set_vendor_context( $context ) {
		if ( ! isset( $context['vendor'] ) ) {
			if ( is_singular( hp_prefix( $this->name ) ) ) {

				// Get vendor.
				$context['vendor'] = get_userdata( get_post_field( 'post_author', get_queried_object_id() ) );
			} else {

				// Get vendor.
				$context['vendor'] = get_user_by( 'login', sanitize_user( get_query_var( 'hp_' . $this->name . '_vendor' ) ) );

				// Set context.
				$context['column_width']  = 6;
				$context['listing_query'] = new \WP_Query(
					[
						'post_type'      => hp_prefix( $this->name ),
						'author'         => $context['vendor']->ID,
						'post_status'    => 'publish',
						'posts_per_page' => -1,
					]
				);
			}
		}

		return $context;
	}

	/**
	 * Sets archive context.
	 *
	 * @param array $context
	 * @return array
	 */
	public function set_archive_context( $context ) {
		global $wp_query;

		if ( is_page() ) {
			query_posts(
				[
					'post_type'      => hp_prefix( $this->name ),
					'post_status'    => 'publish',
					'paged'          => hp_get_current_page(),
					'posts_per_page' => absint( get_option( hp_prefix( $this->name . 's_per_page' ) ) ),
				]
			);
		}

		$context['listing_query'] = $wp_query;
		$context['column_width']  = 6;

		return $context;
	}

	/**
	 * Updates featured image.
	 *
	 * @param array $args
	 */
	public function update_image( $args ) {
		$images = wp_list_pluck( get_attached_media( 'image', $args['post_id'] ), 'ID' );

		if ( ! empty( $images ) ) {
			set_post_thumbnail( $args['post_id'], reset( $images ) );
		} else {
			delete_post_thumbnail( $args['post_id'] );
		}
	}

	/**
	 * Sets featured image.
	 *
	 * @param string $html
	 * @param int    $post_id
	 * @return string
	 */
	public function set_image( $html, $post_id ) {
		if ( empty( $html ) && get_post_type( $post_id ) === hp_prefix( $this->name ) ) {
			$html = '<img src="' . esc_url( HP_CORE_URL . '/assets/images/placeholders/' . $this->name . '.png' ) . '" alt="' . esc_attr( get_the_title( $post_id ) ) . '" />';
		}

		return $html;
	}

	/**
	 * Renders gallery.
	 *
	 * @param array $args
	 * @return string
	 */
	public function render_gallery( $args = [] ) {
		$output = '';

		// Set default arguments.
		$args = hp_merge_arrays(
			[
				'before'       => '',
				'after'        => '',
				'before_image' => '',
				'after_image'  => '',
			],
			$args
		);

		// Get image IDs.
		$image_ids = wp_list_pluck( get_attached_media( 'image', $this->get_id() ), 'ID' );

		if ( has_post_thumbnail( $this->get_id() ) ) {
			array_unshift( $image_ids, get_post_thumbnail_id( $this->get_id() ) );
		}

		$image_ids = array_unique( $image_ids );

		// Render images.
		if ( ! empty( $image_ids ) ) {
			$output .= $args['before'];

			foreach ( $image_ids as $image_id ) {
				$output .= $args['before_image'];
				$output .= wp_get_attachment_image( $image_id, 'hp_listing__large' );
				$output .= $args['after_image'];
			}

			$output .= $args['after'];
		}

		return $output;
	}

	/**
	 * Gets current category ID.
	 *
	 * @return int
	 */
	private function get_category_id() {
		$category_id = hp_get_array_value( $_GET, 'category' );

		if ( is_tax( hp_prefix( $this->name . '_category' ) ) ) {
			$category_id = get_queried_object_id();
		}

		return absint( $category_id );
	}

	/**
	 * Gets listing categories.
	 *
	 * @param array $args
	 * @return array
	 */
	public function get_categories( $args = [] ) {

		// Set default arguments.
		$args = hp_merge_arrays(
			[
				'taxonomy'   => hp_prefix( $this->name . '_category' ),
				'parent'     => 0,
				'hide_empty' => false,
				'meta_key'   => 'hp_order',
				'orderby'    => 'meta_value_num',
			],
			$args
		);

		// Set parent category.
		if ( is_tax( hp_prefix( $this->name . '_category' ) ) ) {
			$args['parent'] = get_queried_object_id();
		} else {
			$category_id = absint( get_query_var( 'hp_' . $this->name . '_submission_category' ) );

			if ( $category_id > 1 ) {
				$args['parent'] = $category_id;
			}
		}

		return get_terms( $args );
	}

	/**
	 * Gets category count.
	 *
	 * @param int $category_id
	 * @return int
	 */
	protected function get_category_count( $category_id ) {

		// Get child category IDs.
		$child_category_ids = get_terms(
			[
				'taxonomy'   => hp_prefix( $this->name . '_category' ),
				'child_of'   => $category_id,
				'hide_empty' => false,
				'fields'     => 'ids',
			]
		);

		// Get listing IDs.
		$listing_ids = get_posts(
			[
				'post_type'      => hp_prefix( $this->name ),
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'tax_query'      => [
					[
						'taxonomy' => hp_prefix( $this->name . '_category' ),
						'terms'    => array_merge( [ $category_id ], $child_category_ids ),
					],
				],
			]
		);

		return count( $listing_ids );
	}

	/**
	 * Sets category archive context.
	 *
	 * @param array $context
	 * @return array
	 */
	public function set_category_archive_context( $context ) {

		// Get categories.
		$categories = $this->get_categories();

		// Set category count.
		foreach ( $categories as $category_index => $category ) {
			$categories[ $category_index ]->count = $this->get_category_count( $category->term_id );
		}

		// Set template context.
		$context['categories']   = $categories;
		$context['column_width'] = 4;

		return $context;
	}

	/**
	 * Renders category image.
	 *
	 * @param int $category_id
	 * @return string
	 */
	public function render_category_image( $category_id ) {
		$output = '';

		// Get image ID.
		$image_id = absint( get_term_meta( $category_id, 'hp_image', true ) );

		// Render image HTML.
		$output = wp_get_attachment_image( $image_id, 'hp_listing__medium' );

		if ( '' === $output ) {
			$output = '<img src="' . esc_url( HP_CORE_URL . '/assets/images/placeholders/category.png' ) . '" alt="" />';
		}

		return $output;
	}

	/**
	 * Renders category.
	 *
	 * @param array $args
	 */
	public function render_category( $args = [] ) {
		$output = '';

		// Set default arguments.
		$args = hp_merge_arrays(
			[
				'before' => '',
				'after'  => '',
			],
			$args
		);

		// Get category IDs.
		$category_ids = wp_get_post_terms( $this->get_id(), hp_prefix( $this->name . '_category' ), [ 'fields' => 'ids' ] );

		// Render categories.
		if ( ! empty( $category_ids ) ) {
			$output .= $args['before'];
			$output .= rtrim( get_term_parents_list( reset( $category_ids ), hp_prefix( $this->name . '_category' ), [ 'separator' => ' / ' ] ), ' / ' );
			$output .= $args['after'];
		}

		return $output;
	}

	/**
	 * Renders category filter.
	 *
	 * @return string
	 */
	public function render_category_filter() {

		// Get category IDs.
		$category_ids = [];

		$category_id = $this->get_category_id();

		if ( 0 !== $category_id ) {

			// Get parent categories.
			$category_ids = array_merge( [ $category_id ], get_ancestors( $category_id, hp_prefix( $this->name . '_category' ), 'taxonomy' ) );

			// Get child categories.
			$category_ids = array_merge( $category_ids, wp_list_pluck( get_terms( hp_prefix( $this->name . '_category' ), [ 'parent' => $category_id ] ), 'term_id' ) );
		} else {

			// Get top-level categories.
			$category_ids = wp_list_pluck( get_terms( hp_prefix( $this->name . '_category' ), [ 'parent' => 0 ] ), 'term_id' );
		}

		// Get categories.
		$categories = get_terms(
			[
				'taxonomy'   => hp_prefix( $this->name . '_category' ),
				'include'    => $category_ids,
				'hide_empty' => false,
				'meta_key'   => 'hp_order',
				'orderby'    => 'meta_value_num',
				'order'      => 'ASC',
			]
		);

		// Set menu items.
		$items = [
			0 => [
				'name'   => esc_html__( 'All Categories', 'hivepress' ),
				'parent' => null,
				'url'    => home_url(
					'?' . http_build_query(
						array_merge(
							$_GET,
							[
								'category'  => 0,
								'post_type' => hp_prefix( $this->name ),
							]
						)
					)
				),
			],
		];

		foreach ( $categories as $category ) {
			$items[ $category->term_id ] = [
				'name'   => $category->name,
				'parent' => $category->parent,
				'url'    => home_url(
					'?' . http_build_query(
						array_merge(
							$_GET,
							[
								'category'  => $category->term_id,
								'post_type' => hp_prefix( $this->name ),
							]
						)
					)
				),
			];
		}

		return hivepress()->template->render_submenu( $items, $category_id );
	}

	/**
	 * Adds admin columns.
	 *
	 * @param array $columns
	 * @return array
	 */
	public function add_admin_columns( $columns ) {
		return array_merge(
			array_slice( $columns, 0, 2, true ),
			[
				'category' => esc_html__( 'Category', 'hivepress' ),
			],
			array_slice( $columns, 2, null, true )
		);
	}

	/**
	 * Renders admin columns.
	 *
	 * @param string $column
	 * @param int    $post_id
	 */
	public function render_admin_columns( $column, $post_id ) {
		if ( 'category' === $column ) {
			$title = '&mdash;';

			// Get category ID.
			if ( get_post_type( $post_id ) === hp_prefix( $this->name ) ) {
				$category_ids = wp_get_post_terms( $post_id, hp_prefix( $this->name . '_category' ), [ 'fields' => 'ids' ] );
				$category_id  = absint( reset( $category_ids ) );
			} else {
				$category_id = absint( get_post_meta( $post_id, 'hp_category', true ) );
			}

			if ( 0 !== $category_id ) {

				// Get category.
				$category = get_term( $category_id, hp_prefix( $this->name . '_category' ) );

				if ( ! is_null( $category ) ) {

					// Set category name.
					$title = $category->name;
				}
			}

			echo esc_html( $title );
		}
	}

	/**
	 * Sets admin actions.
	 *
	 * @param array   $actions
	 * @param WP_Post $post
	 * @return array
	 */
	public function set_admin_actions( $actions, $post ) {
		if ( hp_prefix( $this->name ) === $post->post_type ) {
			unset( $actions['inline hide-if-no-js'] );
		}

		return $actions;
	}

	/**
	 * Removes meta boxes.
	 */
	public function remove_meta_boxes() {
		global $pagenow, $post;

		if ( 'post.php' === $pagenow && hp_prefix( $this->name ) === $post->post_type ) {

			// Get category IDs.
			$category_ids = wp_get_post_terms( $post->ID, hp_prefix( $this->name . '_category' ), [ 'fields' => 'ids' ] );

			// Remove meta boxes.
			foreach ( $this->attributes as $attribute_id => $attribute ) {
				if ( 'taxonomy' === $attribute['type'] && ! empty( $attribute['categories'] ) && count( array_intersect( $category_ids, $attribute['categories'] ) ) === 0 ) {
					remove_meta_box( hp_prefix( $this->name . '_' . $attribute_id ) . 'div', hp_prefix( $this->name ), 'side' );
				}
			}
		}
	}
}
