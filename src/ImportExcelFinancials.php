<?php

namespace DPRMC\ClearStructure\Sentry\DataService\Services;

/**
 * Class ImportExcelFinancials
 * @package DPRMC\ClearStructure\Sentry\DataService\Services
 */
class ImportExcelFinancials extends ImportExcel {
    protected $sheetName       = 'Financials';
    protected $excelFilePrefix = 'sentry_financials_';
}