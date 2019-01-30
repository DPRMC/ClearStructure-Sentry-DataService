<?php

namespace DPRMC\ClearStructure\Sentry\DataService\Services;

/**
 * Class ImportExcel
 * @package DPRMC\ClearStructure\Sentry\DataService\Services
 */
class ImportExcelSecurityPricingUpdate extends ImportExcel {
    protected $sheetName       = 'Security_Pricing_Update';
    protected $excelFilePrefix = 'sentry_sec_pricing_';
}