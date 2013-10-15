<?php

/*
 Selenium tests for ajax functionality
 */

/**
 * Description of MWCSeleniumTest
 *
 * @author Simon Kerr
 * @copyright (c) 2013, Simon kerr
 */
namespace SkNd\UserBundle\Tests\Controller;
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class MWCSeleniumTest extends PHPUnit_Extensions_SeleniumTestCase {
    protected function setUp(){
        $this->setBrowser('*firefox');
        $this->setBrowserUrl('http://localhost/SkNd/web/app_dev.php');
    }
    
    public function testTitle(){
        $this->open('http://localhost/SkNd/web/app_dev.php');
        $this->assertTitle('noodleDig – Helps you find stuff you loved');
    }
}

?>
