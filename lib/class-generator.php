<?php

namespace WXR_Generator;

use DateTime;
use DateTimeZone;
use Oxymel;
use Exception;

require_once __DIR__ . '/class-oxymel.php';

/**
 * Class Generator
 *
 * @package WXR_Generator
 */
class Generator {

	/**
	 * The version of the WXR
	 *
	 * @var string
	 */
	protected $wxr_version = '1.2';

	/**
	 * @var Writer_Interface $writer Instance of the writer interface to which to output the WXR.
	 */
	protected $writer;

	/**
	 * @var array $schema The schema for the WXR.
	 */
	protected $schema;

	public function __construct( Writer_Interface $writer ) {
		$this->writer = $writer;
		$this->schema = $this->get_schema();
	}

	/**
	 * Initializes the WXR header and site meta.
	 *
	 * @param array $site_meta Site meta information.
	 *
	 * @example lib/schema.json See 'site_meta' type for all available fields.
	 *
	 * @throws \OxymelException
	 */
	public function initialize( $site_meta = array() ) {
		$this->write_header();

		$this->add_data( 'site_meta', $site_meta );
	}

	/**
	 * Add a post.
	 *
	 * @param array $post
	 *
	 * @example lib/schema.json See 'post' type for all available fields.
	 *
	 * @throws \OxymelException
	 */
	public function add_post( $post = array() ) {
		$this->add_data( 'post', $post );
	}

	/**
	 * Add a term.
	 *
	 * @param array $term
	 *
	 * @example lib/schema.json See 'term' type for all available fields.
	 *
	 * @throws \OxymelException
	 */
	public function add_term( $term = array() ) {
		$this->add_data( 'term', $term );
	}

	/**
	 * Add a category.
	 *
	 * @param array $category
	 *
	 * @example lib/schema.json See 'category' type for all available fields.
	 *
	 * @throws \OxymelException
	 */
	public function add_category( $category = array() ) {
		$this->add_data( 'category', $category );
	}

	/**
	 * Add a tag.
	 *
	 * @param array $tag
	 *
	 * @example lib/schema.json See 'tag' type for all available fields.
	 *
	 * @throws \OxymelException
	 */
	public function add_tag( $tag = array() ) {
		$this->add_data( 'tag', $tag );
	}

	/**
	 * Add an author.
	 *
	 * @param array $author
	 *
	 * @example lib/schema.json See 'tag' type for all available fields.
	 *
	 * @throws \OxymelException
	 */
	public function add_author( $author = array() ) {
		$this->add_data( 'author', $author );
	}

	/**
	 * Finalize the WXR.
	 *
	 * @throws \OxymelException
	 */
	public function finalize() {
		// Write footer
		$this->write_footer();
	}

	/**
	 * Get the schema.
	 *
	 * @return array $schema The schema as an array.
	 */
	protected function get_schema() {

		$schema = file_get_contents( __DIR__ . '/schema.json' );
		$schema = json_decode( $schema, true );

		$this->wxr_version = $schema['wxr_version'];

		foreach ( $schema['types'] as $type => $data ) {
			foreach ( $data['fields'] as $key => $field ) {
				$schema['types'][ $type ]['fields'][ $field['name'] ] = $field;
				unset( $schema['types'][ $type ]['fields'][ $key ] );
			}
		}

		return $schema;
	}

	/**
	 * Add data of specified type to the WXR.
	 *
	 * @param string $type The type as defined in the schema.
	 * @param array $data The field values.
	 *
	 * @throws \OxymelException
	 */
	protected function add_data( $type, $data ) {
		$final_data = array();

		// Validate incoming data against schema, set defaults, etc.
		foreach ( $this->schema['types'][ $type ]['fields'] as $field_name => $field ) {

			// Get the value from the data or set it to null if it doesn't exist.
			$value = isset( $data[ $field_name ] ) ? $data[ $field_name ] : null;

			// Empty values or readonly values should be set to default or null
			if ( ! empty( $field['readonly'] ) || null === $value ) {
				$value = isset( $field['default'] ) ? $field['default'] : null;
			}

			// Without a value there's nothing to add so we continue.
			if ( null === $value ) {
				continue;
			}

			$final_data[ $field_name ] = $this->cast_value( $field['type'], $value );
		}

		$this->write_data( $type, $final_data );
	}

