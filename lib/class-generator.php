<?php

namespace WXR_Generator;

require_once __DIR__ . '/class-export-oxymel.php';

class Generator {

	const WXR_VERSION = '1.2';

	/**
	 * @var Writer_Interface $writer Instance of the writer interface to which to output the WXR.
	 */
	protected $writer;

	/**
	 * @var array $schema The schema for the WXR.
	 */
	protected $schema;

	public function __construct(\WXR_Generator\Writer_Interface  $writer) {
		$this->writer = $writer;
		$this->schema = $this->get_schema();
	}

	/**
	 * Run initialization logic.
	 */
	public function initialize() {
		$this->write_header();
	}

	/**
	 * Add a post.
	 *
	 * @param array $post
	 *
	 * @throws \OxymelException
	 */
	public function add_post($post = []) {
		$this->add_data('post', $post);
	}

	/**
	 * Add a term.
	 *
	 * @param array $term
	 *
	 * @throws \OxymelException
	 */
	public function add_term($term = []) {
		$this->add_data('term', $term);
	}

	/**
	 * Add a category.
	 *
	 * @param array $category
	 *
	 * @throws \OxymelException
	 */
	public function add_category($category = []) {
		$this->add_data('category', $category);
	}

	/**
	 * Add a tag.
	 *
	 * @param array $tag
	 *
	 * @throws \OxymelException
	 */
	public function add_tag($tag = []) {
		$this->add_data('tag', $tag);
	}

	/**
	 * Add an author.
	 *
	 * @param array $author
	 *
	 * @throws \OxymelException
	 */
	public function add_author($author = []) {
		$this->add_data('author', $author);
	}

