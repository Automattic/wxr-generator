<?php

namespace WXR_Generator;

require_once __DIR__ . '/class-export-oxymel.php';

use WXR_Generator\Export_Oxymel;
use Traversable;


define( 'WXR_VERSION', '1.2' );

class Generator {

	/**
	 * Export instance that provides the data required to populate the WXR.
	 *
	 * @var Export_Interface $export
	 */
	protected $export;

	/**
	 * The version of the WXR export format.
	 *
	 * @var string
	 */
	protected $wxr_version = WXR_VERSION;


	/**
	 * The WXR Generator's constructor.
	 *
	 * @param Export_Interface $export
	 */
	public function __construct( Export_Interface $export ) {
		$this->export = $export;
	}

	/**
	 * Run the generator and output to the given writer.
	 *
	 * @param Writer_Interface $writer The WXR will be written to this writer.
	 * @param array $requested_sections Sections to include in the WXR.
	 */
	public function run( Writer_Interface $writer, $requested_sections = array() ) {

		$available_sections = array(
			'site_metadata',
			'posts',
			'authors',
			'categories',
			'tags',
			'nav_menu_terms',
			'custom_taxonomies_terms',
			'rss2_head_action',
		);

		$writer->write( $this->header() );

		// Determine which sections should be included in the WXR and run each of them.
		$sections = array_intersect( $available_sections, array_unique( $requested_sections ) );

		$this->run_sections( $sections, $writer );

		$writer->write( $this->footer() );

		$writer->close();
	}

	protected function run_sections( $sections, Writer_Interface $writer ) {
		foreach ( $sections as $section ) {
			$section_output = $this->{$section}();

			// Sections could return traversable type for performance reasons.
			if ( $section_output instanceof Traversable ) {
				foreach ( $section_output as $output ) {
					$writer->write( $output );
				}
				continue;
			}

			$writer->write( $this->{$section}() );
		}
	}

	/**
	 * Handle each post.
	 *
	 * @return \Generator
	 */
	protected function posts() {
		/*
		 * @todo author
		 */
		foreach ( $this->export->posts() as $post ) {
			$oxymel = new Export_Oxymel();
			$oxymel->item->contains
				->title( apply_filters( 'the_title_rss', $post['title'] ) )
				->tag( 'content:encoded' )->contains->cdata( $post['content'] )
				->tag( 'excerpt:encoded' )->contains->cdata( $post['excerpt'] )->end
				->tag( 'wp:post_id', $post['id'] )
				->tag( 'wp:post_date', $post['post_date'] )
				->tag( 'wp:post_date_gmt', $post['post_date_gmt'] )
				->tag( 'wp:post_type', $post['post_type'] )
				->tag( 'wp:comment_status', $post['comment_status'] )
				->optional( 'wp:post_modified', $post['modified'] )
				->optional( 'wp:post_modified_gmt', $post['modified_gmt'] )
				->tag( 'wp:ping_status', $post['ping_status'] )
				->tag( 'wp:post_name', $post['post_name'] )
				->tag( 'wp:status', $post['status'] )
				->tag( 'wp:post_parent', $post['post_parent'] )
				->tag( 'wp:menu_order', $post['menu_order'] )
				->tag( 'wp:post_password', $post['post_password'] )
				->tag( 'wp:is_sticky', $post['is_sticky'] )
				->tag( 'description', $post['description'] )
				->tag( 'guid', $post['link'], array( 'isPermalink' => 'false' ) )
				->end;

			/**
			 * 'title'          => '',
			'link'           => '',
			'published'      => '',
			'post_date'      => '',
			'post_date_gmt'  => date( 'Y-m-d H:i:s', time() ),
			'modified'       => date( 'Y-m-d H:i:s', time() ),
			'modified_gmt'   => date( 'Y-m-d H:i:s', time() ),
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
			 */

			//          foreach ( $post->terms as $term ) {
			//              $oxymel
			//                  ->category(
			//                      array(
			//                          'domain'   => $term->taxonomy,
			//                          'nicename' => $term->slug,
			//                      )
			//                  )->contains->cdata( $term->name )->end;
			//          }
			//                    foreach ( $post->meta as $meta ) {
			//                        $oxymel
			//                            ->tag( 'wp:postmeta' )->contains
			//                            ->tag( 'wp:meta_key', $meta->meta_key )
			//                            ->tag( 'wp:meta_value' )->contains->cdata( $meta->meta_value )->end
			//                            ->end;
			//                    }
			//          foreach ( $post->comments as $comment ) {
			//              $oxymel
			//                  ->tag( 'wp:comment' )->contains
			//                  ->tag( 'wp:comment_id', $comment->comment_ID )
			//                  ->tag( 'wp:comment_author' )->contains->cdata( $comment->comment_author )->end
			//                  ->tag( 'wp:comment_author_email', $comment->comment_author_email )
			//                  ->tag( 'wp:comment_author_url', esc_url( $comment->comment_author_url ) )
			//                  ->tag( 'wp:comment_author_IP', $comment->comment_author_IP )
			//                  ->tag( 'wp:comment_date', $comment->comment_date )
			//                  ->tag( 'wp:comment_date_gmt', $comment->comment_date_gmt )
			//                  ->tag( 'wp:comment_content' )->contains->cdata( $comment->comment_content )->end
			//                  ->tag( 'wp:comment_approved', $comment->comment_approved )
			//                  ->tag( 'wp:comment_type', $comment->comment_type )
			//                  ->tag( 'wp:comment_parent', $comment->comment_parent )
			//                  ->tag( 'wp:comment_user_id', $comment->user_id )
			//                  ->oxymel( $this->comment_meta( $comment ) )
			//                  ->end;
			//          }
			//          $oxymel
			//              ->end;
			$oxymel->end();
			yield $oxymel->to_string();
		}
	}

