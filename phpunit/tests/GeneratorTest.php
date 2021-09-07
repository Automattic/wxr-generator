<?php
/**
 * Class GeneratorTest
 *
 * @package Wpx_Generator
 */

use WXR_Generator\Generator;
use WXR_Generator\Buffer_Writer;

/**
 * Tests for the generator.
 */
class GeneratorTest extends WP_UnitTestCase {

	/**
	 * @var Buffer_Writer
	 */
	protected $writer;

	/**
	 * @var Generator
	 */
	protected $generator;

	public function setUp() {
		parent::setUp();

		$this->writer    = new Buffer_Writer();
		$this->generator = new Generator( $this->writer );

		return;
	}

	/**
	 * This test verifies that a WXR with no content is created correctly.
	 *
	 * @throws OxymelException
	 */
	public function testEmptyWXR() {
		$this->generator->initialize();
		$this->generator->finalize();

		$wxr = simplexml_load_string( $this->writer->get_clear() );

		$this->assertNotFalse( $wxr );
		$this->assertEquals( 1, $wxr->channel->count() );
		$this->assertEquals( 1, $wxr->channel[0]->count() );
	}

	/**
	 * This test verifies a post is correctly added to the WXR.
	 * @throws OxymelException
	 */
	public function testPost() {
		$this->generator->initialize();

		$this->generator->add_post(
			array(
				'title'           => 'Test post',
				'content'         => 'Test content',
				'metas'           => array(
					array(
						'key'   => 'test key',
						'value' => 'test value',
					),
				),
				'comments'        => array(
					array(
						'author'  => 'testuser',
						'content' => 'test comment',
						'metas'   => array(
							array(
								'key'   => 'test key',
								'value' => 'test value',
							),
						),
					),
				),
				'post_taxonomies' => array(
					array(
						'name'   => 'test',
						'domain' => 'category',
						'slug'   => 'test-category',
					),
				),
			)
		);

		$this->generator->finalize();

		$wxr = simplexml_load_string( $this->writer->get_clear() );

		$this->assertNotFalse( $wxr );
		$this->assertEquals( 1, count( $wxr->channel[0]->item ) );
		$item = $wxr->channel[0]->item[0];
		$this->assertEquals( 'Test post', $item->title );
		$this->assertEquals( 'Test content', $item->children( 'content', true )->encoded );

		$this->assertEquals( 1, count( $item->children( 'wp', true )->post_meta ) );
		$this->assertEquals( 1, count( $item->children( 'wp', true )->comment ) );
		$this->assertEquals( 1, count( $item->children( 'wp', true )->comment[0]->children( 'wp', true )->commentmeta ) );
		$this->assertEquals( 1, count( $item->category ) );
	}

	/**
	 * This test verifies that a filter defined in the schema is correctly applied.
	 *
	 * @throws OxymelException
	 */
	public function testFilterIsApplied() {
		$this->generator->initialize();

		$filter = function( $title ) {
			return $title . ' filtered';
		};

		add_filter( 'the_title_rss', $filter );

		$this->generator->add_post(
			array(
				'title'   => 'Test post',
				'content' => 'Test content',
			)
		);

		$this->generator->finalize();

		remove_filter( 'the_title_rss', $filter );

		$wxr = simplexml_load_string( $this->writer->get_clear() );
		$this->assertEquals( $wxr->channel[0]->item[0]->title, 'Test post filtered' );
	}

	public function testDateFormatting() {
		$this->generator->initialize();

		$this->generator->add_post(
			array(
				'title'     => 'Test post',
				'content'   => 'Test content',
				'date'      => '2012-12-12 12:12:12',
				'post_date' => '2012-12-12 12:12:12',
			)
		);

		$this->generator->finalize();

		$wxr = simplexml_load_string( $this->writer->get_clear() );

		$item = $wxr->channel[0]->item[0];
		$this->assertEquals( $item->pubDate, 'Wed, 12 Dec 2012 12:12:12 +0000' ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->assertEquals( $item->children( 'wp', true )->post_date, '2012-12-12 12:12:12' );
	}

	/**
	 * This test ensures that post_date_gmt is used when present, and post_date
	 * does not fill with an invalid `now` value.
	 *
	 * @throws OxymelException
	 */
	public function testPostDateGmt() {
		$this->generator->initialize();

		$this->generator->add_post(
			array(
				'title'         => 'Test post',
				'content'       => 'Test content',
				'date'          => '2012-12-12 12:12:12',
				'post_date_gmt' => '2012-12-12 12:12:12',
			)
		);

		$this->generator->finalize();

		$wxr = simplexml_load_string( $this->writer->get_clear() );

		$item = $wxr->channel[0]->item[0];
		$this->assertEquals( $item->pubDate, 'Wed, 12 Dec 2012 12:12:12 +0000' ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->assertEquals( $item->children( 'wp', true )->post_date_gmt, '2012-12-12 12:12:12' );
		$this->assertNotContains( '<wp:post_date/>', $wxr->asXML() ); // make sure post_date empty node is not present.
	}

	public function testCategories() {
		$this->generator->initialize();

		$this->generator->add_category(
			array(
				'slug'  => 'test-category',
				'name'  => 'Test Category',
				'metas' => array(
					array(
						'key'   => 'meta_key',
						'value' => 'meta_value',
					),
				),
			)
		);

		$this->generator->finalize();

		$wxr = simplexml_load_string( $this->writer->get_clear() );

		$wp = $wxr->channel[0]->children( 'wp', true );

		$this->assertEquals( 1, count( $wp->category ) );
		$this->assertEquals( 1, count( $wp->category->children( 'wp', true )->term_meta ) );
		$this->assertEquals( 'test-category', $wp->category[0]->category_nicename );
		$this->assertEquals( 'Test Category', $wp->category[0]->cat_name );
	}
}
