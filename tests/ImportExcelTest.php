<?php

use DPRMC\ClearStructure\Sentry\DataService\Services\ImportExcel;

class ImportExcelTest extends DprmcTestCase {

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
        $parsedResponse   = $importExcel->run( $pathToImportFile, false );

        $this->assertEquals( 2, $parsedResponse[ 'num' ] );
    }


    /**
     * @test
     */
    public function missingImportFileThrowsException() {
        $this->expectException( 'Exception' );
        $uatUrl           = getenv( 'UAT' );
        $prodUrl          = getenv( 'PROD' );
        $user             = getenv( 'USER' );
        $pass             = getenv( 'PASS' );
        $pathToImportFile = 'tests/iDoNotExist.xlsx';
        $importExcel      = new ImportExcel( $uatUrl, $prodUrl, $user, $pass );
        $parsedResponse   = $importExcel->run( $pathToImportFile, false );
    }
}