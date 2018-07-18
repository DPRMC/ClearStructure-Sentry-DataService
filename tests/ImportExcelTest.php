<?php

use DPRMC\ClearStructure\Sentry\DataService\Services\ImportExcel;

class ImportExcelTest extends DprmcTestCase {

    /**
     * @test
     */
    public function importsTwoPrices() {
        $uatUrl           = $_ENV[ 'UAT' ];
        $prodUrl          = $_ENV[ 'PROD' ];
        $user             = $_ENV[ 'USER' ];
        $pass             = $_ENV[ 'PASS' ];
        $pathToImportFile = 'tests/testImport.xlsx';
        $importExcel      = new ImportExcel( $uatUrl, $prodUrl, $user, $pass );
        $parsedResponse   = $importExcel->run( $pathToImportFile, false );

        $this->assertEquals( 2, $parsedResponse[ 'num' ] );
    }


}