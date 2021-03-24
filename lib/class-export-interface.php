<?php


namespace WXR_Generator;

use stdClass;

interface Export_Interface {

	/**
	 * Returns site metadata.
	 *
	 * @return array
	 */
	public function site_metadata();


	/**
	 * Returns the generator tag.
	 *
	 * @return string
	 */
	public function wp_generator_tag();


	/**
	 * Returns the charset for the site.
	 *
	 * @return string
	 */
	public function charset();

	/**
	 * Returns categories.
	 *
	 * @return stdClass[]
	 */
	public function categories();

	/**
	 * Returns tags.
	 *
	 * @return stdClass[]
	 */
	public function tags();

	/**
	 * Returns authors.
	 *
	 * @return stdClass[]
	 */
	public function authors();

	/**
	 * Returns posts.
	 *
	 * @return iterable
	 */
	public function posts();

	/**
	 * Returns custom terms.
	 *
	 * @return stdClass[]
	 */
	public function custom_taxonomies_terms();

	/**
	 * Returns nav menu terms.
	 *
	 * @return stdClass[]
	 */
	public function nav_menu_terms();

}
