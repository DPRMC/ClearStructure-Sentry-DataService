<?php

use DPRMC\ClearStructure\Sentry\DataService\Services\ImportExcelSecurityAttributeUpdate;

class ImportExcelSecurityAttributeUpdateTest extends DprmcTestCase {


    /**
     * @test
     */
    public function importsTwoAttributesFromFile() {
        $uatUrl           = getenv('UAT');
        $prodUrl          = getenv('PROD');
        $user             = getenv('USER');
        $pass             = getenv('PASS');
        $pathToImportFile = 'tests/testImportAttributes.xlsx';

        $importExcelReponse = ImportExcelSecurityAttributeUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                                                ->setData($pathToImportFile)
                                                                ->run();

        $this->assertEquals( 2, $importExcelReponse->response()[ 'num' ] );
    }

    /**
     * @test
     */
    public function importsTwoAttributesFromArray() {
        $uatUrl  = getenv('UAT');
        $prodUrl = getenv('PROD');
        $user    = getenv('USER');
        $pass    = getenv('PASS');

        $data   = [];
        $data[] = [
            'scheme_identifier' => '00075QAF9',
            'scheme_name'       => 'CUSIP',
            'start_date'        => '8/1/2018',
            'Pricing Vendor'    => 'IPS',
        ];
        $data[] = [
            'scheme_identifier' => '004421CU5',
            'scheme_name'       => 'CUSIP',
            'start_date'        => '8/1/2018',
            'Pricing Vendor'    => 'Stifel',
        ];

        $importExcelReponse = ImportExcelSecurityAttributeUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                                                ->setData($data)
                                                                ->run();

        $this->assertEquals( 2, $importExcelReponse->response()[ 'num' ] );
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
            ImportExcelSecurityAttributeUpdate::init($uatUrl, $prodUrl, $user, $pass, TRUE)
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
            ImportExcelSecurityAttributeUpdate::init($uatUrl, $prodUrl, $user, $pass, TRUE)
                                              ->setData($invalidDataType)
                                              ->run();
        } catch ( Exception $exception ) {

        }
        $this->assertInstanceOf(Exception::class, $exception);
    }


    /**
     * @test
     */
    public function importsTwoAttributesWithInvalidValueFromArrayShouldThrowException() {
        $uatUrl  = getenv('UAT');
        $prodUrl = getenv('PROD');
        $user    = getenv('USER');
        $pass    = getenv('PASS');

        $data   = [];
        $data[] = [
            'scheme_identifier' => '00075QAF9',
            'scheme_name'       => 'CUSIP',
            'start_date'        => '8/8/2018',
            'Pricing Vendor'    => 'aaaaaaaaaaa',
        ];
        $data[] = [
            'scheme_identifier' => '004421CU5',
            'scheme_name'       => 'CUSIP',
            'start_date'        => '8/1/2018',
            'Pricing Vendor'    => 'Stifel',
        ];

        $importExcelReponse = ImportExcelSecurityAttributeUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                                                ->setData($data)
                                                                ->run();

        $this->assertCount( 1, $importExcelReponse->response()[ 'warnings' ] );
    }
}