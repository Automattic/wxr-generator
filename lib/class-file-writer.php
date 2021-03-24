<?php

namespace WXR_Generator;

require_once __DIR__ . '/class-writer-interface.php';

class File_Writer implements Writer_Interface {

	protected $handle;

	public function __construct( $path ) {
		$this->handle = fopen( $path, 'wb+' );
	}

	public function write( $data ) {
		fwrite( $this->handle, $data );
	}

	public function close() {
		fclose( $this->handle );
	}

}
