<?php

namespace DPRMC\ClearStructure\Sentry\DataService\Services;

use Carbon\Carbon;
use DPRMC\Excel;

/**
 * Class ImportExcel
 * @package DPRMC\ClearStructure\Sentry\DataService\Services
 */
class ImportExcelSecurityPricingUpdate extends ImportExcel {


    /**
     * Provided a properly formatted Excel import file, this method will import that data into the Sentry system and
     * return the SOAP response.
     *
     * @param      $pathToImportFile string Used by file_get_contents(). Should be the path to a properly formatted
     *                               Excel import file. See ClearStructure docs for details.
     * @throws \Exception
     * @return mixed
     */
    public function importPath($pathToImportFile) {

        $stream = file_get_contents($pathToImportFile);

        $function       = 'ImportExcel';
        $culture        = 'en-US';
        $soapParameters = [
            'cultureString'               => $culture,
            'userName'                    => $this->user,
            'password'                    => $this->password,
            'stream'                      => $stream,
            'sortTransactionsByTradeDate' => FALSE,
            'createTrades'                => FALSE,
        ];

        $this->soapClient = new \SoapClient($this->wsdl, [
            'location' => $this->url,
            'uri'      => 'gibberish',
        ]);

        $soapResponse = $this->soapClient->$function($soapParameters);

        return $this->parseSoapResponse($soapResponse);
    }

    /**
     * @param $dataArray
     * @return mixed
     * @throws \Exception
     */
    protected function importArray($dataArray) {
        $tempFileHandle = tmpfile();
        $metaData       = stream_get_meta_data($tempFileHandle);
        $tempFilename   = $metaData[ 'uri' ];
        $options        = [
            'title'    => "Sentry Import File",
            'subject'  => "Import File",
            'category' => "import",
        ];
        $pathToTempFile = Excel::simple($this->dataArray, [], "Security_Pricing_Update", $tempFilename, $options);

        $response = $this->importPath($pathToTempFile);
        unlink($tempFilename);
        return $response;
    }

    /**
     * You can see below the parsed XML from Sentry isn't the cleanest, so this method pulls out the info I need into a
     * nicely formatted array.
     *
     * @param $soapResponse
     *
     * @return array
     */
    protected function parseSoapResponse($soapResponse) {
        $parsed = new \SimpleXMLElement($soapResponse->ImportExcelResult->any);

        $parsedResponse = [
            'time'    => Carbon::parse((string)$parsed->attributes()->time),
            'name'    => (string)$parsed->tables->table->attributes()->name,
            'num'     => (int)$parsed->tables->table->import,
            'runtime' => (float)$parsed->tables->table->RunTime,
            'errors'  => (array)$parsed->tables->table->errors,
        ];

        return $parsedResponse;

    }
}