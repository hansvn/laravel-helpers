## Helper functions for PHP frameworks

This package contains display formatting helpers for your php project.

### Usage

all functions are called as static functions. Some examples:
```php
Helper::formatDate($date);
Helper::isJson($json_string);
Helper::cast($object, $to_class_name);
Helper::toBase64($filename);
Helper::timeSince($date);
Helper::truncate($really_long_string);
Helper::mime($file_extension);
Helper::checkForFolder($folder_path);
Helper::is_serialized($string_to_check);
Helper::maxFileUploadSize();
Helper::hex2rgb($hexadecimal_string);
```

## Installation

### Laravel 5.x:

After updating composer, add the ServiceProvider to the providers array in config/app.php

    Hansvn\Helper\ServiceProvider::class,

You can optionally use the facade for shorter code. Add this to your facades:

    'Helper' => Hansvn\Helper\Facade::class


### Lumen:

After updating composer add the following lines to register provider in `bootstrap/app.php`

  ```
  $app->register(\Hansvn\Helper\ServiceProvider::class);
  ```
  
To change the configuration, copy the config file to your config folder and enable it in `bootstrap/app.php`:

  ```
  $app->configure('helper');
  ```


### Configuration
The defaults configuration settings are set in `config/helper.php`. Copy this file to your own config directory to modify the values. You can publish the config using this command:

    php artisan vendor:publish --tag=config --provider="Hansvn\Helper\ServiceProvider"


### Functions

There are serveral functions in this package (represented with the parameters of the functions definition):

* formatDate: function to format a date
  * `formatDate($date, $format = null)`
* isJson: check if a given string is json
  * `isJson($string)`
* cast: cast object to an(other) class
  * `cast($obj, $to_class)`
* toBase64: base64 encode files
  * `toBase64($directory, $file = null, $with_mime = false)`
* timeSince: display the difference in time in a human readable form
  * `timeSince($date, $now = null)`
* truncate: cut a text to specified length and add three dots ('...') at the and of the string
  * `truncate($text, $length = 100, $options = array())`
* mime: Get the mime type from file extension
  * `mime($extension, $default = 'application/octet-stream')`
* checkForFolder: Check if a folder exists and create if not exists
  * `checkForFolder($folder, $permissions = 0775)`
* is_serialized: Check if a string is serialized
  * `is_serialized($string)`
* maxFileUploadSize: Get the maximum file upload size (in MB) allowed by the sever
  * `maxFileUploadSize()`
* hex2rgb: Convert a hexadecimal color to their rgb values
  * `hex2rgb($hex)`


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
