## Helper functions for PHP frameworks

This package contains display formatting helpers for your php project.

### Usage

all functions are called as static functions. Example:
```
Helper::formatDate($date);
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

There are serveral functions in this package:

* formatDate: function to format a date
* isJson: check if a given string is json
* cast: cast object to an(other) class
* toBase64: base64 encode files
* timeSince: function to calculate date difference
* truncate: cut a text to specified length and add '...' at the and of the string
* mime: Get the mime type from file extension
* checkForFolder: Check if a folder exists and create if not exists
* is_serialized: Check if a string is serialized


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
