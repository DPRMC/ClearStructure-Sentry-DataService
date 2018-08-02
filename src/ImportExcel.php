<?php

namespace DPRMC\ClearStructure\Sentry\DataService\Services;

use Carbon\Carbon;
use DPRMC\Excel;

abstract class ImportExcel {
    protected static $_instance;

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
     * @var string Either path or array
     */
    protected $dataType = NULL;


    /**
     * @var string|array Either a path to an XLSX or an array of data to be imported.
     */
    protected $dataArray;

    /**
     * @var bool Do you want to import the data to the UAT site, or the PROD site. Default is PROD.
     */
    protected $uat = FALSE;

    /**
     * @var string The final URL that will be posted to. UAT or PROD
     */
    protected $url;

    /**
     * @var string The final WSDL URL that will be used in the post. UAT or PROD.
     */
    protected $wsdl;


    /**
     * ImportExcel constructor.
     * @param $uatUrl
     * @param $prodUrl
     * @param $user
     * @param $pass
     * @param bool $uat
     * @throws \Exception
     */
    public function __construct($uatUrl, $prodUrl, $user, $pass, $uat = FALSE) {

        if ( $uatUrl == $prodUrl ):
            throw new \Exception("Your UAT url is the same as your PROD url. That could be dangerous.");
        endif;

        $this->uatUrl   = $uatUrl;
        $this->prodUrl  = $prodUrl;
        $this->user     = $user;
        $this->password = $pass;
        $this->uat      = $uat;

        $this->url  = $uat ? $this->uatUrl : $this->prodUrl;
        $this->wsdl = $this->url . '?WSDL';
    }

    public final static function init($uatUrl, $prodUrl, $user, $pass, $uat = FALSE) {
        if ( NULL === static::$_instance ) {
            static::$_instance = new static($uatUrl, $prodUrl, $user, $pass, $uat);
        }

        return static::$_instance;
    }

    /**
     * @param $data
     * @return $this
     * @throws \Exception
     */
    public function setData($data) {
        if ( is_array($data) ):
            $this->dataType  = 'array';
            $this->dataArray = $data;
            return $this;
        endif;

        // PATH WAS PASSED IN.
        if ( is_string($data) ):
            if ( FALSE === file_exists($data) ):
                throw new \Exception("Unable to find the file located at [" . $data . "] and my directory is " . __DIR__);
            endif;

            $this->pathToImportFile = $data;
            $this->dataType         = 'path';
            return $this;
        endif;

        throw new \Exception("You need to pass a path to an Excel file, or a multi-dimensional array containing the data to be inserted.");
    }

    public function run() {
        switch ( $this->dataType ):
            case 'array':
                return $this->importArray($this->dataArray);
                break;

            case 'path':
                return $this->importPath($this->pathToImportFile);
                break;

            default:
                throw new \Exception("You need to set your data source for the import.");
        endswitch;
    }


    abstract protected function importPath($pathToImportFile);

    abstract protected function importArray($dataArray);

    abstract protected function parseSoapResponse($soapResponse);
}