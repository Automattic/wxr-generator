<?php

namespace WXR_Generator;

require_once __DIR__ . '/class-writer-interface.php';

class Buffer_Writer implements Writer_Interface {

	protected $buffer = '';

	public function write( $data ) {
		$this->buffer .= $data;
	}

	public function get() {
		return $this->buffer;
	}

	public function get_clear() {
		$buffer = $this->buffer;
		$this->clear();
		return $buffer;
	}

	public function clear() {
		$this->buffer = '';
	}

}
