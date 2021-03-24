<?php

namespace WXR_Generator;

interface Writer_Interface {

	/**
	 * Write data
	 *
	 * @param $data
	 *
	 * @return void
	 */
	public function write( $data);

	/**
	 * Cleanup and closing.
	 *
	 * @return mixed
	 */
	public function close();

}
