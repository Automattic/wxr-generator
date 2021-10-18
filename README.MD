# WXR Generator

This library helps with the creation of valid WXR output.

## Description

The WXR generator library is a helper library for the creation of WXR output. Typically, 2 main components are used:

### Writer
A writer (e.g. file, buffer, database) to which the WXR will be written. This can be any writer that implements the `Writer_Interface`.

### Generator
The generator takes data that conforms to the `lib/schema.php` and uses the writer to output this data.

## Example usage

```php
$writer    = new Buffer_Writer();
$generator = new WXR_Generator\Generator( $writer );

// Run initialization logic.
$generator->initialize();

// Add some posts
foreach( $posts as $post ) {
	$generator->add_post( $post );
}

// Run finalization logic.
$generator->finalize();

echo $writer->get_clear();
```

## Testing instructions

Testing the plugin locally is easiest done by using [wp-env](https://make.wordpress.org/core/2020/03/03/wp-env-simple-local-environments-for-wordpress/).

## Set up your local environment

The easiest way to run manual and unit tests for the WXR Generator is by using [wp-env](https://make.wordpress.org/core/2020/03/03/wp-env-simple-local-environments-for-wordpress/).
The instructions here assume you are using wp-env.

### Run unit tests

To run unit tests some composer dependencies need to be installed. We can run composer and install the dependencies by running th following command:

`wp-env run composer install`

Unit tests can now be run with the following command:

`wp-env run phpunit "phpunit --configuration=html/wp-content/plugins/wxr-generator/phpunit.xml.dist"`
