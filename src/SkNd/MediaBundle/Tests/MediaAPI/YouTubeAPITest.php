<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * AmazonAPI tests
 * @author Simon Kerr
 * @version 1.0
 */


namespace SkNd\MediaBundle\Tests\MediaAPI;
use SkNd\MediaBundle\MediaAPI\YouTubeAPI;
use SkNd\MediaBundle\Entity\MediaSelection;

//require_once 'Zend/Loader.php';

class YouTubeAPITest extends \PHPUnit_Framework_TestCase {

    private $params;
    private $ms;
    private $ytObj;
    
    protected function setUp(){
        //\Zend_Loader::loadClass('Zend_Gdata_YouTube');
        $this->ytObj = $this->getMock('\Zend_Gdata_YouTube',
                array(
                    'getVideoFeed'
                ));
        
        $this->params = array(
            'keywords'  =>  'sample title',
            'decade'    =>  '1980s',
            'media'     =>  'film',
            'genre'     =>  'all',
        );
        
        $this->ms = $this->getMockBuilder('\\SkNd\\MediaBundle\\Entity\\MediaSelection')
                ->setMethods(array(
                    'getComputedKeywords'))
                ->getMock();
        
        $this->ms->expects($this->any())
                ->method('getComputedKeywords')
                ->will($this->returnValue('computed keywords'));
        
        $mt = $this->getMockBuilder('\\SkNd\\MediaBundle\\Entity\\MediaType')
                ->setMethods(array(
                    'getSlug'))
                ->getMock();
        
        $mt->expects($this->any())
                ->method('getSlug')
                ->will($this->returnValue('slug'));
                
        $this->ms->setMediaType($mt);
    }
    
    /**
     * @expectedException RuntimeException 
     * @expectedExceptionMessage Could not connect to YouTube 
     */
    public function testNoResponseThrowsRuntimeException(){
        $this->ytObj->expects($this->any())
                ->method('getVideoFeed')
                ->will($this->returnValue(false));
        
        $yt = new YouTubeAPI();
        $yt->setRequestObject($this->ytObj);
        $yt->getListings($this->ms);
    }
    
    /**
     * @expectedException LengthException 
     * @expectedExceptionMessage No results were returned
     */
    public function testEmptyResponseReturnsLengthException(){
        $this->ytObj->expects($this->any())
                ->method('getVideoFeed')
                ->will($this->returnValue(array()));
        
        $yt = new YouTubeAPI();
        $yt->setRequestObject($this->ytObj);
        $yt->getListings($this->ms);
    }
    
    /**
     * @expectedException InvalidArgumentException 
     * @expectedExceptionMessage No id was passed to Youtube
     */
    public function testGetYouTubeDetailsWithNoParamsThrowsException(){
              
        $yt = new YouTubeAPI($this->ytObj);
        $yt->getDetails(array());
    }
    
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not connect to YouTube
     */
    public function testGetYouTubeDetailsNoConnectionThrowsException(){
        $ytObj = $this->getMock('\Zend_Gdata_YouTube',
                array(
                    'getVideoEntry'
                ));

        $ytObj->expects($this->any())
                ->method('getVideoEntry')
                ->will($this->returnValue(false));
        
        $yt = new YouTubeAPI();
        $yt->setRequestObject($ytObj);
        $yt->getDetails(array('ItemId' => '1'));
    }
    
    /**
     * @expectedException LengthException 
     * @expectedExceptionMessage No results were returned
     */
    public function testGetYouTubeDetailsNoResultsThrowsException(){
        $ytObj = $this->getMock('\Zend_Gdata_YouTube',
                array(
                    'getVideoEntry'
                ));

        $ytObj->expects($this->any())
                ->method('getVideoEntry')
                ->will($this->returnValue(array()));
        
        $yt = new YouTubeAPI();
        $yt->setRequestObject($ytObj);
        $yt->getDetails(array('ItemId' => '1'));
    }
    
    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not connect to YouTube
     */
    public function testGetBatchJobReturnsFalseThrowsException(){
        $ytObj = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\TestYouTubeRequest')
                ->setMethods(array(
                    'post'
                ))
                ->getMock();

        $ytObj->expects($this->any())
                ->method('post')
                ->will($this->returnValue(false));
        
        $yt = new YouTubeAPI();
        $yt->setRequestObject($ytObj);
        $yt->getBatch(array('ItemId' => '1'));
    }
    
    /**
     * @expectedException RuntimeException 
     * @expectedExceptionMessage Could not parse response
     */
    public function testGetBatchNoResultsThrowsException(){
        $ytObj = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\TestYouTubeRequest')
                ->setMethods(array(
                    'post'
                ))
                ->getMock();
        
        $mockResponse = $this->getMockBuilder('\Zend_Http_Response')
                ->disableOriginalConstructor()             
                ->setMethods(array(
                    'getStatus',
                    'getBody'
                ))
                ->getMock();
        $mockResponse->expects($this->any())
                ->method('getStatus')
                ->will($this->returnValue(200));//indicating valid response
        
        $mockResponse->expects($this->any())
                ->method('getBody')
                ->will($this->returnValue(array()));//indicating valid response

        $ytObj->expects($this->any())
                ->method('post')
                ->will($this->returnValue($mockResponse));
        
        $yt = new YouTubeAPI();
        $yt->setRequestObject($ytObj);
        $yt->getBatch(array('ItemId' => '1'));
    }
    
    /**
     * @expectedException RuntimeException 
     * @expectedExceptionMessage A problem occurred with the response
     */
    public function testGetBatchReturnsNoSuccessStatusThrowsException(){
        $ytObj = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\TestYouTubeRequest')
                ->setMethods(array(
                    'post'
                ))
                ->getMock();
        
        $mockResponse = $this->getMockBuilder('\Zend_Http_Response')
                ->disableOriginalConstructor()             
                ->setMethods(array(
                    'getStatus',
                    'getBody'
                ))
                ->getMock();
        $mockResponse->expects($this->any())
                ->method('getStatus')
                ->will($this->returnValue(400));//indicating bad request
        
        $mockResponse->expects($this->any())
                ->method('getBody')
                ->will($this->returnValue(array()));//indicating valid response

        $ytObj->expects($this->any())
                ->method('post')
                ->will($this->returnValue($mockResponse));
        
        $yt = new YouTubeAPI();
        $yt->setRequestObject($ytObj);
        $yt->getBatch(array('ItemId' => '1'));
    }
    

}


?>
