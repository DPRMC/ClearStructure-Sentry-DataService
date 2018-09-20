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

    // Passed by reference via the setPathVariable() method.
    protected $pathVariable = NULL;


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

    /**
     * Creates a single instance of the ImportExcel class.
     * @param $uatUrl
     * @param $prodUrl
     * @param $user
     * @param $pass
     * @param bool $uat
     * @return mixed
     * @throws \Exception
     */
    public final static function init($uatUrl, $prodUrl, $user, $pass, $uat = FALSE) {
        if ( NULL === static::$_instance ) {
            static::$_instance = new static($uatUrl, $prodUrl, $user, $pass, $uat);
        }

        return static::$_instance;
    }

    /**
     * Sets the data into this object that you want imported into Sentry.
     * @param $data
     * @return $this
     * @throws \Exception
     */
    public function setData( array $data ) {
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

    public function setPathVariable( &$path ) {
        if ( !is_string( $path ) ):
            throw new \Exception( "You need to pass a string as the path." );
        endif;

        $this->pathToImportFile = $path;
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

    /**
     * If you run ImportExcel using an array of data as the input, its sometimes useful for debugging to see the actual
     * file that is getting sent to Sentry.
     * @return mixed
     */
    abstract public function getExcelFile();

    abstract protected function importPath( string $pathToImportFile ): ImportExcelResponse;

    abstract protected function importArray(): ImportExcelResponse;

    /**
     * You can see below the parsed XML from Sentry isn't the cleanest, so this method pulls out the info I need into a
     * nicely formatted array.
     *
     * @param $soapResponse
     *
     * @return array
     */
    protected function parseSoapResponse( $soapResponse ): array {
        $parsed = new \SimpleXMLElement($soapResponse->ImportExcelResult->any);

        $errors = [];

        if ( !is_null($parsed->tables->table->errors->error) ):
            foreach ( $parsed->tables->table->errors->error as $i => $error ):
                $errors[] = (string)$error;
            endforeach;
        endif;

        $warnings = [];
        if ( !is_null($parsed->tables->table->warnings->warning) ):
            foreach ( $parsed->tables->table->warnings->warning as $i => $warning ):
                $warnings[] = (string)$warning;
            endforeach;
        endif;


        $parsedResponse = [
            'time'     => Carbon::parse((string)$parsed->attributes()->time),
            'name'     => (string)$parsed->tables->table->attributes()->name,
            'num'      => (int)$parsed->tables->table->import,
            'runtime'  => (float)$parsed->tables->table->RunTime,
            'errors'   => $errors,
            'warnings' => $warnings,
        ];

        return $parsedResponse;

    }
}