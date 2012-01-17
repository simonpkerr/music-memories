<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * AmazonAPI tests
 * @author Simon Kerr
 * @version 1.0
 */


namespace ThinkBack\MediaBundle\Tests\Resources\MediaAPI;
use ThinkBack\MediaBundle\Resources\MediaAPI\YouTubeAPI;
require_once 'Zend/Loader.php';

class YouTubeAPITest extends \PHPUnit_Framework_TestCase {

    private $testContainer;
    private $params;
    
    protected function setUp(){
        \Zend_Loader::loadClass('Zend_Gdata_YouTube');
        
        $this->testContainer = new TestContainer2();
        $this->params = array(
            'keywords'  =>  'sample title',
            'decade'    =>  '1980',
            'media'     =>  'film',
            'genre'     =>  'all',
        );
    }
    
    /**
     * @expectedException RuntimeException 
     * @expectedExceptionMessage Could not connect to YouTube 
     */
    public function testNoResponseThrowsRuntimeException(){
        $ytObj = $this->getMock('\Zend_Gdata_YouTube',
                array(
                    'getVideoFeed'
                ));

        $ytObj->expects($this->any())
                ->method('getVideoFeed')
                ->will($this->returnValue(false));
        
        $yt = new YouTubeAPI($this->testContainer, $ytObj);
        $yt->getRequest($this->params);
    }
    
    /**
     * @expectedException LengthException 
     * @expectedExceptionMessage No results were returned
     */
    public function testEmptyResponseReturnsLengthException(){
        $ytObj = $this->getMock('\Zend_Gdata_YouTube',
                array(
                    'getVideoFeed'
                ));

        $ytObj->expects($this->any())
                ->method('getVideoFeed')
                ->will($this->returnValue(array()));
        
        $yt = new YouTubeAPI($this->testContainer, $ytObj);
        $yt->getRequest($this->params);
    }
    
    

}

/**
 * test container for mimicking the parameters in the 
 * actual container object for the controller
 */
class TestContainer2 {
    public $parameters = array(
        'amazon_public_key'     => 'apk',
        'amazon_uk_private_key' => 'aupk',
        'amazon_associate_tag'  => 'aat',
    );
}

?>
