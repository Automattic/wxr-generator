<?php

namespace WXR_Generator;

require_once __DIR__ . '/class-export-interface.php';

abstract class Export_Abstract implements Export_Interface {

	public function site_metadata() {
		$metadata = array(
			'name'        => $this->bloginfo_rss( 'name' ),
			'url'         => $this->bloginfo_rss( 'url' ),
			'language'    => $this->bloginfo_rss( 'language' ),
			'description' => $this->bloginfo_rss( 'description' ),
			'pubDate'     => date( 'D, d M Y H:i:s +0000' ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			'site_url'    => is_multisite() ? network_home_url() : $this->bloginfo_rss( 'url' ),
			'blog_url'    => $this->bloginfo_rss( 'url' ),
		);
		return $metadata;
	}

	private function bloginfo_rss( $section ) {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Calling native WordPress hook.
		return apply_filters( 'bloginfo_rss', get_bloginfo_rss( $section ), $section );
	}

	public function wp_generator_tag() {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Calling native WordPress hook.
		return apply_filters( 'the_generator', get_the_generator( 'export' ), 'export' );
	}

	public function charset() {
		return get_bloginfo( 'charset' );
	}

	/**
	 * Create a new post structure with all of the keys prepopulated
	 * @param  array $item
	 * @return array
	 */
	protected function create_new_post( $item = array() ) {
		return array_merge(
			array(
				'title'          => '',
				'link'           => '',
				'published'      => '',
				'post_date'      => '',
				'post_date_gmt'  => date( 'Y-m-d H:i:s', time() ),
				'modified'       => '',
				'modified_gmt'   => '',
				'author'         => null,
				'guid'           => '',
				'description'    => '',
				'content'        => '',
				'excerpt'        => '',
				'id'             => 0,
				'comment_status' => 'open',
				'ping_status'    => 'open',
				'post_name'      => '',
				'status'         => 'draft',
				'post_parent'    => '',
				'menu_order'     => 0,
				'post_type'      => 'post',
				'post_password'  => '',
				'is_sticky'      => 0,
				'comments'       => array(),
				'categories'     => array(),
				'tags'           => array(),
				'meta'           => array(),
			),
			$item
		);
	}
}
