# ClearStructure - Sentry - Data Service
[![Coverage Status](https://coveralls.io/repos/github/DPRMC/ClearStructure-Sentry-DataService/badge.svg?branch=master)](https://coveralls.io/github/DPRMC/ClearStructure-Sentry-DataService?branch=master) [![Build Status](https://travis-ci.org/DPRMC/ClearStructure-Sentry-DataService.svg?branch=master)](https://travis-ci.org/DPRMC/ClearStructure-Sentry-DataService)

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
$importExcel      = new ImportExcel( $uatUrl, $prodUrl, $user, $pass );
$parsedResponse   = $importExcel->run( $pathToImportFile, false );
```

## Testing
Want to run the PHPUnit tests?
```console
foo@bar:~$ php ./phpunit-5.7.27.phar
```
