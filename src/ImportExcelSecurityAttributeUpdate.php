<?php

namespace DPRMC\ClearStructure\Sentry\DataService\Services;

use Carbon\Carbon;
use DPRMC\Excel;


class ImportExcelSecurityAttributeUpdate extends ImportExcel {


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
            'title'    => "Sentry Attribute Update",
            'subject'  => "Import File",
            'category' => "import",
        ];
        $pathToTempFile = Excel::simple($this->dataArray, [], "Security_Attribute_Update", $tempFilename, $options);

        $response = $this->importPath($pathToTempFile);
        @unlink($tempFilename);
        return $response;
    }


}