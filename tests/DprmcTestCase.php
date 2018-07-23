<?php

/**
 * @url https://stackoverflow.com/questions/42811164/class-phpunit-framework-testcase-not-found/42828632#42828632
 */
if ( ! class_exists( '\PHPUnit\Framework\TestCase' ) &&
     class_exists( '\PHPUnit_Framework_TestCase' ) ) {
    class_alias( '\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase' );
}

use PHPUnit\Framework\TestCase;

class DprmcTestCase extends TestCase {

    public function __construct() {
        parent::__construct();

        try {
            $dotenv = new Dotenv\Dotenv( __DIR__ );
            $dotenv->load();
        } catch ( Exception $exception ) {
            // Eat the exception.
            // When testing on travis-ci.org there will be no .env file.
        }

    }


}