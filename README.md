# ClearStructure - Sentry - Data Service
[![Coverage Status](https://coveralls.io/repos/github/DPRMC/ClearStructure-Sentry-DataService/badge.svg?branch=master)](https://coveralls.io/github/DPRMC/ClearStructure-Sentry-DataService?branch=master) [![Build Status](https://travis-ci.org/DPRMC/ClearStructure-Sentry-DataService.svg?branch=master)](https://travis-ci.org/DPRMC/ClearStructure-Sentry-DataService) [![Total Downloads](https://poser.pugx.org/dprmc/clear-structure-sentry-data-service/downloads)](https://packagist.org/packages/dprmc/clear-structure-sentry-data-service) [![Latest Stable Version](https://poser.pugx.org/dprmc/clear-structure-sentry-data-service/version)](https://packagist.org/packages/dprmc/clear-structure-sentry-data-service) [![License](https://poser.pugx.org/dprmc/clear-structure-sentry-data-service/license)](https://packagist.org/packages/dprmc/clear-structure-sentry-data-service) 

## ImportExcel
Used to import Standard Import Files as defined by ClearStructure.

Check Sentry's documentation for the proper format if a Standard Import File.

```php
// This will attempt to import the contents of 'standard_import_file.xlsx' into the UAT site.
// Change the 2nd parameter of the run() method to true to import to your production site.

use DPRMC\ClearStructure\Sentry\DataService\Services\ImportExcel;

$uatUrl           = 'http://your-uat-url';
$prodUrl          = 'http://your-prod-url';
$user             = 'yourSentryUserName';
$pass             = 'yourEncryptedSentryPassword';
$pathToImportFile = 'standard_import_file.xlsx';
$postToUAT        = true;

$importExcelResponse = ImportExcel::init( $uatUrl, $prodUrl, $user, $pass, $postToUAT )
                           ->setData($pathToImportFile)
                           ->run();
                           
// Contents of $importExcelResponse->response() if everything goes well:
Array
(
    [time] => Carbon\Carbon Object
        (
            [date] => 2018-08-03 16:12:23.000000
            [timezone_type] => 3
            [timezone] => UTC
        )

    [name] => Security_Attribute_Update
    [num] => 2
    [runtime] => 296.8872
    [errors] => Array
        (
        )

    [warnings] => Array
        (
        )
)

// Call path() to get the local filepath of the xlsx that was uploaded to Sentry.
$importExcelResponse->path();

```

## Deleting Data
```php

$data   = [];
$data[] = [
    'scheme_identifier'          => 42,
    'scheme_name'                => 'SentryId',
    'market_data_authority_name' => 'DB',
    'action'                     => 'DELETE',
    'as_of_date'                 => '1/1/2018',
];

$data[] = [
    'scheme_identifier'          => 'ABCDEFGH1',
    'scheme_name'                => 'CUSIP',
    'market_data_authority_name' => 'DB',
    'action'                     => 'DELETE',
    'as_of_date'                 => '1/1/2018',
];

$deleteExcelResponse = DeleteExcelSecurityPricing::init( $uatUrl, $prodUrl, $user, $pass, $postToUAT )
                                  ->setData($data)  
                                  ->delete();




```

## Testing
Want to run the PHPUnit tests?
```console
foo@bar:~$ php ./phpunit-5.7.27.phar
```
