<?php

use DPRMC\ClearStructure\Sentry\DataService\Services\DeleteExcelSecurityPricing;

/**
 * @runTestsInSeparateProcesses
 */
class DeleteExcelSecurityPricingTest extends DprmcTestCase {


    /**
     * @test
     * @group invalid
     * @group delete1
     */
    public function deletingInvalidCUSIPReturnsError() {

        $uatUrl  = getenv( 'UAT' );
        $prodUrl = getenv( 'PROD' );
        $user    = getenv( 'SENTRY_USER' );
        $pass    = getenv( 'SENTRY_PASS' );

        $data   = [];
        $data[] = [
            'scheme_identifier'          => $this->invalidCusip,
            'scheme_name'                => 'CUSIP',
            'market_data_authority_name' => 'DB',
            'action'                     => 'DELETE',
            'as_of_date'                 => '1/1/2018',
        ];

        $importExcelResponse = DeleteExcelSecurityPricing::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                                         ->setData( $data )
                                                         ->run();
        $errors              = $importExcelResponse->getErrors();
        $this->assertEquals( "Exception Message: This security doesn't exist - ZZZ75QAF9.", $errors[ 0 ] );
    }


    /**
     * @test
     * @group price
     * @group delete
     */
    public function importsTwoPricesFromFile() {
        $uatUrl           = getenv( 'UAT' );
        $prodUrl          = getenv( 'PROD' );
        $user             = getenv( 'SENTRY_USER' );
        $pass             = getenv( 'SENTRY_PASS' );
        $pathToImportFile = 'tests/testDeletePrices.xlsx';

        $importExcelResponse = DeleteExcelSecurityPricing::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                                         ->setData( $pathToImportFile )
                                                         ->run();

        $this->assertEquals( 2, $importExcelResponse->response()[ 'num' ] );
    }


    /**
     * @test
     * @group price
     * @group delete
     */
    public function deleteTwoPricesFromArray() {
        $uatUrl  = getenv( 'UAT' );
        $prodUrl = getenv( 'PROD' );
        $user    = getenv( 'SENTRY_USER' );
        $pass    = getenv( 'SENTRY_PASS' );

        $data   = [];
        $data[] = [
            'scheme_identifier'          => $this->validCusip,
            'scheme_name'                => 'CUSIP',
            'market_data_authority_name' => 'DB',
            'action'                     => 'DELETE',
            'as_of_date'                 => '1/1/2018',
        ];
        $data[] = [
            'scheme_identifier'          => $this->validCusip,
            'scheme_name'                => 'CUSIP',
            'market_data_authority_name' => 'DB',
            'action'                     => 'DELETE',
            'as_of_date'                 => '1/2/2018',
        ];

        $importExcelResponse = DeleteExcelSecurityPricing::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                                         ->setData( $data )
                                                         ->run();

        $this->assertEquals( 2, $importExcelResponse->response()[ 'num' ] );
    }


    /**
     * @test
     * @group price
     * @group delete
     */
    public function deleteLotsOfRowsShouldTriggerSplitUploads() {
        $numToImport     = 10;
        $numPerSplitFile = 6;

        $uatUrl  = getenv( 'UAT' );
        $prodUrl = getenv( 'PROD' );
        $user    = getenv( 'SENTRY_USER' );
        $pass    = getenv( 'SENTRY_PASS' );

        $date = \Carbon\Carbon::create( 2020, 1, 1, 0, 0, 0, 'America/New_York' );

        $data = [];
        for ( $i = 0; $i < $numToImport; $i++ ):
            $data[] = [
                'scheme_identifier'          => $this->validCusip,
                'scheme_name'                => 'CUSIP',
                'market_data_authority_name' => 'DB',
                'action'                     => 'ADDUPDATE',
                'as_of_date'                 => $date->format( 'j/n/Y' ),
            ];

            $date = $date->copy()->addDay();
        endfor;

        $importExcelResponse = DeleteExcelSecurityPricing::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                                         ->setRowsForSplitFile( $numPerSplitFile )
                                                         ->setData( $data )
                                                         ->run();

        $this->assertEquals( 10, $importExcelResponse->response()[ 'num' ] );
    }
}