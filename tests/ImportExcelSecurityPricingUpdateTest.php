<?php

use DPRMC\ClearStructure\Sentry\DataService\Services\ImportExcelSecurityPricingUpdate;

class ImportExcelSecurityPricingUpdateTest extends DprmcTestCase {

    /**
     * @test
     * @group price
     */
    public function importsTwoPricesFromFile() {
        $uatUrl           = getenv( 'UAT' );
        $prodUrl          = getenv( 'PROD' );
        $user             = getenv( 'USER' );
        $pass             = getenv( 'PASS' );
        $pathToImportFile = 'tests/testImportPrices.xlsx';

        $importExcelResponse = ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                                               ->setData( $pathToImportFile )
                                                               ->run();

        $this->assertEquals( 2, $importExcelResponse->response()[ 'num' ] );
    }


    /**
     * @test
     * @group price1
     */
    public function importsTwoPricesFromArray() {
        $uatUrl  = getenv( 'UAT' );
        $prodUrl = getenv( 'PROD' );
        $user    = getenv( 'USER' );
        $pass    = getenv( 'PASS' );

        $data   = [];
        $data[] = [
            'scheme_identifier'          => '00075QAF9',
            'scheme_name'                => 'CUSIP',
            'market_data_authority_name' => 'DB',
            'action'                     => 'ADDUPDATE',
            'as_of_date'                 => '1/1/2018',
            'price'                      => 444,
        ];
        $data[] = [
            'scheme_identifier'          => '00075XAG2',
            'scheme_name'                => 'CUSIP',
            'market_data_authority_name' => 'DB',
            'action'                     => 'ADDUPDATE',
            'as_of_date'                 => '1/1/2018',
            'price'                      => 555,
        ];

        $importExcelReponse = ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                                              ->setData( $data )
                                                              ->run();

        $this->assertEquals( 2, $importExcelReponse->response()[ 'num' ] );
    }


    /**
     * @test
     * @group price2
     */
    public function importLotsOfRowsShouldTriggerSplitUploads() {
        $numToImport = 10;
        $numPerSplitFile = 6;

        $uatUrl  = getenv( 'UAT' );
        $prodUrl = getenv( 'PROD' );
        $user    = getenv( 'USER' );
        $pass    = getenv( 'PASS' );

        $data   = [];
        for($i=0; $i<$numToImport; $i++):
        $data[] = [
            'scheme_identifier'          => '00075QAF9',
            'scheme_name'                => 'CUSIP',
            'market_data_authority_name' => 'DB',
            'action'                     => 'ADDUPDATE',
            'as_of_date'                 => '1/1/2018',
            'price'                      => 444,
        ];
        endfor;

        $importExcelResponse = ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                                              ->setRowsForSplitFile($numPerSplitFile)
                                                              ->setData( $data )
                                                              ->run();


        $this->assertEquals( 10, $importExcelResponse->response()[ 'num' ] );
    }


    /**
     * @test
     * @group price
     */
    public function missingImportFileThrowsException() {

        $uatUrl           = getenv( 'UAT' );
        $prodUrl          = getenv( 'PROD' );
        $user             = getenv( 'USER' );
        $pass             = getenv( 'PASS' );
        $pathToImportFile = 'tests/iDoNotExist.xlsx';
        try {
            ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                            ->setData( $pathToImportFile )
                                            ->run();
        } catch ( Exception $exception ) {

        }
        $this->assertInstanceOf( Exception::class, $exception );
    }


    /**
     * @test
     * @group price
     */
    public function invalidDataTypeThrowsException() {
        $uatUrl          = getenv( 'UAT' );
        $prodUrl         = getenv( 'PROD' );
        $user            = getenv( 'USER' );
        $pass            = getenv( 'PASS' );
        $invalidDataType = 42;
        try {
            ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                            ->setData( $invalidDataType )
                                            ->run();
        } catch ( Exception $exception ) {

        }
        $this->assertInstanceOf( Exception::class, $exception );
    }

    /**
     * @test
     * @group price
     */
    public function prodUrlMatchingUatUrlShouldThrowException() {
        $this->expectException( Exception::class );
        $uatUrl   = getenv( 'UAT' );
        $prodUrl  = getenv( 'UAT' );
        $user     = getenv( 'USER' );
        $pass     = getenv( 'PASS' );
        $fakeData = [];
        ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                        ->setData( $fakeData )
                                        ->run();
    }

    /**
     * @test
     * @group price
     */
    public function notCallingSetDataShouldThrowException() {
        $uatUrl  = getenv( 'UAT' );
        $prodUrl = getenv( 'PROD' );
        $user    = getenv( 'USER' );
        $pass    = getenv( 'PASS' );
        try {
            ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                            ->run();
        } catch ( Exception $exception ) {

        }
        $this->assertInstanceOf( Exception::class, $exception );
    }


}