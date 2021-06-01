<?php
/**
 * Plugin Name:     WXR Generator
 * Text Domain:     wxr-generator
 * Plugin URI:      https://github.com/Automattic/wxr-generator
 * Description:     The WXR Generator is a library used to help with the creation of WXR files.
 * Author:          Caribou Team
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Wxr_Generator
 */

namespace WXR_Generator;

// Prevent loading the classes if they are defined.
if ( class_exists( '\WXR_Generator\Generator' ) ) {
	return;
}

require_once __DIR__ . '/lib/class-generator.php';
require_once __DIR__ . '/lib/class-buffer-writer.php';
require_once __DIR__ . '/lib/class-file-writer.php';
