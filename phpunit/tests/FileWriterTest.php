<?php
/**
 * Class FileWriterTest
 *
 * @package Wpx_Generator
 */

use WXR_Generator\File_Writer;

/**
 * Tests for the file writer
 */
class FileWriterTest extends WP_UnitTestCase {

	public function testWriteContent() {
		$writer = new File_Writer( 'php://output' );

		ob_start();

		$writer->write( 'Test' );
		$writer->close();

		$output = ob_get_clean();

		$this->assertEquals( 'Test', $output );
	}
}
