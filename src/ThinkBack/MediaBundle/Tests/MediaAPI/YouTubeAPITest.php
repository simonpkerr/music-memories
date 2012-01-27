<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * AmazonAPI tests
 * @author Simon Kerr
 * @version 1.0
 */


namespace ThinkBack\MediaBundle\Tests\Resources\MediaAPI;
use ThinkBack\MediaBundle\Resources\YouTubeAPI;
require_once 'Zend/Loader.php';

class YouTubeAPITest extends \PHPUnit_Framework_TestCase {

    private $params;
    
    protected function setUp(){
        \Zend_Loader::loadClass('Zend_Gdata_YouTube');
        
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
        
        $yt = new YouTubeAPI($ytObj);
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
        
        $yt = new YouTubeAPI($ytObj);
        $yt->getRequest($this->params);
    }
    
    

}


?>