	/**
	 * Finalize the WXR.
	 *
	 * @throws \OxymelException
	 */
	public function finalize() {

		foreach($this->tags as $tag) {
			$this->add_data('tag', $tag);
		}

		foreach($this->categories as $category) {
			$this->add_data('tag', $category);
		}

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
		$schema = json_decode($schema, true);

		foreach($schema as $type => $data) {
			foreach($data['fields'] as $key => $field) {
				$schema[$type]['fields'][$field['name']] = $field;
				unset($schema[$type]['fields'][$key]);
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
	protected function add_data($type, $data) {

		$final_data = [];

		// Validate incoming data against schema, set defaults, etc.
		foreach($this->schema[$type]['fields'] as $field_name => $field) {

			// Get the value from the data or set it to null if it doesn't exist.
			$value = isset($data[$field_name]) ? $data[$field_name] : null;

			// If the value is null, check if we have a default to set.
			if( $value === null && isset($field['default']) ) {
				$value = $field['default'] instanceof \Closure
					? $field['default']()
					: $field['default'];
			}

			// Without a value there's nothing to add so we continue.
			if( $value === null) {
				continue;
			}

			$final_data[$field_name] = $this->cast_value( $field['type'], $value );
		}

		$this->write_data($type, $final_data);
	}

	/**
	 * Generates the WXR section for the given type and data, and writes it to the writer.
	 *
	 * @param string $type The type (schema) of the data.
	 * @param array $data  The data.
	 *
	 * @throws \OxymelException
	 */
	protected function write_data($type, $data) {

		// If there is no data provided, there's nothing to persist.
		if( !count($data) ) {
			return;
		}

		$schema = $this->schema[$type];
		$container = isset($schema['container_element']) ? $schema['container_element'] : null;

		// If there is a container defined for the type, we must open it.
		if( $container ) {
			$this->write_container_element( $container );
		}

		// Write each field to the WXR.
		foreach( $data as $field_name => $value ) {
			$field = $schema['fields'][$field_name];
			$this->write_field( $field, $value );
		}

		// If there is a container defined for the type, we must close it.
		if( $container ) {
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
		$oxymel = new Export_Oxymel();
		$element = sprintf('%s_%s', $action, $element);
		$oxymel->{$element};
		$this->writer->write($oxymel->to_string());
	}

	/**
	 * Handles the writing of an individual field to the WXR output.
	 *
	 * @param array $field The field information coming from the schema.
	 * @param mixed $value The value for the field.
	 */
	protected function write_field(  $field, $value ) {

		$oxymel = new Export_Oxymel();

		// Apply a filter hook if it has been defined on the schema.
		if( !empty($field['filter_hook']) ) {
			$value = apply_filters( $field['filter_hook'], $value );
		}

		// Handle writing the field depending on the type.
		switch( $field['type'] ) {

			case "comments":
				foreach($value as $comment) {
					$this->add_data( 'comment', $comment);
				}
				break;

			case "metas":
				$this->write_meta($field, $value);
				break;

			case "post_taxonomies":

				foreach($value as $taxonomy) {
					$attributes = ['nicename' => $taxonomy['slug'], 'domain' => $taxonomy['domain'] ];
					$oxymel->tag('category', $attributes)
						->contains->cdata($taxonomy['name'])->end;
				}

				break;

			case "cdata":
				$oxymel->tag($field['element'])->contains->cdata($value)->end;
				break;

			default:
				$oxymel->tag($field['element'], $value);
		}

		$this->writer->write( $oxymel->to_string() );
	}

	/**
	 * Write meta keys and values.
	 *
	 * @param array $field The field information.
	 * @param array $metas Array of meta information.
	 *
	 * @throws \OxymelException
	 */
	protected function write_meta($field, $metas) {
		// Temporarily set the container_element on the meta type of the schema
		// to ensure the correct container element is used.
		$this->schema['meta']['container_element'] = $field['child_element'];

		foreach($metas as $meta) {
			$this->add_data('meta', $meta);
		}

		unset($this->schema['meta']['container_element']);
	}

	/**
	 * Given a particular value, cast it to the passed type.
	 *
	 * @param string $type The schema type of the passed value.
	 * @param mixed $value The value to be cast.
	 *
	 * @return array|int|mixed
	 */
	protected function cast_value($type, $value) {
		switch($type) {
			case "comments":

				if( !is_array($value) ) {
					$value = array();
				}
				break;


			case "int":
					$value = (int) $value;
				break;

			case "metas":

				if( !is_array($value) ) {
					$value = array();
				}

				$value = array_filter($value, function($meta) {
					return array_key_exists('key', $meta) && array_key_exists('value', $meta);
				});

				break;

			case "post_taxonomies":

				if( !is_array($value) ) {
					$value = array();
				}

				$value = array_filter($value, function($taxonomy) {
					return array_key_exists('name', $taxonomy)
						&& array_key_exists('slug', $taxonomy)
						&& array_key_exists('domain', $taxonomy);
				});

				break;
		}


		$categories = [
			[
				'name' => 'test',
				'attributes' => ['slug' => 'abc', 'cde']
			]
		];

		return $value;
	}

	/**
	 * Writes the header portion of the WXR to the output writer.
	 */
	protected function write_header() {
		$oxymel           = new Export_Oxymel();
		$encoding          = get_bloginfo('charset');
		$wp_generator_tag = apply_filters( 'the_generator', get_the_generator( 'export' ), 'export' );
		$wxr_version        = self::WXR_VERSION;
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
		$oxymel->xml(['encoding' => $encoding, 'version' => '1.0'])
				->comment( $comment )
				->raw( $wp_generator_tag )
				->open_rss(
					array(
						'version'       => '2.0',
						'xmlns:excerpt' => "http://wordpress.org/export/{$wxr_version}/excerpt/",
						'xmlns:content' => 'http://purl.org/rss/1.0/modules/content/',
						'xmlns:wfw'     => 'http://wellformedweb.org/CommentAPI/',
						'xmlns:dc'      => 'http://purl.org/dc/elements/1.1/',
						'xmlns:wp'      => "http://wordpress.org/export/{$wxr_version}/",
					)
				)
				->open_channel;

		$this->writer->write( $oxymel->to_string() );
	}

	/**
	 * Write the footer portion of the WXR to the output writer.
	 */
	protected function write_footer() {
		$oxymel = new Export_Oxymel();
		$this->writer->write($oxymel->close_channel->close_rss->to_string());
	}


}