	/**
	 * Generates the WXR section for the given type and data, and writes it to the writer.
	 *
	 * @param string $type The type (schema) of the data.
	 * @param array $data  The data.
	 *
	 * @throws \OxymelException
	 */
	protected function write_data( $type, $data ) {

		// If there is no data provided, there's nothing to persist.
		if ( ! count( $data ) ) {
			return;
		}

		$schema    = $this->schema['types'][ $type ];
		$container = isset( $schema['container_element'] ) ? $schema['container_element'] : null;

		// If there is a container defined for the type, we must open it.
		if ( $container ) {
			$this->write_container_element( $container );
		}

		// Write each field to the WXR.
		foreach ( $data as $field_name => $value ) {
			$field = $schema['fields'][ $field_name ];
			$this->write_field( $field, $value );
		}

		// If there is a container defined for the type, we must close it.
		if ( $container ) {
			$this->write_container_element( $container, 'close' );
		}
	}

	/**
	 * Open or close a container element and write the XML to the output writer.
	 *
	 * @param string $element Element/tag name of the container.
	 * @param string $action Whether to open or close. Valid: 'open' or 'close'.
	 */
	protected function write_container_element( $element, $action = 'open' ) {
		$oxymel  = new Oxymel();
		$element = sprintf( '%s_%s', $action, $element );
		$oxymel->{$element};
		$this->writer->write( $oxymel->to_string() );
	}

	/**
	 * Handles the writing of an individual field to the WXR output.
	 *
	 * @param array $field The field information coming from the schema.
	 * @param mixed $value The value for the field.
	 *
	 * @throws \OxymelException
	 */
	protected function write_field( $field, $value ) {
		$oxymel = new Oxymel();

		// Apply a filter hook if it has been defined on the schema.
		if ( ! empty( $field['filter_hook'] ) ) {
			$value = apply_filters( $field['filter_hook'], $value );
		}

		if ( is_string( $value ) ) {
			$value = $this->to_utf8( $value );
		}

		// Handle writing the field depending on the type.
		switch ( $field['type'] ) {
			case 'comments':
				foreach ( $value as $comment ) {
					$this->add_data( 'comment', $comment );
				}
				break;

			case 'metas':
				$this->write_meta( $field, $value );
				break;

			case 'post_taxonomies':
				foreach ( $value as $taxonomy ) {
					$attributes = array(
						'nicename' => $taxonomy['slug'],
						'domain'   => $taxonomy['domain'],
					);
					$oxymel->tag( 'category', $attributes )
						->contains->cdata( $taxonomy['name'] )->end;
				}

				break;

			case 'cdata':
			case 'string':
				$oxymel->tag( $field['element'] )->contains->cdata( $value )->end;
				break;

			default:
				$oxymel->tag( $field['element'], $value );
		}

		$this->writer->write( $oxymel->to_string() );
	}

	protected function to_utf8( $value ) {

		$from_encoding = mb_detect_encoding( $value, 'auto' );
		$to_encoding   = 'UTF-8';

		if ( $from_encoding !== $to_encoding ) {
			$value = mb_convert_encoding( $value, $to_encoding, $from_encoding );
		}

		return $value;

	}

	/**
	 * Write meta keys and values.
	 *
	 * @param array $field The field information.
	 * @param array $metas Array of meta information.
	 *
	 * @throws \OxymelException
	 */
	protected function write_meta( $field, $metas ) {
		// Temporarily set the container_element on the meta type of the schema
		// to ensure the correct container element is used.
		$this->schema['types']['meta']['container_element'] = $field['child_element'];

		foreach ( $metas as $meta ) {
			$this->add_data( 'meta', $meta );
		}

		unset( $this->schema['types']['meta']['container_element'] );
	}

