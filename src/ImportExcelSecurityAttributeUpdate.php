<?php


namespace DPRMC\ClearStructure\Sentry\DataService\Services;
/**
 * Class ImportExcelSecurityAttributeUpdate
 * @package DPRMC\ClearStructure\Sentry\DataService\Services
 */
class ImportExcelSecurityAttributeUpdate extends ImportExcel {
    protected $sheetName       = 'Security_Attribute_Update';
    protected $excelFilePrefix = 'sentry_sec_attribute_';
}