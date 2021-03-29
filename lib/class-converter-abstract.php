<?php

namespace WXR_Generator;

require_once __DIR__ . '/class-converter-interface.php';

/**\
 * Class Converter_Abstract
 *
 * @package WXR_Generator
 */
abstract class Converter_Abstract implements Converter_Interface {


	/**
	 * @var Generator $generator The instance of the WXR generator.
	 */
	protected $generator;


	/**
	 * Converter_Abstract constructor.
	 *
	 * @param $source_file
	 * @param Generator $generator
	 */
	public function __construct( Generator $generator) {
		$this->generator = $generator;
	}


	/**
	 * Start the conversion of data to WXR.
	 *
	 * @throws \OxymelException
	 */
	public function convert() {
		$this->generator->initialize();

		$this->process();

		$this->generator->finalize();
	}

	/**
	 * Converter method that gathers data and passes it to the generator.
	 *
	 * @return void
	 */
	abstract protected function process();

}
