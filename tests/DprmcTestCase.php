<?php

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