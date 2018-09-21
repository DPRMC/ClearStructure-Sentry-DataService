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
     * @return ImportExcelResponse
     */
    protected function importPath( string $pathToImportFile ): ImportExcelResponse {

        $this->pathVariable = $pathToImportFile;
        $stream             = file_get_contents( $pathToImportFile );

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

        $this->soapClient = new \SoapClient( $this->wsdl, [
            'location' => $this->url,
            'uri'      => 'gibberish',
        ] );

        $soapResponse = $this->soapClient->$function( $soapParameters );

        return new ImportExcelResponse( $soapResponse, $pathToImportFile );

        //return $this->parseSoapResponse($soapResponse);
    }

    /**
     * @return ImportExcelResponse
     * @throws \Exception
     */
    protected function importArray(): ImportExcelResponse {
        $pathToTempFile = $this->getExcelFile();
        $response       = $this->importPath( $pathToTempFile );
        return $response;
    }


    public function getExcelFile( string $directoryForExcelFile = NULL ) {
        $tempFilename   = tempnam( $directoryForExcelFile, 'sentry_sec_pricing_' );
        $tempFileHandle = fopen( $tempFilename, "w" );

        //$tempFileHandle = tmpfile();
        $metaData     = stream_get_meta_data( $tempFileHandle );
        $tempFilename = $metaData[ 'uri' ] . '.xlsx';
        $options      = [
            'title'    => "Sentry Attribute Update",
            'subject'  => "Import File",
            'category' => "import",
        ];
        return Excel::simple( $this->dataArray, [], "Security_Pricing_Update", $tempFilename, $options );
    }


}