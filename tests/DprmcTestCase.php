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




}