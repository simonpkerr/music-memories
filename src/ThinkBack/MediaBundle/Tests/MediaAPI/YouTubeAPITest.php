<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * AmazonAPI tests
 * @author Simon Kerr
 * @version 1.0
 */


namespace ThinkBack\MediaBundle\Tests\MediaAPI;
use ThinkBack\MediaBundle\MediaAPI\YouTubeAPI;

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
        
        $yt = new YouTubeAPI();
        $yt->setRequestObject($ytObj);
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
        
        $yt = new YouTubeAPI();
        $yt->setRequestObject($ytObj);
        $yt->getRequest($this->params);
    }
    
    public function testSimpleParametersReturnsURL(){
        $ytObj = $this->getMock('\Zend_Gdata_YouTube',
                array(
                    'getVideoFeed'
                ));

        $ytObj->expects($this->any())
                ->method('getVideoFeed')
                ->will($this->returnValue(array()));
        
        $this->params['keywords'] = "sherlock";
        
        $yt = new YouTubeAPI();
        $yt->setRequestObject($ytObj);
        $yt->getRequest($this->params);
        $this->assertEquals("http://gdata.youtube.com/feeds/api/videos/-/Film%7CEntertainment?max-results=25&q=sherlock", $yt->)
    }

}


?>
