<?php

use DPRMC\ClearStructure\Sentry\DataService\Services\ImportExcelSecurityAttributeUpdate;

/**
 * @runTestsInSeparateProcesses
 */
class ImportExcelExceptionsTest extends DprmcTestCase {


    /**
     * @test
     * @group error
     */
    public function uatAndProdUrlsBeingIdenticalShouldThrowException() {
        $this->expectException( Exception::class );
        $uatUrl           = getenv( 'UAT' );
        $prodUrl          = getenv( 'UAT' );
        $user             = getenv( 'USER' );
        $pass             = getenv( 'PASS' );
        $pathToImportFile = 'tests/testImportAttributes.xlsx';

        ImportExcelSecurityAttributeUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                          ->setData( $pathToImportFile )
                                          ->run();
    }


    /**
     * @test
     * @group error
     */
    public function settingDataAsBooleanShouldThrowException() {
        $this->expectException( Exception::class );
        $uatUrl           = getenv( 'UAT' );
        $prodUrl          = getenv( 'PROD' );
        $user             = getenv( 'USER' );
        $pass             = getenv( 'PASS' );

        ImportExcelSecurityAttributeUpdate::init( $uatUrl, $prodUrl, $user, $pass, TRUE )
                                          ->setData( (object)'foo'  )
                                          ->run();
    }

}