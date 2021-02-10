<?php


namespace DPRMC\ClearStructure\Sentry\DataService\Services;
/**
 * Class ImportExcelSecurityAttributeUpdate
 * @package DPRMC\ClearStructure\Sentry\DataService\Services
 */
class ImportExcelCashTransactions extends ImportExcel {
    protected $sheetName       = 'CashTransactions';
    protected $excelFilePrefix = 'sentry_cash_transactions_';
}