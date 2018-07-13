<?php

namespace DPRMC\ClearStructure\Sentry\DataService\Services;

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
        $this->pass    = $pass;
    }

    /**
     * Provided a properly formatted Excel import file, this method will import that data into the Sentry system and
     * return the SOAP response.
     *
     * @param      $pathToImportFile Used by file_get_contents(). Should be the path to a properly formatted Excel
     *                               import file. See ClearStructure docs for details.
     * @param bool $uat              Do you want to run this import against the UAT (testing) site.
     *
     * @return mixed
     */
    public function run( $pathToImportFile, $uat = false ) {
        $this->pathToImportFile = $pathToImportFile;

        $url    = $uat ? $this->uatUrl : $this->prodUrl;
        $wdsl   = $url . '?WSDL';
        $stream = file_get_contents( $pathToImportFile );

        $function        = 'ImportExcel';
        $culture         = 'en-US';
        $aSoapParameters = [
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

        $soapResponse = $this->soapClient->$function( $aSoapParameters );

        return $soapResponse;
    }
}