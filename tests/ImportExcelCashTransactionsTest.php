<?php

namespace DPRMC\Tests;

use Carbon\Carbon;
use DPRMC\ClearStructure\Sentry\DataService\Services\ImportExcelCashTransactions;

/**
 * @runTestsInSeparateProcesses
 */
class ImportExcelCashTransactionsTest extends DprmcTestCase {


    /**
     * @test
     * @group cash
     */
    public function uploadingValidDataShouldReturnNoErrors() {
        $uatUrl  = getenv( 'UAT' );
        $prodUrl = getenv( 'PROD' );
        $user    = getenv( 'SENTRY_USER' );
        $pass    = getenv( 'SENTRY_PASS' );

        // This needs to be in the future, or you will get an error like:
        // "The settle date 2/2/2021 violates the account lock for portfolio 'STS'."
        $nextYear = Carbon::today()->addYear()->year;

        $data   = [];
        $data[] = [
            'Sentry ID' => '4883',
            'Import Action'              => 'New',
            'Portfolio Short Name'       => 'STS',
            'Security Identifier Scheme' => 'CUSIP',
            'Security Identifier'        => '86359ACC5',
            'Transaction Code'           => 'INADJ',
            'Trade Date'                 => $nextYear . '-01-27',
            'Settle Date'                => $nextYear . '-02-02',
            'Cash Receipt Date'          => $nextYear . '-02-02',
            'Signed Amount'              => '5.91',
            'Currency Code'              => 'USD',
        ];


        /**
         * @var \DPRMC\ClearStructure\Sentry\DataService\Services\ImportExcelResponse $importExcelResponse
         */
        $importExcelResponse = ImportExcelCashTransactions::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                                          ->setData( $data )
                                                          ->run();
        $errors              = $importExcelResponse->getErrors();

        $this->assertEmpty($errors);
    }


//    /**
//     * @test
//     * @group price
//     */
//    public function importsTwoPricesFromFile() {
//        $uatUrl           = getenv( 'UAT' );
//        $prodUrl          = getenv( 'PROD' );
//        $user             = getenv( 'SENTRY_USER' );
//        $pass             = getenv( 'SENTRY_PASS' );
//        $pathToImportFile = 'tests/testImportPrices.xlsx';
//
//        $importExcelResponse = ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
//                                                               ->setData( $pathToImportFile )
//                                                               ->run();
//
//        $this->assertEquals( 2, $importExcelResponse->response()[ 'num' ] );
//    }
//
//
//    /**
//     * @test
//     * @group price
//     */
//    public function importsTwoPricesFromArray() {
//        $uatUrl  = getenv( 'UAT' );
//        $prodUrl = getenv( 'PROD' );
//        $user    = getenv( 'SENTRY_USER' );
//        $pass    = getenv( 'SENTRY_PASS' );
//
//        $data   = [];
//        $data[] = [
//            'scheme_identifier'          => $this->validCusip,
//            'scheme_name'                => 'CUSIP',
//            'market_data_authority_name' => 'DB',
//            'action'                     => 'ADDUPDATE',
//            'as_of_date'                 => '1/1/2018',
//            'price'                      => 444,
//        ];
//        $data[] = [
//            'scheme_identifier'          => $this->validCusip,
//            'scheme_name'                => 'CUSIP',
//            'market_data_authority_name' => 'DB',
//            'action'                     => 'ADDUPDATE',
//            'as_of_date'                 => '1/1/2018',
//            'price'                      => 555,
//        ];
//
//        $importExcelResponse = ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
//                                                               ->setData( $data )
//                                                               ->run();
//
//        $this->assertEquals( 2, $importExcelResponse->response()[ 'num' ] );
//    }
//
//
//    /**
//     * @test
//     * @group price
//     */
//    public function importLotsOfRowsShouldTriggerSplitUploads() {
//        $numToImport     = 10;
//        $numPerSplitFile = 6;
//
//        $uatUrl  = getenv( 'UAT' );
//        $prodUrl = getenv( 'PROD' );
//        $user    = getenv( 'SENTRY_USER' );
//        $pass    = getenv( 'SENTRY_PASS' );
//
//        $data = [];
//        for ( $i = 0; $i < $numToImport; $i++ ):
//            $data[] = [
//                'scheme_identifier'          => $this->validCusip,
//                'scheme_name'                => 'CUSIP',
//                'market_data_authority_name' => 'DB',
//                'action'                     => 'ADDUPDATE',
//                'as_of_date'                 => '1/1/2018',
//                'price'                      => 444,
//            ];
//        endfor;
//
//        $importExcelResponse = ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
//                                                               ->setRowsForSplitFile( $numPerSplitFile )
//                                                               ->setData( $data )
//                                                               ->run();
//
//        $this->assertEquals( 10, $importExcelResponse->response()[ 'num' ] );
//    }
//
//
//    /**
//     * @test
//     * @group price
//     */
//    public function missingImportFileThrowsException() {
//
//        $uatUrl           = getenv( 'UAT' );
//        $prodUrl          = getenv( 'PROD' );
//        $user             = getenv( 'SENTRY_USER' );
//        $pass             = getenv( 'SENTRY_PASS' );
//        $pathToImportFile = 'tests/iDoNotExist.xlsx';
//        try {
//            ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
//                                            ->setData( $pathToImportFile )
//                                            ->run();
//        } catch ( \Exception $exception ) {
//
//        }
//        $this->assertInstanceOf( \Exception::class, $exception );
//    }
//
//
//    /**
//     * @test
//     * @group price
//     */
//    public function invalidDataTypeThrowsException() {
//        $uatUrl          = getenv( 'UAT' );
//        $prodUrl         = getenv( 'PROD' );
//        $user            = getenv( 'SENTRY_USER' );
//        $pass            = getenv( 'SENTRY_PASS' );
//        $invalidDataType = 42;
//        try {
//            ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
//                                            ->setData( $invalidDataType )
//                                            ->run();
//        } catch ( \Exception $exception ) {
//
//        }
//        $this->assertInstanceOf( \Exception::class, $exception );
//    }
//
//    /**
//     * @test
//     * @group price
//     */
//    public function prodUrlMatchingUatUrlShouldThrowException() {
//        $this->expectException( \Exception::class );
//        $uatUrl   = getenv( 'UAT' );
//        $prodUrl  = getenv( 'UAT' );
//        $user     = getenv( 'SENTRY_USER' );
//        $pass     = getenv( 'SENTRY_PASS' );
//        $fakeData = [];
//        ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
//                                        ->setData( $fakeData )
//                                        ->run();
//    }
//
//    /**
//     * @test
//     * @group price
//     */
//    public function notCallingSetDataShouldThrowException() {
//        $uatUrl  = getenv( 'UAT' );
//        $prodUrl = getenv( 'PROD' );
//        $user    = getenv( 'SENTRY_USER' );
//        $pass    = getenv( 'SENTRY_PASS' );
//        try {
//            ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
//                                            ->run();
//        } catch ( \Exception $exception ) {
//
//        }
//        $this->assertInstanceOf( \Exception::class, $exception );
//    }
//
//
//    /**
//     * @test
//     * @group neg
//     */
//    public function uploadingNegativePricesReturnsError() {
//        $uatUrl  = getenv( 'UAT' );
//        $prodUrl = getenv( 'PROD' );
//        $user    = getenv( 'SENTRY_USER' );
//        $pass    = getenv( 'SENTRY_PASS' );
//
//        $data   = [];
//        $data[] = [
//            'scheme_identifier'          => '93363RAF3',
//            'scheme_name'                => 'CUSIP',
//            'market_data_authority_name' => 'Final',
//            'action'                     => 'ADDUPDATE',
//            'as_of_date'                 => '1/1/2018',
//            'price'                      => -0.06,
//        ];
//
//        /**
//         * @var \DPRMC\ClearStructure\Sentry\DataService\Services\ImportExcelResponse $importExcelResponse
//         */
//        $importExcelResponse = ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
//                                                               ->setData( $data )
//                                                               ->run();
//
//        $errors = $importExcelResponse->getErrors();
//        $this->assertEquals( "Exception Message: Negative Prices are not allowed.", $errors[ 0 ] );
//    }
//
//
//
//
//
//    /**
//     *
//     * This wrecks the Sentry system and causes the "Conversion overflows" error.
//     */
////    public function uploadingHugePriceReturnsError() {
////        $uatUrl  = getenv( 'UAT' );
////        $prodUrl = getenv( 'PROD' );
////        $user    = getenv( 'USER' );
////        $pass    = getenv( 'PASS' );
////
////        $data   = [];
////        $data[] = [
////            'scheme_identifier'          => $this->validCusip,
////            'scheme_name'                => 'CUSIP',
////            'market_data_authority_name' => 'DB',
////            'action'                     => 'ADDUPDATE',
////            'as_of_date'                 => '1/1/2018',
////            'price'                      => 9999999999999999999,
////        ];
////
////        /**
////         * @var \DPRMC\ClearStructure\Sentry\DataService\Services\ImportExcelResponse $importExcelResponse
////         */
////        $importExcelResponse = ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
////                                                               ->setData( $data )
////                                                               ->run();
////        $errors              = $importExcelResponse->getErrors();
////        $this->assertEquals( "Exception Message: Conversion overflows.", $errors[ 0 ] );
////    }
//
//
//    /**
//     * @test
//     */
//    public function uploadingBadDateReturnsError() {
//        $uatUrl  = getenv( 'UAT' );
//        $prodUrl = getenv( 'PROD' );
//        $user    = getenv( 'SENTRY_USER' );
//        $pass    = getenv( 'SENTRY_PASS' );
//
//        $data   = [];
//        $data[] = [
//            'scheme_identifier'          => $this->validCusip,
//            'scheme_name'                => 'CUSIP',
//            'market_data_authority_name' => 'DB',
//            'action'                     => 'ADDUPDATE',
//            'as_of_date'                 => '1/32/2018',
//            'price'                      => 74,
//        ];
//
//        /**
//         * @var \DPRMC\ClearStructure\Sentry\DataService\Services\ImportExcelResponse $importExcelResponse
//         */
//        $importExcelResponse = ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
//                                                               ->setData( $data )
//                                                               ->run();
//        $errors              = $importExcelResponse->getErrors();
//        $this->assertEquals( "Exception Message: In Field (as_of_date) the following error occurred - String was not recognized as a valid DateTime.  Inner Exception Message: String was not recognized as a valid DateTime.", $errors[ 0 ] );
//    }
//
//
//    /**
//     * @test
//     * @group bad
//     */
//    public function uploadingMultipleBadDataSetsReturnsMultipleError() {
//        $uatUrl  = getenv( 'UAT' );
//        $prodUrl = getenv( 'PROD' );
//        $user    = getenv( 'SENTRY_USER' );
//        $pass    = getenv( 'SENTRY_PASS' );
//
//        $data   = [];
//        $data[] = [
//            'scheme_identifier'          => '00764MAK3',
//            'scheme_name'                => 'CUSIP',
//            'market_data_authority_name' => 'DB',
//            'action'                     => 'ADDUPDATE',
//            'as_of_date'                 => '1/32/2018',
//            'price'                      => 74,
//        ];
//        $data[] = [
//            'scheme_identifier'          => $this->validCusip,
//            'scheme_name'                => 'CUSIP',
//            'market_data_authority_name' => 'DB',
//            'action'                     => 'ADDUPDATE',
//            'as_of_date'                 => '1/32/2018',
//            'price'                      => 74,
//        ];
//        $data[] = [
//            'scheme_identifier'          => $this->validCusip,
//            'scheme_name'                => 'CUSIP',
//            'market_data_authority_name' => 'DB',
//            'action'                     => 'ADDUPDATE',
//            'as_of_date'                 => '1/32/2018',
//            'price'                      => 74,
//        ];
//        $data[] = [
//            'scheme_identifier'          => $this->validCusip,
//            'scheme_name'                => 'CUSIP',
//            'market_data_authority_name' => 'DB',
//            'action'                     => 'ADDUPDATE',
//            'as_of_date'                 => '1/32/2018',
//            'price'                      => 74,
//        ];
//
//        /**
//         * @var \DPRMC\ClearStructure\Sentry\DataService\Services\ImportExcelResponse $importExcelResponse
//         */
//        $importExcelResponse = ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
//                                                               ->setData( $data )
//                                                               ->run();
//        $errors              = $importExcelResponse->getErrors();
//
//        $this->assertCount( 4, $errors );
//    }
//
//
//    /**
//     * @test
//     */
//    public function getWarningsShouldReturnAnArray() {
//        $uatUrl  = getenv( 'UAT' );
//        $prodUrl = getenv( 'PROD' );
//        $user    = getenv( 'SENTRY_USER' );
//        $pass    = getenv( 'SENTRY_PASS' );
//
//        $data   = [];
//        $data[] = [
//            'scheme_identifier'          => $this->validCusip,
//            'scheme_name'                => 'CUSIP',
//            'market_data_authority_name' => 'DB',
//            'action'                     => 'ADDUPDATE',
//            'as_of_date'                 => '1/31/2018',
//            'price'                      => 74,
//        ];
//
//        /**
//         * @var \DPRMC\ClearStructure\Sentry\DataService\Services\ImportExcelResponse $importExcelResponse
//         */
//        $importExcelResponse = ImportExcelSecurityPricingUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
//                                                               ->setData( $data )
//                                                               ->run();
//        $warnings            = $importExcelResponse->getWarnings();
//        $this->assertIsArray( $warnings );
//    }


}