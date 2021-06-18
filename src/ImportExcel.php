<?php

namespace DPRMC\ClearStructure\Sentry\DataService\Services;

use DPRMC\Excel\Excel;

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
     * @var /SoapClient The client used to communicate with the Sentry API.
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
     * @var int The Sentry system will timeout if you try to upload too large of a file. If the dataset you want to upload has more rows than $numRowsForSplitFile, then this library will split the upload into smaller batches.
     */
    protected $numRowsForSplitFile = 500;

    /**
     * @var int The value for ini's default_socket_timeout. I set it arbitrarily large here, because I was consistently getting errors because Sentry's system was slow to respond.
     */
    protected $defaultSocketTimeout = 9999999;


    /**
     * ImportExcel constructor.
     * @param $uatUrl
     * @param $prodUrl
     * @param $user
     * @param $pass
     * @param bool $uat
     * @throws \Exception
     */
    public function __construct( $uatUrl, $prodUrl, $user, $pass, $uat = FALSE ) {

        if ( $uatUrl == $prodUrl ):
            throw new \Exception( "Your UAT url is the same as your PROD url. That could be dangerous." );
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
     * @param string|NULL $directoryForExcelFile
     * @return string
     * @throws \Exception
     */
    protected function getExcelFile( string $directoryForExcelFile = NULL ) {
        $tempFilename   = tempnam( $directoryForExcelFile, $this->excelFilePrefix );
        $tempFileHandle = fopen( $tempFilename, "w" );
        $metaData       = stream_get_meta_data( $tempFileHandle );
        $tempFilename   = $metaData[ 'uri' ] . '.xlsx';
        $options        = [
            'title'    => "Sentry Import File",
            'subject'  => "Import File",
            'category' => "import",
        ];
        return Excel::simple( $this->dataArray, [], $this->sheetName, $tempFilename, $options );
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
    public final static function init( $uatUrl, $prodUrl, $user, $pass, $uat = FALSE ) {
        return static::$_instance = new static( $uatUrl, $prodUrl, $user, $pass, $uat );
//        if ( NULL === static::$_instance ):
//            static::$_instance = new static( $uatUrl, $prodUrl, $user, $pass, $uat );
//        endif;
//
//        return static::$_instance;
    }

    /**
     * Sets the data into this object that you want imported into Sentry.
     * @param mixed $data
     * @return $this
     * @throws \Exception
     */
    public function setData( $data ) {
        if ( is_array( $data ) ):
            $this->dataType  = 'array';
            $this->dataArray = $data;
            return $this;
        endif;

        // PATH WAS PASSED IN.
        if ( is_string( $data ) ):
            if ( FALSE === file_exists( $data ) ):
                throw new \Exception( "Unable to find the file located at [" . $data . "] and my directory is " . __DIR__ );
            endif;

            $this->pathToImportFile = $data;
            $this->dataType         = 'path';
            return $this;
        endif;

        throw new \Exception( "You need to pass a path to an Excel file, or a multi-dimensional array containing the data to be inserted." );
    }


    /**
     * Use this method to tweak the size of the split files until you can complete the upload without errors.
     * @param int $numRows
     * @return $this
     */
    public function setRowsForSplitFile( int $numRows ) {
        $this->numRowsForSplitFile = $numRows;
        return $this;
    }

    /**
     * If for some reason you need to set a fixed socket timeout, use this method before you call run()
     * @param int $defaultSocketTimeoutInSeconds
     * @return $this
     */
    public function setDefaultSocketTimeout( int $defaultSocketTimeoutInSeconds ) {
        $this->defaultSocketTimeout = $defaultSocketTimeoutInSeconds;
        return $this;
    }

//    public function setPathVariable( &$path ) {
//        if ( !is_string( $path ) ):
//            throw new \Exception( "You need to pass a string as the path." );
//        endif;
//
//        $this->pathToImportFile = $path;
//    }

    /**
     * @return ImportExcelResponse
     * @throws \Exception
     */
    public function run(): ImportExcelResponse {
        $existingDefaultSocketTimeout = ini_get( 'default_socket_timeout' );
        ini_set( 'default_socket_timeout', $this->defaultSocketTimeout );

        switch ( $this->dataType ):
            case 'array':
                $importExcelResponse = $this->importArray( $this->dataArray );
                ini_set( 'default_socket_timeout', $existingDefaultSocketTimeout );
                return $importExcelResponse;

            case 'path':
                $importExcelResponse = $this->importPath( $this->pathToImportFile );
                ini_set( 'default_socket_timeout', $existingDefaultSocketTimeout );
                return $importExcelResponse;

            // @codeCoverageIgnoreStart
            // This should never be called, because an exception would be thrown earlier in the setData() method.
            default:
                ini_set( 'default_socket_timeout', $existingDefaultSocketTimeout );
                throw new \Exception( "You need to set your data source for the import." );
            // @codeCoverageIgnoreEnd
        endswitch;
    }


    /**
     * @param string $pathToImportFile
     * @return ImportExcelResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function importPath( string $pathToImportFile ): ImportExcelResponse {
        if ( FALSE === $this->importFileHasTooManyLines( $pathToImportFile ) ):
            $soapResponse = $this->sendToSentry( $pathToImportFile );
            return new ImportExcelResponse( $soapResponse, $pathToImportFile );
        endif;

        $tempFilePaths = Excel::splitSheet( $pathToImportFile, 0, $this->numRowsForSplitFile );

        $soapResponses = [];
        foreach ( $tempFilePaths as $i => $tempFilePath ):
            $soapResponses[ $tempFilePath ] = $this->sendToSentry( $tempFilePath );
        endforeach;

        return $this->consolidateSoapResponsesIntoImportExcelResponse( $soapResponses );

    }

    /**
     * @param string $pathToImportFile
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function importFileHasTooManyLines( string $pathToImportFile ): bool {
        // Determine how many lines are in the import file.
        // If the number of lines is greater than $this->numLinesPerSplitFile, then split it up and process each one.
        $numLinesInSheet = Excel::numLinesInSheet( $pathToImportFile );
        if ( $numLinesInSheet < $this->numRowsForSplitFile ):
            return FALSE;
        endif;
        return TRUE;
    }

    /**
     * @param string $pathToImportFile
     * @return \stdClass
     */
    protected function sendToSentry( string $pathToImportFile ): \stdClass {
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

        return $this->soapClient->$function( $soapParameters );
    }

    /**
     * @return ImportExcelResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function importArray(): ImportExcelResponse {
        $pathToTempFile = $this->getExcelFile();
        $response       = $this->importPath( $pathToTempFile );
        @unlink( $pathToTempFile );
        return $response;
    }


    /**
     * @param array $soapResponses
     * @return ImportExcelResponse
     */
    protected function consolidateSoapResponsesIntoImportExcelResponse( array $soapResponses ): ImportExcelResponse {
        $importExcelResponse = new ImportExcelResponse();
        foreach ( $soapResponses as $pathToFile => $soapResponse ):
            $newImportExcelResponse = new ImportExcelResponse( $soapResponse, $pathToFile );
            $importExcelResponse->addImportExcelResponseObject( $newImportExcelResponse );
        endforeach;
        return $importExcelResponse;
    }
}