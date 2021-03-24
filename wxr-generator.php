<?php
/**
 * Plugin Name:     WXR Generator
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     wxr-generator
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Wxr_Generator
 */

namespace WXR_Generator;

// Prevent loading the classes if they are defined.
if ( class_exists('\WXR_Generator\Generator')) {
	return;
}

require_once __DIR__ . '/lib/class-generator.php';
require_once __DIR__ . '/lib/class-export-abstract.php';
require_once __DIR__ . '/lib/class-file-writer.php';
