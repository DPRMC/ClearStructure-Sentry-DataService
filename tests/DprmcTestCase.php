<?php

use PHPUnit\Framework\TestCase;

class DprmcTestCase extends TestCase {

    public function __construct( ?string $name = null, array $data = [], string $dataName = '' ) {
        parent::__construct( $name, $data, $dataName );

        $dotenv = new Dotenv\Dotenv( __DIR__ );
        $dotenv->load();
    }


}