	/**
	 * Given a particular value, cast it to the passed type.
	 *
	 * @param string $type The schema type of the passed value.
	 * @param mixed $value The value to be cast.
	 *
	 * @return array|int|mixed
	 */
	protected function cast_value( $type, $value ) {
		switch ( $type ) {
			case 'comments':
				if ( ! is_array( $value ) ) {
					$value = array();
				}
				break;

			case 'int':
					$value = (int) $value;
				break;

			case 'mysql_date':
				$value = $this->is_valid_date( $value )
					? ( new DateTime( $value, new DateTimeZone( 'UTC' ) ) )
						->format( 'Y-m-d H:i:s' )
					: '';

				break;

			case 'rfc2822_date':
				$value = $this->is_valid_date( $value )
					? ( new DateTime( $value, new DateTimeZone( 'UTC' ) ) )
						->format( DateTime::RFC2822 )
					: '';
				break;

			case 'metas':
				if ( ! is_array( $value ) ) {
					$value = array();
				}

				$value = array_filter(
					$value,
					function( $meta ) {
						return array_key_exists( 'key', $meta ) && array_key_exists( 'value', $meta );
					}
				);

				break;

			case 'post_taxonomies':
				if ( ! is_array( $value ) ) {
					$value = array();
				}

				$value = array_filter(
					$value,
					function( $taxonomy ) {
						return array_key_exists( 'name', $taxonomy )
						&& array_key_exists( 'slug', $taxonomy )
						&& array_key_exists( 'domain', $taxonomy );
					}
				);

				break;
		}

		return $value;
	}

	/**
	 * Writes the header portion of the WXR to the output writer.
	 */
	protected function write_header() {
		$oxymel           = new Oxymel();
		$wp_generator_tag = apply_filters( 'the_generator', get_the_generator( 'export' ), 'export' );
		$comment          = <<<COMMENT

 This is a WordPress eXtended RSS file generated by WordPress as an export of your site.
 It contains information about your site's posts, pages, comments, categories, and other content.
 You may use this file to transfer that content from one site to another.
 This file is not intended to serve as a complete backup of your site.

 To import this information into a WordPress site follow these steps:
 1. Log in to that site as an administrator.
 2. Go to Tools: Import in the WordPress admin panel.
 3. Install the "WordPress" importer from the list.
 4. Activate & Run Importer.
 5. Upload this file using the form provided on that page.
 6. You will first be asked to map the authors in this export file to users
    on the site. For each author, you may choose to map to an
    existing user on the site or to create a new user.
 7. WordPress will then import each of the posts, pages, comments, categories, etc.
    contained in this file into your site.

COMMENT;
		$oxymel->xml()
				->comment( $comment )
				->raw( $wp_generator_tag )
				->open_rss(
					array(
						'version'       => '2.0',
						'xmlns:excerpt' => "http://wordpress.org/export/{$this->wxr_version}/excerpt/",
						'xmlns:content' => 'http://purl.org/rss/1.0/modules/content/',
						'xmlns:wfw'     => 'http://wellformedweb.org/CommentAPI/',
						'xmlns:dc'      => 'http://purl.org/dc/elements/1.1/',
						'xmlns:wp'      => "http://wordpress.org/export/{$this->wxr_version}/",
					)
				)
				->open_channel;

		$this->writer->write( $oxymel->to_string() );
	}

	/**
	 * Write the footer portion of the WXR to the output writer.
	 */
	protected function write_footer() {
		$oxymel = new Oxymel();
		$this->writer->write( $oxymel->close_channel->close_rss->to_string() );
	}

	/**
	 * Validate if the given date is a valid date that we can work with.
	 *
	 * @param string $date The date to validate as a string.
	 *
	 * @return bool
	 */
	protected function is_valid_date( $date ) {
		if ( ! is_string( $date ) || '' === $date ) {
			return false;
		}

		try {
			new DateTime( $date );
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}


}
