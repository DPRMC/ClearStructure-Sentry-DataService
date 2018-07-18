<?php

use PHPUnit\Framework\TestCase;

class DprmcTestCase extends TestCase {

    public function __construct() {
        parent::__construct();

        $dotenv = new Dotenv\Dotenv( __DIR__ );
        $dotenv->load();
    }


}