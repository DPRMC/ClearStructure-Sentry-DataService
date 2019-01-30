<?php

namespace DPRMC\ClearStructure\Sentry\DataService\Services;

use Carbon\Carbon;

/**
 * Class ImportExcelResponse
 * @package DPRMC\ClearStructure\Sentry\DataService\Services
 */
class ImportExcelResponse {
    protected $soapResponse;
    protected $parsedResponse = [
        'num'      => 0,
        'runtime'  => 0,
        'errors'   => [],
        'warnings' => [],
    ];
    protected $pathToFile;

    /**
     * ImportExcelResponse constructor.
     * @param $soapResponse
     * @param $pathToFile
     */
    public function __construct( $soapResponse = NULL, $pathToFile = NULL ) {
        if ( $soapResponse ):
            $this->parseSoapResponse( $soapResponse );
        endif;

        if ( $pathToFile ):
            $this->pathToFile = $pathToFile;
        endif;
    }

    /**
     * @param ImportExcelResponse $importExcelResponse
     */
    public function addImportExcelResponseObject( ImportExcelResponse $importExcelResponse ) {
        $this->parsedResponse[ 'num' ]     += $importExcelResponse->parsedResponse[ 'num' ];
        $this->parsedResponse[ 'runtime' ] += $importExcelResponse->parsedResponse[ 'runtime' ];

        foreach ( $importExcelResponse->parsedResponse[ 'errors' ] as $error ):
            $this->parsedResponse[ 'errors' ][] = $error;
        endforeach;

        foreach ( $importExcelResponse->parsedResponse[ 'warnings' ] as $warning ):
            $this->parsedResponse[ 'warnings' ][] = $warning;
        endforeach;
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