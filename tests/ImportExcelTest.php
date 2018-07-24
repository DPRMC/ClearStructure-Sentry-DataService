<?php

use DPRMC\ClearStructure\Sentry\DataService\Services\ImportExcel;

class ImportExcelTest extends DprmcTestCase {

    /**
     * @test
     */
    public function testWSDL() {
        echo "\n\nSTART testWSDL\n";
        $uatUrl   = getenv( 'UAT' );
        var_dump( $uatUrl );
        $contents = file_get_contents( $uatUrl );
        $this->assertFalse( empty( $contents ) );
        echo "\n\nEND testWSDL\n";
    }

    /**
     * @test
     */
    public function importsTwoPrices() {
        $uatUrl           = getenv( 'UAT' );
        $prodUrl          = getenv( 'PROD' );
        $user             = getenv( 'USER' );
        $pass             = getenv( 'PASS' );
        $pathToImportFile = 'tests/testImport.xlsx';
        $importExcel      = new ImportExcel( $uatUrl, $prodUrl, $user, $pass );
        try {
            $parsedResponse = $importExcel->run( $pathToImportFile, true );
        } catch ( Exception $exception ) {
            echo "\n\n\n";
            echo $exception->getTraceAsString();
            echo $exception->getMessage();
            echo "\n\n\n";
        }


        $this->assertEquals( 2, $parsedResponse[ 'num' ] );
    }


    /**
     * @test
     */
    public function missingImportFileThrowsException() {

        $uatUrl           = getenv( 'UAT' );
        $prodUrl          = getenv( 'PROD' );
        $user             = getenv( 'USER' );
        $pass             = getenv( 'PASS' );
        $pathToImportFile = 'tests/iDoNotExist.xlsx';
        $importExcel      = new ImportExcel( $uatUrl, $prodUrl, $user, $pass );
        try {
            $parsedResponse = $importExcel->run( $pathToImportFile, false );
        } catch ( Exception $exception ) {

        }
        $this->assertInstanceOf( Exception::class, $exception );
    }
}