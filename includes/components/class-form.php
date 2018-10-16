<?php
namespace HivePress;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages forms.
 *
 * @class Form
 */
class Form extends Component {

	/**
	 * Array of forms.
	 *
	 * @var array
	 */
	private $forms = [];

	/**
	 * Array of the form messages.
	 *
	 * @var array
	 */
	private $messages = [];

	/**
	 * Form AJAX response.
	 *
	 * @var string
	 */
	private $response = '';

	/**
	 * Class constructor.
	 *
	 * @param array $settings
	 */
	public function __construct( $settings ) {
		parent::__construct( $settings );

		// Initialize form messages.
		add_action( 'init', [ $this, 'init_messages' ] );

		// Initialize forms.
		add_action( 'hivepress/component/init_forms', [ $this, 'init_forms' ], 10, 2 );

		// Submit form.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			add_action( 'wp_ajax_hp_form_submit', [ $this, 'submit_form' ] );
			add_action( 'wp_ajax_nopriv_hp_form_submit', [ $this, 'submit_form' ] );
		} else {
			add_action( 'wp', [ $this, 'submit_form' ] );
		}

		// Manage file uploads.
		add_action( 'hivepress/form/submit_form/form__upload_file', [ $this, 'upload_file' ] );
		add_action( 'hivepress/form/submit_form/form__delete_file', [ $this, 'delete_file' ] );
		add_action( 'hivepress/form/submit_form/form__sort_files', [ $this, 'sort_files' ] );

