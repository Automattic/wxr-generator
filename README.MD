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

echo $writer->get_clean();
```

## Testing

To run unit tests, please make sure the environment in which you want to test has the following:

 - PHPunit 7.x
 - SVN
 - A MySQL database

Before running unit tests, run the `phpunit/install.sh` shell script. This shell script will download test dependencies
and set up a database (if it does not already exist).

After running the install script, you can run `phpunit` to run the tests.
