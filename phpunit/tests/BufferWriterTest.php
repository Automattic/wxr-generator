<?php
/**
 * Class BufferWriterTest
 *
 * @package Wpx_Generator
 */

use WXR_Generator\Buffer_Writer;

/**
 * Tests for the buffer writer
 */
class BufferWriterTest extends WP_UnitTestCase {

	public function testWriteContent() {
		$writer = new Buffer_Writer();
		$writer->write( 'Test' );
		$this->assertEquals( 'Test', $writer->get_clear() );
	}

	public function testClearBuffer() {
		$writer = new Buffer_Writer();
		$writer->write( 'Test' );
		$writer->clear();
		$this->assertEmpty( $writer->get() );
	}
}
