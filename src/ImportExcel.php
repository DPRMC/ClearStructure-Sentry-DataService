<?php

namespace DPRMC\ClearStructure\Sentry\DataService\Services;

use Carbon\Carbon;

/**
 * Class ImportExcel
 * @package DPRMC\ClearStructure\Sentry\DataService\Services
 */
class ImportExcel {


    /**
     * @var string The URL to the UAT (testing) site on ClearStructure's server. Unique for every client of theirs.
     */
    protected $uatUrl;

    /**
     * @var string The URL to the Prod (live) site on ClearStructure's server. Unique for every client of theirs.
     */
    protected $prodUrl;

    /**
     * @var string The username you use to log into the Sentry web interface.
     */
    protected $user;

    /**
     * @var string The password you use to log into the Sentry web interface.
     */
    protected $password;

    /**
     * @var string A path on your local filesystem to the Excel import file.
     */
    protected $pathToImportFile;

    /**
     * @var SoapClient The client used to communicate with the Sentry API.
     */
    protected $soapClient;

    /**
     * ImportExcel constructor.
     *
     * @param $uatUrl
     * @param $prodUrl
     * @param $user
     * @param $pass
     */
    public function __construct( $uatUrl, $prodUrl, $user, $pass ) {
        $this->uatUrl  = $uatUrl;
        $this->prodUrl = $prodUrl;
        $this->user    = $user;
        $this->password = $pass;
    }

    /**
     * Provided a properly formatted Excel import file, this method will import that data into the Sentry system and
     * return the SOAP response.
     *
     * @param      $pathToImportFile Used by file_get_contents(). Should be the path to a properly formatted Excel
     *                               import file. See ClearStructure docs for details.
     * @param bool $uat              Do you want to run this import against the UAT (testing) site.
     *
     * @throws \Exception
     * @return mixed
     */
    public function run( $pathToImportFile, $uat = false ) {

        if ( false === file_exists( $pathToImportFile ) ):
            throw new \Exception( "Unable to find the file located at [" . $pathToImportFile . "] and my directory is " . __DIR__ );
        endif;

        $this->pathToImportFile = $pathToImportFile;

        $url    = $uat ? $this->uatUrl : $this->prodUrl;
        $wdsl   = $url . '?WSDL';
        $stream = file_get_contents( $this->pathToImportFile );

        $function       = 'ImportExcel';
        $culture        = 'en-US';
        $soapParameters = [
            'cultureString'               => $culture,
            'userName'                    => $this->user,
            'password'                    => $this->password,
            'stream'                      => $stream,
            'sortTransactionsByTradeDate' => false,
            'createTrades'                => false,
        ];

        $this->soapClient = new \SoapClient( $wdsl, [
            'location'   => $url,
            'uri'        => 'gibberish',
            'trace'      => true,
            'exceptions' => true, // whether soap errors throw exceptions of type SoapFault.
            'keep_alive' => true, // whether to send the Connection: Keep-Alive header or Connection: close.
        ] );

        $soapResponse = $this->soapClient->$function( $soapParameters );

        return $this->parseSoapResponse( $soapResponse );

    }

    /**
     * You can see below the parsed XML from Sentry isn't the cleanest, so this method pulls out the info I need into a
     * nicely formatted array.
     *
     * @param $soapResponse
     *
     * @return array
     */
    protected function parseSoapResponse( $soapResponse ) {
        $parsed = new \SimpleXMLElement( $soapResponse->ImportExcelResult->any );

        return [
            'time'    => Carbon::parse( (string)$parsed->attributes()->time ),
            'name'    => (string)$parsed->tables->table->attributes()->name,
            'num'     => (int)$parsed->tables->table->import,
            'runtime' => (float)$parsed->tables->table->RunTime,
            'errors'  => (array)$parsed->tables->table->errors,
        ];

    }
}