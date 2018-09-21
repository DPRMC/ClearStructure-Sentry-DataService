<?php

namespace DPRMC\ClearStructure\Sentry\DataService\Services;

use Carbon\Carbon;

/**
 * Class ImportExcelResponse
 * @package DPRMC\ClearStructure\Sentry\DataService\Services
 */
class ImportExcelResponse {
    protected $soapResponse;
    protected $parsedResponse;
    protected $pathToFile;

    /**
     * ImportExcelResponse constructor.
     * @param $soapResponse
     * @param $pathToFile
     */
    public function __construct( $soapResponse, $pathToFile ) {
        $this->parseSoapResponse( $soapResponse );
        $this->pathToFile = $pathToFile;
    }

    /**
     * @return array
     */
    public function response(): array {
        return $this->parsedResponse;
    }


    /**
     * @return string
     */
    public function path(): string {
        return $this->pathToFile;
    }


    /**
     * @param $soapResponse
     */
    protected function parseSoapResponse( $soapResponse ) {
        $parsed = new \SimpleXMLElement( $soapResponse->ImportExcelResult->any );

        $errors = [];

        if ( !is_null( $parsed->tables->table->errors->error ) ):
            foreach ( $parsed->tables->table->errors->error as $i => $error ):
                $errors[] = (string)$error;
            endforeach;
        endif;

        $warnings = [];
        if ( !is_null( $parsed->tables->table->warnings->warning ) ):
            foreach ( $parsed->tables->table->warnings->warning as $i => $warning ):
                $warnings[] = (string)$warning;
            endforeach;
        endif;


        $parsedResponse = [
            'time'     => Carbon::parse( (string)$parsed->attributes()->time ),
            'name'     => (string)$parsed->tables->table->attributes()->name,
            'num'      => (int)$parsed->tables->table->import,
            'runtime'  => (float)$parsed->tables->table->RunTime,
            'errors'   => $errors,
            'warnings' => $warnings,
        ];

        $this->parsedResponse = $parsedResponse;

    }

}