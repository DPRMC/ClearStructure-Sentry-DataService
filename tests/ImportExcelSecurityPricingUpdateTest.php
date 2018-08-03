<?php

use DPRMC\ClearStructure\Sentry\DataService\Services\ImportExcelSecurityPricingUpdate;

class ImportExcelSecurityPricingUpdateTest extends DprmcTestCase {

    /**
     * @test
     */
    public function importsTwoPricesFromFile() {
        $uatUrl           = getenv('UAT');
        $prodUrl          = getenv('PROD');
        $user             = getenv('USER');
        $pass             = getenv('PASS');
        $pathToImportFile = 'tests/testImportPrices.xlsx';

        $parsedResponse = ImportExcelSecurityPricingUpdate::init($uatUrl, $prodUrl, $user, $pass, TRUE)
                                                          ->setData($pathToImportFile)
                                                          ->run();

        $this->assertEquals(2, $parsedResponse[ 'num' ]);
    }


    /**
     * @test
     */
    public function importsTwoPricesFromArray() {
        $uatUrl  = getenv('UAT');
        $prodUrl = getenv('PROD');
        $user    = getenv('USER');
        $pass    = getenv('PASS');

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

        $parsedResponse = ImportExcelSecurityPricingUpdate::init($uatUrl, $prodUrl, $user, $pass, TRUE)
                                                          ->setData($data)
                                                          ->run();

        $this->assertEquals(2, $parsedResponse[ 'num' ]);
    }


    /**
     * @test
     */
    public function missingImportFileThrowsException() {

        $uatUrl           = getenv('UAT');
        $prodUrl          = getenv('PROD');
        $user             = getenv('USER');
        $pass             = getenv('PASS');
        $pathToImportFile = 'tests/iDoNotExist.xlsx';
        try {
            ImportExcelSecurityPricingUpdate::init($uatUrl, $prodUrl, $user, $pass, TRUE)
                                            ->setData($pathToImportFile)
                                            ->run();
        } catch ( Exception $exception ) {

        }
        $this->assertInstanceOf(Exception::class, $exception);
    }


    /**
     * @test
     */
    public function invalidDataTypeThrowsException() {
        $uatUrl          = getenv('UAT');
        $prodUrl         = getenv('PROD');
        $user            = getenv('USER');
        $pass            = getenv('PASS');
        $invalidDataType = 42;
        try {
            ImportExcelSecurityPricingUpdate::init($uatUrl, $prodUrl, $user, $pass, TRUE)
                                            ->setData($invalidDataType)
                                            ->run();
        } catch ( Exception $exception ) {

        }
        $this->assertInstanceOf(Exception::class, $exception);
    }

    /**
     * @test
     */
    public function prodUrlMatchingUatUrlShouldThrowException() {
        $uatUrl   = getenv('UAT');
        $prodUrl  = getenv('UAT');
        $user     = getenv('USER');
        $pass     = getenv('PASS');
        $fakeData = [];
        try {
            ImportExcelSecurityPricingUpdate::init($uatUrl, $prodUrl, $user, $pass, TRUE)
                                            ->setData($fakeData)
                                            ->run();
        } catch ( Exception $exception ) {

        }
        $this->assertInstanceOf(Exception::class, $exception);
    }

    /**
     * @test
     */
    public function notCallingSetDataShouldThrowException() {
        $uatUrl  = getenv('UAT');
        $prodUrl = getenv('PROD');
        $user    = getenv('USER');
        $pass    = getenv('PASS');
        try {
            ImportExcelSecurityPricingUpdate::init($uatUrl, $prodUrl, $user, $pass, TRUE)
                                            ->run();
        } catch ( Exception $exception ) {

        }
        $this->assertInstanceOf(Exception::class, $exception);
    }


}