	protected function header() {
		$oxymel           = new Export_Oxymel();
		$charset          = $this->export->charset();
		$wp_generator_tag = $this->export->wp_generator_tag();
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
		return $oxymel
			->xml
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
			->open_channel
			->to_string();

	}

	protected function site_metadata() {
		$oxymel   = new Export_Oxymel();
		$metadata = $this->export->site_metadata();
		return $oxymel
			->title( $metadata['name'] )
			->link( $metadata['url'] )
			->description( $metadata['description'] )
			->pubDate( $metadata['pubDate'] )
			->language( $metadata['language'] )
			->tag( 'wp:wxr_version', $this->wxr_version )
			->tag( 'wp:base_site_url', $metadata['site_url'] )
			->tag( 'wp:base_blog_url', $metadata['blog_url'] )
			->to_string();
	}

	protected function authors() {
		$oxymel  = new Export_Oxymel();
		$authors = $this->export->authors();
		foreach ( $authors as $author ) {
			$oxymel
				->tag( 'wp:author' )->contains
				->tag( 'wp:author_id', $author->ID )
				->tag( 'wp:author_login', $author->user_login )
				->tag( 'wp:author_email', $author->user_email )
				->tag( 'wp:author_display_name' )->contains->cdata( $author->display_name )->end
				->tag( 'wp:author_first_name' )->contains->cdata( $author->user_firstname )->end
				->tag( 'wp:author_last_name' )->contains->cdata( $author->user_lastname )->end
				->end;
		}
		return $oxymel->to_string();
	}

	public function categories() {
		$oxymel     = new Export_Oxymel();
		$categories = $this->export->categories();
		foreach ( $categories as $term_id => $category ) {
			$category->parent_slug = $category->parent ? $categories[ $category->parent ]->slug : '';
			$oxymel->tag( 'wp:category' )->contains
				->tag( 'wp:term_id', $category->term_id )
				->tag( 'wp:category_nicename', $category->slug )
				->tag( 'wp:category_parent', $category->parent_slug )
				->optional_cdata( 'wp:cat_name', $category->name )
				->optional_cdata( 'wp:category_description', $category->description )
				->end;
		}
		return $oxymel->to_string();
	}

	protected function tags() {
		$oxymel = new Export_Oxymel();
		$tags   = $this->export->tags();
		foreach ( $tags as $tag ) {
			$oxymel->tag( 'wp:tag' )->contains
				->tag( 'wp:term_id', $tag->term_id )
				->tag( 'wp:tag_slug', $tag->slug )
				->optional_cdata( 'wp:tag_name', $tag->name )
				->optional_cdata( 'wp:tag_description', $tag->description )
				->end;
		}
		return $oxymel->to_string();
	}

	protected function nav_menu_terms() {
		return $this->terms( $this->export->nav_menu_terms() );
	}

	protected function custom_taxonomies_terms() {
		return $this->terms( $this->export->custom_taxonomies_terms() );
	}

	protected function rss2_head_action() {
		ob_start();
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress native hook.
		do_action( 'rss2_head' );
		return ob_get_clean();
	}

	protected function footer() {
		$oxymel = new Export_Oxymel();
		return $oxymel->close_channel->close_rss->to_string();
	}

	protected function terms( $terms ) {
		$oxymel = new Export_Oxymel();
		foreach ( $terms as $term ) {
			$term->parent_slug = $term->parent ? $terms[ $term->parent ]->slug : '';
			$oxymel->tag( 'wp:term' )->contains
				->tag( 'wp:term_id', $term->term_id )
				->tag( 'wp:term_taxonomy', $term->taxonomy )
				->tag( 'wp:term_slug', $term->slug );
			if ( 'nav_menu' !== $term->taxonomy ) {
				$oxymel
					->tag( 'wp:term_parent', $term->parent_slug );
			}
			$oxymel
				->optional_cdata( 'wp:term_name', $term->name )
				->optional_cdata( 'wp:term_description', $term->description )
				->end;
		}
		return $oxymel->to_string();
	}
}
