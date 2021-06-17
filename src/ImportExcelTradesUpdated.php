<?php

namespace DPRMC\ClearStructure\Sentry\DataService\Services;

/**
 * Class ImportExcelSecurityPricingUpdate
 * @package DPRMC\ClearStructure\Sentry\DataService\Services
 */
class ImportExcelTradesUpdated extends ImportExcel {
    protected $sheetName       = 'Trades_Updated';
    protected $excelFilePrefix = 'sentry_trades_updated_';
}