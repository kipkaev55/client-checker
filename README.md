# ClientChecker PHP #

## Description ##

This package provides information about the user's GEO,
its browser, OS and client type (desktop or mobile).
The library works with free [GeoLite2 databases](http://dev.maxmind.com/geoip/geoip2/geolite2/).

## Install via Composer ##

We recommend installing this package with [Composer](http://getcomposer.org/).

### Download Composer ###

To download Composer, run in the root directory of your project:

```bash
curl -sS https://getcomposer.org/installer | php
```

You should now have the file `composer.phar` in your project directory.

### Install Dependencies ###

Run in your project root:

```
php composer.phar require kipkaev55/client-checker:dev
```

You should now have the files `composer.json` and `composer.lock` as well as
the directory `vendor` in your project directory. If you use a version control
system, `composer.json` should be added to it.

### Require Autoloader ###

After installing the dependencies, you need to require the Composer autoloader
from your code:

```php
require 'vendor/autoload.php';
```

## Usage ##

Straightforward:

```php
require_once __DIR__ . '/vendor/autoload.php'; // Autoload files using Composer autoload

use ClientChecker\Client;

$client = new Client(
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 YaBrowser/17.3.1.838 Yowser/2.5 Safari/537.36',
    '172.68.11.66',
    './GeoLite2-City.mmdb'
);
echo ($client->isMobile()) ? 'true' : 'false';
echo "\n";
echo $client->getOs();      //Mac OS X 10.12.4
echo "\n";
echo $client->getBrowser(); //Yandex Browser 17.3.1
echo "\n";
$geo = $client->getIpData();
echo $geo['country'];       //RU
echo "\n"; 
echo $geo['city'];          //Москва
echo "\n";
```

## Copyright and License ##

* This software is Copyright (c) 2017 by [Pro.Motion](http://prmotion.ru).
* This is free software, licensed under the MIT license
* Ua-parser PHP Library is licensed under the MIT license
* The user agents data from the ua-parser project is licensed under the Apache license
* The initial list of generic feature phones & smartphones came from Mobile Web OSP under the MIT license
* The initial list of spiders was taken from Yiibu's profile project under the MIT license.
* GeoIP2 PHP API licensed under the Apache License, Version 2.0.
