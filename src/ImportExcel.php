<?php

namespace DPRMC\ClearStructure\Sentry\DataService\Services;

use Carbon\Carbon;
use DPRMC\Excel;

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
     * @var string Either path or array
     */
    protected $dataType = NULL;

    /**
     * ImportExcel constructor.
     *
     * @param $uatUrl
     * @param $prodUrl
     * @param $user
     * @param $pass
     */
    public function __construct($uatUrl, $prodUrl, $user, $pass) {
        $this->uatUrl   = $uatUrl;
        $this->prodUrl  = $prodUrl;
        $this->user     = $user;
        $this->password = $pass;
    }

    public static function init($uatUrl, $prodUrl, $user, $pass) {
        return new self($uatUrl, $prodUrl, $user, $pass);
    }

    /**
     * @param $data
     * @return $this
     * @throws \Exception
     */
    public function setData($data) {
        if ( is_array($data) ):
            $this->dataType = 'array';
            $this->data     = $data;
            return $this;
        endif;

        if ( is_string($data) ):
            $this->dataType = 'path';
            $this->data     = $data;
            return $this;
        endif;

        throw new \Exception("You need to pass a path to an Excel file, or a multi-dimensional array containing the data to be inserted.");
    }

    public function run($uat = FALSE) {
        switch ( $this->dataType ):
            case 'array':
                break;

            case 'path':
                return $this->importPath($this->data, $uat);
                break;

            default:
                throw new \Exception("You need to set your data source for the import.");
        endswitch;
    }

    /**
     * Provided a properly formatted Excel import file, this method will import that data into the Sentry system and
     * return the SOAP response.
     *
     * @param      $pathToImportFile string Used by file_get_contents(). Should be the path to a properly formatted
     *                               Excel import file. See ClearStructure docs for details.
     * @param bool $uat Do you want to run this import against the UAT (testing) site.
     *
     * @throws \Exception
     * @return mixed
     */
    public function importPath($pathToImportFile, $uat = FALSE) {

        if ( FALSE === file_exists($pathToImportFile) ):
            throw new \Exception("Unable to find the file located at [" . $pathToImportFile . "] and my directory is " . __DIR__);
        endif;

        $this->pathToImportFile = $pathToImportFile;

        $url  = $uat ? $this->uatUrl : $this->prodUrl;
        $wsdl = $url . '?WSDL';

        $stream = file_get_contents($this->pathToImportFile);

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

        $this->soapClient = new \SoapClient($wsdl, [
            'location' => $url,
            'uri'      => 'gibberish',
        ]);

        $soapResponse = $this->soapClient->$function($soapParameters);

        return $this->parseSoapResponse($soapResponse);
    }

    /**
     * @param $dataArray
     * @param bool $uat
     * @return mixed
     * @throws \Exception
     */
    protected function importArray($dataArray, $uat = FALSE) {
        $tempFile       = tmpfile();
        $options        = [
            'title'    => "Sentry Import File",
            'subject'  => "Import File",
            'category' => "import",
        ];
        $pathToTempFile = Excel::simple($dataArray, [], "Data to Import", $tempFile, $options);
        return $this->importPath($pathToTempFile, $uat);
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

        return [
            'time'    => Carbon::parse((string)$parsed->attributes()->time),
            'name'    => (string)$parsed->tables->table->attributes()->name,
            'num'     => (int)$parsed->tables->table->import,
            'runtime' => (float)$parsed->tables->table->RunTime,
            'errors'  => (array)$parsed->tables->table->errors,
        ];

    }
}