		// Set field options.
		add_filter( 'hivepress/form/field_args', [ $this, 'set_field_options' ] );
	}

	/**
	 * Initializes form messages.
	 */
	public function init_messages() {
		if ( session_status() === PHP_SESSION_NONE ) {
			session_start();
		}

		if ( ! isset( $_SESSION['hp_form_messages'] ) ) {
			$_SESSION['hp_form_messages'] = [];
		}

		$this->messages = $_SESSION['hp_form_messages'];
	}

	/**
	 * Sets form messages.
	 *
	 * @param string $form_id
	 * @param array  $messages
	 */
	private function set_messages( $form_id = 'default', $messages ) {
		$this->messages[ $form_id ]   = $messages;
		$_SESSION['hp_form_messages'] = $this->messages;
	}

	/**
	 * Clears form messages.
	 *
	 * @param string $form_id
	 */
	public function clear_messages( $form_id = 'default' ) {
		$this->set_messages( $form_id, [] );
	}

	/**
	 * Gets form messages.
	 *
	 * @param string $form_id
	 * @return array
	 */
	public function get_messages( $form_id = 'default' ) {
		return hp_get_array_value( $this->messages, $form_id, [] );
	}

	/**
	 * Adds form message.
	 *
	 * @param string $form_id
	 * @param array  $args
	 */
	public function add_message( $form_id = 'default', $args ) {
		$this->messages[ $form_id ][] = $args;
		$_SESSION['hp_form_messages'] = $this->messages;
	}

	/**
	 * Adds error message.
	 *
	 * @param string $text
	 */
	public function add_error( $text ) {
		$this->add_message(
			'default',
			[
				'type' => 'error',
				'text' => $text,
			]
		);
	}

	/**
	 * Renders form messages.
	 *
	 * @param string $form_id
	 * @param array  $args
	 * @return string
	 */
	private function render_messages( $form_id = 'default', $args = [] ) {
		$output = '';

		// Set default arguments.
		$args = hp_merge_arrays(
			[
				'before'         => '',
				'after'          => '',
				'before_message' => '',
				'after_message'  => '',
			],
			$args
		);

		// Render messages.
		$messages = $this->get_messages( $form_id );

		if ( ! empty( $messages ) ) {
			$output .= '<div class="hp-form__messages">' . $args['before'];

			foreach ( $messages as $message ) {

				// Get type slug.
				$message['type_slug'] = preg_replace( '/[_]+/', '-', $message['type'] );

				// Render message.
				$output .= hp_replace_placeholders( $message, '<div class="hp-form__message hp-form__message--%type_slug%">' . $args['before_message'] );
				$output .= esc_html( $message['text'] );
				$output .= hp_replace_placeholders( $message, $args['after_message'] . '</div>' );
			}

			$output .= $args['after'] . '</div>';
		}

		// Clear messages.
		$this->clear_messages( $form_id );

		return $output;
	}

	/**
	 * Sets AJAX response.
	 *
	 * @param string $response
	 */
	public function set_response( $response ) {
		$this->response = $response;
	}

	/**
	 * Initializes forms.
	 *
	 * @param array  $forms
	 * @param string $component_name
	 */
	public function init_forms( $forms, $component_name ) {
		$this->forms = array_merge(
			$this->forms,
			array_combine(
				array_map(
					function( $form_name ) use ( $component_name ) {
						return $component_name . '__' . $form_name;
					},
					array_keys( $forms )
				),
				$forms
			)
		);
	}

	/**
	 * Submits form.
	 */
	public function submit_form() {

		// Get form ID.
		$form_id = hp_get_array_value( $_POST, 'form_id', '' );

		if ( '' !== $form_id && isset( $this->forms[ $form_id ] ) ) {

			// Set AJAX arguments.
			$is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

			$ajax_args = [
				'status' => 'error',
			];

			// Get and filter form arguments.
			$form = apply_filters( "hivepress/form/form_args/{$form_id}", $this->forms[ $form_id ] );

			// Get nonce and capability.
			$nonce      = hp_get_array_value( $_POST, '_wpnonce', '' );
			$capability = hp_get_array_value( $form, 'capability', 'read' );

			if ( wp_verify_nonce( $nonce, $form_id ) && ( ( 'login' === $capability && ! is_user_logged_in() ) || current_user_can( $capability ) ) ) {

				// Validate form.
				$values = $this->validate_form( $form_id );

				if ( false !== $values ) {

					// Clear messages.
					$this->clear_messages( $form_id );
					$this->clear_messages();

					// Submit form.
					do_action( "hivepress/form/submit_form/{$form_id}", $values );

					// Set messages.
					$this->set_messages( $form_id, $this->get_messages() );
					$this->clear_messages();

					if ( count( $this->get_messages( $form_id ) ) === 0 ) {

						// Add success message.
						if ( isset( $form['success_message'] ) ) {
							$this->add_message(
								$form_id,
								[
									'text' => $form['success_message'],
									'type' => 'success',
								]
							);
						}

						if ( $is_ajax ) {

							// Set AJAX status.
							$ajax_args['status'] = 'success';

							// Set redirect URL.
							if ( isset( $form['success_redirect'] ) ) {
								$ajax_args['redirect'] = $form['success_redirect'];
							}
						} else {

							// Redirect user.
							hp_redirect( hp_get_array_value( $form, 'success_redirect' ) );
						}
					}
				}
			}

			// Send AJAX response.
			if ( $is_ajax ) {
				wp_send_json(
					hp_merge_arrays(
						$ajax_args,
						[
							'messages' => $this->render_messages( $form_id ),
							'response' => $this->response,
						]
					)
				);
			}
		}
	}

	/**
	 * Validates form.
	 *
	 * @param string $form_id
	 * @return mixed
	 */
	public function validate_form( $form_id ) {
		if ( isset( $this->forms[ $form_id ] ) ) {
			$values = [];

			// Get and filter form arguments.
			$args = apply_filters(
				"hivepress/form/form_args/{$form_id}",
				hp_merge_arrays(
					[
						'method'  => 'POST',
						'captcha' => false,
						'fields'  => [],
					],
					$this->forms[ $form_id ]
				)
			);

			// Get default values.
			$defaults = $_GET;

			if ( strtoupper( $args['method'] ) === 'POST' ) {
				$defaults = $_POST;
			}

			// Filter form fields.
			$args['fields'] = apply_filters( "hivepress/form/form_fields/{$form_id}", $args['fields'], $defaults );

			// Clear form messages.
			$this->clear_messages( $form_id );
			$this->clear_messages();

			foreach ( $args['fields'] as $field_id => $field ) {

				// Validate field.
				$value = $this->validate_field( $field, hp_get_array_value( $defaults, $field_id ) );

				// Set field value.
				if ( false !== $value ) {
					$values[ $field_id ] = $value;
				}
			}

			// Verify captcha.
			$captcha_secret_key = get_option( 'hp_recaptcha_secret_key' );

			if ( ( $args['captcha'] || in_array( $form_id, (array) get_option( 'hp_recaptcha_forms' ), true ) ) && '' !== $captcha_secret_key ) {
				$captcha_response = hp_get_remote_json(
					'https://www.google.com/recaptcha/api/siteverify?' . http_build_query(
						[
							'secret'   => $captcha_secret_key,
							'response' => hp_get_array_value( $_POST, 'g-recaptcha-response' ),
						]
					)
				);

				if ( empty( $captcha_response ) || ! hp_get_array_value( $captcha_response, 'success', false ) ) {
					$this->add_error( esc_html__( 'Captcha is invalid.', 'hivepress' ) );
				}
			}

			// Set form messages.
			$this->set_messages( $form_id, $this->get_messages() );
			$this->clear_messages();

			// Return field values.
			if ( count( $this->get_messages( $form_id ) ) === 0 ) {
				return $values;
			}
		}

		return false;
	}

	/**
	 * Renders form.
	 *
	 * @param string $form_id
	 * @param array  $args
	 * @param array  $values
	 * @return string
	 */
	public function render_form( $form_id, $args = [], $values = [] ) {
		$output = '';

		if ( isset( $this->forms[ $form_id ] ) ) {

			// Get and filter form arguments.
			$args = apply_filters(
				"hivepress/form/form_args/{$form_id}",
				hp_merge_arrays(
					[
						'id'            => $form_id,
						'action'        => hp_get_current_url(),
						'method'        => 'POST',
						'enctype'       => 'multipart/form-data',
						'captcha'       => false,
						'fields'        => [],
						'submit_button' => [
							'name'       => esc_html__( 'Submit', 'hivepress' ),
							'type'       => 'submit',
							'attributes' => [
								'class' => '',
							],
						],
						'attributes'    => [
							'class' => '',
						],
						'before'        => '',
						'after'         => '',
						'before_field'  => '',
						'after_field'   => '',
						'before_submit' => '',
						'after_submit'  => '',
					],
					$this->forms[ $form_id ],
					$args
				)
			);

			$args['slug'] = preg_replace( '/[_]+/', '-', $form_id );

			if ( isset( $args['attributes']['class'] ) ) {
				$args['attributes']['class'] .= ' hp-form hp-form--%slug% hp-js-form';
			}

			if ( isset( $args['submit_button']['attributes']['class'] ) ) {
				$args['submit_button']['attributes']['class'] .= ' hp-form__submit';
			}

			// Filter form values.
			$values = apply_filters( "hivepress/form/form_values/{$form_id}", $values );

			// Filter form fields.
			$args['fields'] = apply_filters( "hivepress/form/form_fields/{$form_id}", $args['fields'], $values );

			// Sort form fields.
			$args['fields'] = hp_sort_array( $args['fields'] );

			// Add parent form fields.
			if ( isset( $args['parent'] ) ) {
				list($component_name, $form_name) = explode( '__', $form_id );

				if ( ! is_array( $args['parent'] ) ) {
					$args['parent'] = [ $args['parent'] ];
				}

				foreach ( $args['parent'] as $parent_form_name ) {
					$parent_values = $this->validate_form( $component_name . '__' . $parent_form_name );

					if ( false !== $parent_values ) {
						foreach ( $parent_values as $value_id => $value ) {
							if ( ! isset( $args['fields'][ $value_id ] ) ) {
								if ( is_array( $value ) ) {
									foreach ( $value as $option_id => $option_value ) {
										$args['fields'][ $value_id . '[' . $option_id . ']' ] = [
											'type'    => 'hidden',
											'default' => $option_value,
										];
									}
								} else {
									$args['fields'][ $value_id ] = [
										'type'    => 'hidden',
										'default' => $value,
									];
								}
							}
						}
					}
				}
			}

			// Check empty form.
			if ( count(
				array_filter(
					$args['fields'],
					function( $field ) {
						return 'hidden' !== $field['type'];
					}
				)
			) === 0 ) {
				$args['attributes']['class'] .= ' hp-form--empty';
			}

			// Get HTML attributes.
			$attributes = hp_replace_placeholders( $args, hp_html_attributes( $args['attributes'] ) );

			// Render form HTML.
			$output .= '<form action="' . esc_url( $args['action'] ) . '" method="' . esc_attr( $args['method'] ) . '" enctype="' . esc_attr( $args['enctype'] ) . '" ' . $attributes . '>';

			$output .= hp_replace_placeholders( $args, $args['before'] );

			if ( strtoupper( $args['method'] ) === 'POST' ) {
				$output .= '<div class="hp-js-messages">' . $this->render_messages( $form_id ) . '</div>';
			} else {
				$this->clear_messages( $form_id );
			}

			foreach ( $args['fields'] as $field_id => $field ) {

				// Get field value.
				$value = hp_get_array_value( $values, $field_id );

				if ( strtoupper( $args['method'] ) === 'POST' ) {
					$value = hp_get_array_value( $_POST, $field_id, $value );
				} else {
					$value = hp_get_array_value( $_GET, $field_id, $value );
				}

				// Render field HTML.
				$before_field = '<div class="hp-form__field-wrapper hp-form__field-wrapper--%type_slug%">';

				if ( isset( $field['name'] ) ) {
					$before_field .= '<label for="%id%" class="hp-form__field-label">%name%</label>';
				}

				$output .= $this->render_field(
					$field_id,
					hp_merge_arrays(
						[
							'id'         => $form_id . '__' . $field_id,
							'attributes' => [ 'class' => 'hp-form__field hp-form__field--%type_slug%' ],
							'before'     => $before_field . $args['before_field'],
							'after'      => $args['after_field'] . '</div>',
						],
						$field
					),
					$value
				);
			}

			// Render captcha.
			$captcha_site_key = get_option( 'hp_recaptcha_site_key' );

			if ( ( $args['captcha'] || in_array( $form_id, (array) get_option( 'hp_recaptcha_forms' ), true ) ) && '' !== $captcha_site_key ) {
				$output .= '<div class="g-recaptcha" data-sitekey="' . esc_attr( $captcha_site_key ) . '"></div>';
			}

			// Render submit button.
			if ( false !== $args['submit_button'] ) {
				$output .= '<div class="hp-form__submit-wrapper">' . $args['before_submit'];
				$output .= $this->render_field( $form_id, $args['submit_button'] );
				$output .= $args['after_submit'] . '</div>';
			}

			// Render action and nonce.
			if ( strtoupper( $args['method'] ) === 'POST' ) {
				$output .= $this->render_field(
					'action',
					[
						'type'    => 'hidden',
						'default' => 'hp_form_submit',
					]
				);

				$output .= $this->render_field(
					'form_id',
					[
						'type'    => 'hidden',
						'default' => $form_id,
					]
				);

				$output .= $this->render_field(
					'_wpnonce',
					[
						'type'    => 'hidden',
						'default' => wp_create_nonce( $form_id ),
					]
				);
			}

			$output .= hp_replace_placeholders( $args, $args['after'] );

			$output .= '</form>';
		}

		return $output;
	}

	/**
	 * Renders form link.
	 *
	 * @param string $form_id
	 * @param array  $args
	 * @param array  $values
	 * @return string
	 */
	public function render_link( $form_id, $args = [], $values = [] ) {
		$output = '';

		if ( isset( $this->forms[ $form_id ] ) ) {

			// Get form.
			$form = apply_filters( "hivepress/form/form_args/{$form_id}", $this->forms[ $form_id ] );

			// Set default arguments.
			$args = hp_merge_arrays(
				[
					'text'       => '',
					'attributes' => [
						'class'     => '',
						'data-type' => '',
					],
				],
				$args
			);

			// Set attributes.
			$args['attributes']['class']     .= ' hp-js-link';
			$args['attributes']['data-type'] .= ' ajax';

			if ( isset( $form['submit_button'] ) ) {
				$args['attributes']['title'] = $form['submit_button']['name'];
			}

			if ( isset( $form['submit_button']['attributes'] ) ) {
				$args['attributes']['data-name']  = hp_get_array_value( $form['submit_button']['attributes'], 'data-name' );
				$args['attributes']['data-state'] = hp_get_array_value( $form['submit_button']['attributes'], 'data-state' );
			}

			// Filter form values.
			$values = apply_filters( "hivepress/form/form_values/{$form_id}", $values );

			// Sanitize form values.
			$field_ids = array_keys( apply_filters( "hivepress/form/form_fields/{$form_id}", hp_get_array_value( $form, 'fields', [] ), $values ) );

			$values = array_filter(
				$values,
				function( $field_id ) use ( $field_ids ) {
					return in_array( $field_id, $field_ids, true );
				},
				ARRAY_FILTER_USE_KEY
			);

			// Add action and nonce.
			$values['action']   = 'hp_form_submit';
			$values['form_id']  = $form_id;
			$values['_wpnonce'] = wp_create_nonce( $form_id );

			// Set form values.
			$args['attributes']['data-json'] = wp_json_encode( $values );

			// Render link.
			$output .= '<a href="#" ' . hp_html_attributes( $args['attributes'] ) . '>' . hp_sanitize_html( $args['text'] ) . '</a>';
		}

		return $output;
	}

	/**
	 * Sanitizes field value.
	 *
	 * @param array $args
	 * @param mixed $value
	 * @return mixed
	 */
	private function sanitize_field( $args, $value ) {

		// Clear field value.
		$value = wp_unslash( $value );

		if ( ! is_array( $value ) ) {
			$value = trim( $value );

			// Cut field value.
			$max_length = false;

			if ( isset( $args['max_length'] ) ) {
				$max_length = $args['max_length'];
			} elseif ( 'email' === $args['type'] ) {
				$max_length = 254;
			} elseif ( 'password' === $args['type'] ) {
				$max_length = 64;
			}

			if ( false !== $max_length && $max_length < strlen( $value ) ) {
				$value = substr( $value, 0, $max_length );
			}
		}

		// Sanitize field value.
		switch ( $args['type'] ) {

			// Select.
			case 'select':
			case 'radio':
				if ( is_array( $value ) ) {
					$value = reset( $value );
				}

				$value = sanitize_text_field( $value );

				break;

			// Number.
			case 'number':
				if ( '' !== $value ) {
					$value = intval( $value );
				}

				break;

			// Number range.
			case 'number_range':
				if ( ! is_array( $value ) ) {
					$value = [];
				}

				$value = array_map(
					function( $number ) {
						if ( '' !== $number ) {
							$number = intval( $number );
						}

						return $number;
					},
					array_filter(
						$value,
						function( $number ) {
							return is_numeric( $number ) || '' === $number;
						}
					)
				);

				if ( ! empty( $value ) ) {
					$value = [ reset( $value ), end( $value ) ];
				} else {
					$value = [ '', '' ];
				}

				break;

			// Checkboxes.
			case 'checkboxes':
				if ( ! is_array( $value ) ) {
					$value = [];
				} else {
					$value = array_map( 'sanitize_text_field', array_filter( $value ) );
				}

				break;

			// File upload.
			case 'file_upload':
				if ( hp_get_array_value( $args, 'multiple', false ) ) {
					if ( ! is_array( $value ) ) {
						$value = [];
					} else {
						$value = array_map( 'absint', array_filter( $value ) );
					}
				} else {
					$value = absint( $value );
				}

				$attachments = get_posts(
					[
						'post_type'      => 'attachment',
						'post_status'    => 'any',
						'post__in'       => array_merge( [ 0 ], (array) $value ),
						'orderby'        => 'post__in',
						'posts_per_page' => -1,
					]
				);

				$attachment_ids = [];

				foreach ( $attachments as $attachment ) {
					$post_id = hp_get_post_id(
						[
							'post_type'   => 'any',
							'post_status' => [ 'auto-draft', 'draft', 'publish' ],
							'post__in'    => [ $attachment->post_parent ],
							'author'      => get_current_user_id(),
						]
					);

					if ( ( absint( $attachment->post_parent ) === 0 && absint( $attachment->post_author ) === get_current_user_id() ) || 0 !== $post_id ) {
						$attachment_ids[] = $attachment->ID;
					}
				}

				if ( hp_get_array_value( $args, 'multiple', false ) ) {
					$value = $attachment_ids;
				} else {
					if ( ! empty( $attachment_ids ) ) {
						$value = reset( $attachment_ids );
					} else {
						$value = '';
					}
				}

				break;

			// File select.
			case 'file_select':
				$attachment_id = hp_get_post_id(
					[
						'post_type'   => 'attachment',
						'post_status' => 'any',
						'post__in'    => [ absint( $value ) ],
					]
				);

				if ( 0 !== $attachment_id ) {
					$value = $attachment_id;
				} else {
					$value = '';
				}

				break;

			// Textarea.
			case 'textarea':
				$value = sanitize_textarea_field( $value );

				break;

			// Other types.
			case 'text':
			case 'email':
			case 'search':
			case 'checkbox':
			case 'file':
			case 'hidden':
				$value = sanitize_text_field( $value );

				break;
		}

		// Get field type.
		$field_type = $args['type'];

		// Filter field value.
		$value = apply_filters( "hivepress/form/field_value/{$field_type}", $value, $args );

		return $value;
	}

	/**
	 * Validates form field.
	 *
	 * @param array $args
	 * @param mixed $value
	 * @return mixed
	 */
	public function validate_field( $args, $value ) {

		// Get and filter field arguments.
		$args = apply_filters(
			'hivepress/form/field_args',
			hp_merge_arrays(
				[
					'type'     => 'text',
					'name'     => '',
					'required' => false,
				],
				$args
			)
		);

		// Sanitize field value.
		$value = $this->sanitize_field( $args, $value );

		// Check required field.
		if ( $args['required'] && ( '' === $value || ( is_array( $value ) && count( array_filter( $value, 'strlen' ) ) === 0 ) ) ) {
			$this->add_error( sprintf( esc_html__( '"%s is required.', 'hivepress' ), $args['name'] ) );
		} else {
			$error_count = count( $this->get_messages() );

			// Validate field options.
			if ( isset( $args['options'] ) && '' !== $value && count( array_diff( (array) $value, array_keys( $args['options'] ) ) ) > 0 ) {
				$this->add_error( sprintf( esc_html__( '"%s contains invalid value.', 'hivepress' ), $args['name'] ) );
			}

			// Validate field value.
			switch ( $args['type'] ) {

				// Checkbox.
				case 'checkbox':
					if ( ! in_array( $value, [ '', '1' ], true ) ) {
						$this->add_error( sprintf( esc_html__( '"%s contains invalid value.', 'hivepress' ), $args['name'] ) );
					}

					break;

				// Number.
				case 'number':
					if ( ! is_numeric( $value ) && '' !== $value ) {
						$this->add_error( sprintf( esc_html__( '"%s contains invalid number.', 'hivepress' ), $args['name'] ) );
					}

					break;

				// Email.
				case 'email':
					if ( '' !== $value && ! is_email( $value ) ) {
						$this->add_error( sprintf( esc_html__( '"%s contains invalid email address.', 'hivepress' ), $args['name'] ) );
					}

					break;
			}

			// Fires when field is being validated.
			do_action( 'hivepress/form/validate_field', $args, $value );

			// Return field value.
			if ( count( $this->get_messages() ) === $error_count ) {
				return $value;
			}
		}

		return false;
	}

	/**
	 * Renders form field.
	 *
	 * @param string $field_id
	 * @param array  $args
	 * @param mixed  $value
	 * @return string
	 */
	public function render_field( $field_id, $args, $value = null ) {
		$output = '';

		// Get and filter field arguments.
		$args = apply_filters(
			'hivepress/form/field_args',
			hp_merge_arrays(
				[
					'id'         => $field_id,
					'type'       => 'text',
					'name'       => '',
					'default'    => '',
					'attributes' => [],
					'before'     => '',
					'after'      => '',
				],
				$args
			)
		);

		$args['type_slug'] = preg_replace( '/[_]+/', '-', $args['type'] );

		// Get default field value.
		if ( is_null( $value ) ) {
			$value = $args['default'];
		}

		// Sanitize field value.
		$value = $this->sanitize_field( $args, $value );

		// Set field attributes.
		if ( isset( $args['placeholder'] ) ) {
			$args['attributes']['placeholder'] = $args['placeholder'];
		}

		if ( hp_get_array_value( $args, 'required', false ) && 'hidden' !== $args['type'] ) {
			$args['attributes']['required'] = 'required';
		}

		if ( isset( $args['max_length'] ) ) {
			$args['attributes']['maxlength'] = $args['max_length'];
		}

		// Render HTML attributes.
		$attributes = hp_replace_placeholders( $args, hp_html_attributes( $args['attributes'] ) );

		// Render field HTML.
		if ( 'hidden' !== $args['type'] ) {
			$output .= hp_replace_placeholders( $args, $args['before'] );
		}

		switch ( $args['type'] ) {

			// Select.
			case 'select':
				$output .= '<select name="' . esc_attr( $field_id ) . '" id="' . esc_attr( $args['id'] ) . '" ' . $attributes . '>';

				foreach ( $args['options'] as $option_id => $option_label ) {
					$output .= '<option value="' . esc_attr( $option_id ) . '" ' . selected( $value, $option_id, false ) . '>' . esc_html( $option_label ) . '</option>';
				}

				$output .= '</select>';

				break;

			// Radio.
			case 'radio':
				$output .= '<div ' . $attributes . '>';

				foreach ( $args['options'] as $option_id => $option_label ) {
					$output .= '<label for="' . esc_attr( $args['id'] . '_' . $option_id ) . '"><input type="' . esc_attr( $args['type'] ) . '" name="' . esc_attr( $field_id ) . '" id="' . esc_attr( $args['id'] . '_' . $option_id ) . '" value="' . esc_attr( $option_id ) . '" ' . checked( $value, $option_id, false ) . ' ' . $attributes . '>' . esc_html( $option_label ) . '</label>';
				}

				$output .= '</div>';

				break;

			// Checkbox.
			case 'checkbox':
				$output .= '<label for="' . esc_attr( $args['id'] ) . '"><input type="' . esc_attr( $args['type'] ) . '" name="' . esc_attr( $field_id ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . esc_attr( hp_get_array_value( $args, 'value', '1' ) ) . '" ' . checked( $value, 1, false ) . ' ' . $attributes . '>' . hp_sanitize_html( hp_get_array_value( $args, 'label' ) ) . '</label>';

				break;

			// Checkboxes.
			case 'checkboxes':
				$output .= '<div ' . $attributes . '>';

				foreach ( $args['options'] as $option_id => $option_label ) {
					$option_value = null;

					if ( in_array( $option_id, $value, true ) ) {
						$option_value = '1';
					}

					$output .= $this->render_field(
						$field_id . '[' . $option_id . ']',
						[
							'id'    => $args['id'] . '_' . $option_id,
							'label' => $option_label,
							'type'  => 'checkbox',
							'value' => $option_id,
						],
						$option_value
					);
				}

				$output .= '</div>';

				break;

			// Number range.
			case 'number_range':
				$output .= '<div ' . $attributes . '>';

				$output .= $this->render_field(
					$field_id . '[min]',
					[
						'id'          => $args['id'] . '_min',
						'type'        => 'number',
						'placeholder' => esc_html__( 'Min', 'hivepress' ),
					],
					reset( $value )
				);

				$output .= $this->render_field(
					$field_id . '[max]',
					[
						'id'          => $args['id'] . '_max',
						'type'        => 'number',
						'placeholder' => esc_html__( 'Max', 'hivepress' ),
					],
					end( $value )
				);

				$output .= '</div>';

				break;

			// File upload.
			case 'file_upload':
				$output .= '<div ' . $attributes . '>';

				$sortable = hp_get_array_value( $attributes, 'multiple', false ) ? 'hp-js-sortable' : '';

				$output .= '<div class="hp-row ' . esc_attr( $sortable ) . '" data-json="' . esc_attr(
					wp_json_encode(
						[
							'form_id'  => 'form__sort_files',
							'_wpnonce' => wp_create_nonce( 'form__sort_files' ),
						]
					)
				) . '">';

				foreach ( (array) $value as $attachment_id ) {
					$image = wp_get_attachment_image( $attachment_id );

					if ( '' !== $image ) {
						$output .= '<div class="hp-col-sm-2 hp-col-xs-4">' . $image;

						$output .= $this->render_link(
							'form__delete_file',
							[
								'text'       => '<i class="fas fa-times"></i>',
								'attributes' => [
									'data-type' => 'remove',
								],
							],
							[ 'attachment_id' => $attachment_id ]
						);

						$output .= $this->render_field(
							'attachment_ids[]',
							[
								'type'    => 'hidden',
								'default' => $attachment_id,
							]
						);

						$output .= '</div>';
					}
				}

				$output .= '</div>';
				$output .= '<button type="button">' . esc_html( hp_get_array_value( $args, 'label', esc_html__( 'Select File', 'hivepress' ) ) );

				$output .= $this->render_field(
					$field_id,
					[
						'id'         => $args['id'],
						'type'       => 'file',
						'attributes' => [
							'class'     => 'hp-js-file-upload',
							'multiple'  => hp_get_array_value( $args, 'multiple', false ) ? 'multiple' : null,
							'accept'    => '.' . implode( ',.', hp_get_array_value( $args, 'extensions', [] ) ),
							'data-json' => wp_json_encode(
								[
									'form_id'  => 'form__upload_file',
									'_wpnonce' => wp_create_nonce( 'form__upload_file' ),
								]
							),
						],
					]
				);

				$output .= '</button>';
				$output .= '</div>';

				break;

			// File select.
			case 'file_select':
				$output .= '<div ' . $attributes . '>';
				$output .= '<div>';

				if ( '' !== $value ) {
					$image = wp_get_attachment_image( $value );

					if ( '' !== $image ) {
						$output .= $image;
					}
				}

				$output .= $this->render_field(
					$field_id,
					[
						'id'      => $args['id'],
						'type'    => 'hidden',
						'default' => $value,
					]
				);

				$output .= '<a href="#" class="hp-js-link" data-type="remove"><span class="dashicons dashicons-no-alt"></span></a>';
				$output .= '</div>';

				$output .= '<button type="button" class="button hp-js-file-select">' . esc_html( hp_get_array_value( $args, 'label', esc_html__( 'Select File', 'hivepress' ) ) ) . '</button>';
				$output .= '</div>';

				break;

			// Textarea.
			case 'textarea':
				$output .= '<textarea name="' . esc_attr( $field_id ) . '" id="' . esc_attr( $args['id'] ) . '" ' . $attributes . '>' . esc_textarea( $value ) . '</textarea>';

				break;

			// Hidden.
			case 'hidden':
				$output .= '<input type="' . esc_attr( $args['type'] ) . '" name="' . esc_attr( $field_id ) . '" value="' . esc_attr( $value ) . '" ' . $attributes . '>';

				break;

			// Submit.
			case 'submit':
				$output .= '<input type="' . esc_attr( $args['type'] ) . '" value="' . esc_attr( $args['name'] ) . '" ' . $attributes . '>';

				break;

			// Other types.
			case 'number':
			case 'text':
			case 'email':
			case 'search':
			case 'password':
			case 'file':
				$output .= '<input type="' . esc_attr( $args['type'] ) . '" name="' . esc_attr( $field_id ) . '" id="' . esc_attr( $args['id'] ) . '" value="' . esc_attr( $value ) . '" ' . $attributes . '>';

				break;

			// Custom types.
			default:
				$field_type = $args['type'];

				$output .= apply_filters( "hivepress/form/field_html/{$field_type}", $output, $field_id, $args, $value );
		}

		if ( 'hidden' !== $args['type'] ) {
			$output .= hp_replace_placeholders( $args, $args['after'] );
		}

		return $output;
	}

	/**
	 * Sets field options.
	 *
	 * @param array $args
	 * @return array
	 */
	public function set_field_options( $args ) {
		if ( isset( $args['options'] ) && ! is_array( $args['options'] ) ) {
			$options = [];

			if ( $args['type'] === 'select' ) {
				$options = [ '' => '&mdash;' ];
			}

			switch ( $args['options'] ) {

				// Posts.
				case 'posts':
					$post_type = get_post_type_object( $args['post_type'] );

					if ( ! is_null( $post_type ) ) {
						$options += wp_list_pluck(
							get_posts(
								[
									'post_type'      => $args['post_type'],
									'post_status'    => 'publish',
									'posts_per_page' => -1,
									'orderby'        => 'title',
									'order'          => 'ASC',
								]
							),
							'post_title',
							'ID'
						);
					}

					break;

				// Terms.
				case 'terms':
					$taxonomy = get_taxonomy( $args['taxonomy'] );

					if ( false !== $taxonomy ) {
						$options += get_terms(
							[
								'taxonomy'   => $args['taxonomy'],
								'fields'     => 'id=>name',
								'hide_empty' => false,
							]
						);
					}

					break;

				// Forms.
				case 'forms':
					$forms = array_filter(
						$this->forms,
						function( $form ) {
							return isset( $form['captcha'] );
						}
					);

					$options += array_combine(
						array_keys( $forms ),
						array_map(
							function( $form, $form_id ) {
								return hp_get_array_value( $form, 'name', $form_id );
							},
							$forms,
							array_keys( $forms )
						)
					);

					break;
			}

			$args['options'] = $options;
		}

		return $args;
	}

	/**
	 * Uploads file.
	 *
	 * @param array $values
	 */
	public function upload_file( $values ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		// Get form.
		$form = hp_get_array_value( $this->forms, $values['parent_form_id'] );

		if ( ! is_null( $form ) && current_user_can( hp_get_array_value( $form, 'capability', 'read' ) ) ) {
			list($component_name, $form_name) = explode( '__', $values['parent_form_id'] );

			// Get file IDs.
			$file_ids = array_keys( $_FILES );

			if ( ! empty( $file_ids ) ) {
				$file_id = reset( $file_ids );

				// Get field.
				$field = hp_get_array_value( hp_get_array_value( $form, 'fields', [] ), $file_id );

				if ( ! is_null( $field ) ) {

					// Get parent post ID.
					$post_id = hp_get_post_id(
						[
							'post_type'   => hp_prefix( $component_name ),
							'post_status' => [ 'auto-draft', 'draft', 'publish' ],
							'post__in'    => [ absint( $values['post_id'] ) ],
							'author'      => get_current_user_id(),
						]
					);

					// Get attachment IDs.
					$attachment_ids = get_posts(
						[
							'post_type'      => 'attachment',
							'post_status'    => 'any',
							'post_parent'    => $post_id,
							'author'         => 0 === $post_id ? get_current_user_id() : null,
							'meta_key'       => 'hp_type',
							'meta_value'     => $file_id,
							'posts_per_page' => -1,
							'fields'         => 'ids',
						]
					);

					// Validate quantity.
					$file_count = hp_get_array_value( $field, 'multiple', false ) ? hp_get_array_value( $field, 'max_files', 10 ) : 2;

					if ( count( $attachment_ids ) >= $file_count ) {
						$this->add_error( esc_html__( 'Maximum number of files exceeded.', 'hivepress' ) );
					} else {

						// Validate extension.
						$file_type       = wp_check_filetype( wp_unslash( $_FILES[ $file_id ]['name'] ) );
						$file_extensions = array_map( 'strtoupper', hp_get_array_value( $field, 'extensions', [] ) );

						if ( ! in_array( strtoupper( $file_type['ext'] ), $file_extensions, true ) ) {
							$this->add_error( sprintf( esc_html__( 'Only %s files are allowed.', 'hivepress' ), implode( ', ', $file_extensions ) ) );
						}
					}

					if ( count( $this->get_messages() ) === 0 ) {

						// Upload file.
						$attachment_id = media_handle_upload( $file_id, $post_id );

						if ( ! is_wp_error( $attachment_id ) ) {

							// Delete attachment.
							if ( ! hp_get_array_value( $field, 'multiple', false ) && ! empty( $attachment_ids ) ) {
								wp_delete_attachment( reset( $attachment_ids ), true );
							}

							// Set order.
							wp_update_post(
								[
									'ID'         => $attachment_id,
									'menu_order' => $attachment_id,
								]
							);

							// Set type.
							update_post_meta( $attachment_id, 'hp_type', $file_id );

							// Get file type.
							$file_type = $this->get_file_type( $attachment_id );

							// Fires when file is uploaded.
							do_action(
								"hivepress/form/upload_file/{$file_type}",
								[
									'attachment_id' => $attachment_id,
									'user_id'       => get_current_user_id(),
									'post_id'       => $post_id,
								]
							);

							// Get image.
							$image = wp_get_attachment_image( $attachment_id );

							// Render image.
							if ( '' !== $image ) {
								$output = '<div class="hp-col-sm-2 hp-col-xs-4">' . $image;

								$output .= $this->render_link(
									'form__delete_file',
									[
										'text'       => '<i class="fas fa-times"></i>',
										'attributes' => [
											'data-type' => 'remove',
										],
									],
									[ 'attachment_id' => $attachment_id ]
								);

								$output .= $this->render_field(
									'attachment_ids[]',
									[
										'type'    => 'hidden',
										'default' => $attachment_id,
									]
								);

								$output .= '</div>';

								// Set AJAX response.
								$this->set_response( $output );
							}
						} else {

							// Add errors.
							foreach ( $attachment_id->get_error_messages() as $error ) {
								$this->add_error( $error );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Deletes file.
	 *
	 * @param array $values
	 */
	public function delete_file( $values ) {

		// Get file type.
		$file_type = $this->get_file_type( $values['attachment_id'] );

		// Get parent post ID.
		$post_id = absint( get_post_field( 'post_parent', $values['attachment_id'] ) );

		// Delete attachment.
		wp_delete_attachment( $values['attachment_id'], true );

		// Fires when file is deleted.
		do_action(
			"hivepress/form/delete_file/{$file_type}",
			array_merge(
				$values,
				[
					'user_id' => get_current_user_id(),
					'post_id' => $post_id,
				]
			)
		);
	}

	/**
	 * Sorts files.
	 *
	 * @param array $values
	 */
	public function sort_files( $values ) {

		// Get file type.
		$file_type = $this->get_file_type( reset( $values['attachment_ids'] ) );

		// Update post order.
		foreach ( $values['attachment_ids'] as $attachment_index => $attachment_id ) {
			wp_update_post(
				[
					'ID'         => $attachment_id,
					'menu_order' => $attachment_index,
				]
			);
		}

		// Fires when files are sorted.
		do_action(
			"hivepress/form/sort_files/{$file_type}",
			array_merge(
				$values,
				[
					'user_id' => get_current_user_id(),
					'post_id' => absint( get_post_field( 'post_parent', reset( $values['attachment_ids'] ) ) ),
				]
			)
		);
	}

	/**
	 * Gets file type.
	 *
	 * @param int $attachment_id
	 * @return string
	 */
	private function get_file_type( $attachment_id ) {
		$file_type = 'user';

		// Set post type.
		$post_id = absint( get_post_field( 'post_parent', $attachment_id ) );

		if ( 0 !== $post_id ) {
			$file_type = hp_unprefix( get_post_type( $post_id ) );
		}

		// Set file type.
		$file_type .= '__' . str_replace( '-', '_', sanitize_title( get_post_meta( $attachment_id, 'hp_type', true ) ) );

		return $file_type;
	}
}
