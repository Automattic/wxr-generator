<?php

namespace WXR_Generator;

/**
 * Interface Converter_Interface
 *
 * A converter is responsible for processing data from a specific source and
 * then use a WXR generator to turn that data into a valid WXR output.
 *
 * @package WXR_Generator
 */
interface Converter_Interface {

	public function __construct(Generator $generator);

	/**
	 * Convert data to WXR using the generator.
	 *
	 * @return mixed
	 */
	public function convert();

}
