<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * Base class for all media api's tests
 * @author Simon Kerr
 * @version 1.0
 */

namespace ThinkBack\MediaBundle\Tests\MediaAPI;
use ThinkBack\MediaBundle\MediaAPI\MediaAPI;
use ThinkBack\MediaBundle\MediaAPI\AmazonAPI;
use ThinkBack\MediaBundle\MediaAPI\YouTubeAPI;
use ThinkBack\MediaBundle\Entity\MediaSelection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MediaAPITests extends WebTestCase {
    private $em;
    private $mediaAPI;
    private $mediaSelection;
    private $testAmazonAPI;
    
    protected function setUp(){
        $kernel = static::createKernel();
        $kernel->boot();
        $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        //for the mock object, need to provide a fully qualified path 
        $this->testAmazonAPI = $this->getMockBuilder('\\ThinkBack\\MediaBundle\\MediaAPI\\AmazonAPI')
                ->disableOriginalConstructor()
                ->setMethods(array(
                    'getListings'
                ))
                ->getMock();
        $this->testAmazonAPI->expects($this->any())
                ->method('getListings')
                ->will($this->returnValue(new \SimpleXMLElement('<?xml version="1.0" ?><items><item id="amazonLiveListings"></item></items>')));              
        
        
        $this->mediaAPI = new MediaAPI('true', $this->em, array(
            'amazonapi'     =>  $this->testAmazonAPI,
            'youtubeapi'    =>  2,
        ));
        
        $mediaType = $this->em->getRepository('ThinkBackMediaBundle:MediaType')->getMediaTypeBySlug('film');
        $this->mediaSelection = new MediaSelection();
        $this->mediaSelection->setMediaTypes($mediaType);
        
        
    }
    
    /**
     * @expectedException RuntimeException
     * @exceptedExceptionMessage api key not found 
     */
    public function testSetAPIStrategyOnNonExistentAPIThrowsException(){
        $response = $this->mediaAPI->setAPIStrategy('bogusAPIKey');
    }
    
    
    public function testNonExistentCachedListingsCallsLiveAPI(){
        /*
         * @params - class to mock, methods to mock, params to pass to constructor
         */
        $this->mediaAPI = $this->getMockBuilder('\\ThinkBack\\MediaBundle\\MediaAPI\\MediaAPI')
                ->setConstructorArgs(array(
                        'true', 
                        $this->em, 
                        array(
                            'amazonapi'     =>  $this->testAmazonAPI,
                            'youtubeapi'    =>  2,
                        )))
                ->setMethods(array('getCachedListings', 'cacheListings'))
                ->getMock();
        //tell the mocked object which methods to mock and what to return
        $this->mediaAPI->expects($this->once())
                ->method('getCachedListings')
                ->will($this->returnValue(null));

        $this->mediaAPI->expects($this->any())
                ->method('cacheListings')
                ->will($this->returnValue('cached live listings'));
        
        $response = $this->mediaAPI->getListings($this->mediaSelection);
        $this->assertEquals($response->item->attributes()->id, 'amazonLiveListings');
        
    }
    
    
    public function testExistingValidCachedListingsReturnedFromSameQueryReturnsListings(){
        $cachedXMLResponse = new \SimpleXMLElement('<?xml version="1.0" ?><items><item id="cachedListings"></item></items>');
        
        $this->mediaAPI = $this->getMockBuilder('\\ThinkBack\\MediaBundle\\MediaAPI\\MediaAPI')
                ->setConstructorArgs(array(
                        'true', 
                        $this->em, 
                        array(
                            'amazonapi'     =>  $this->testAmazonAPI,
                            'youtubeapi'    =>  2,
                        )))
                ->setMethods(array('getCachedListings'))
                ->getMock();
        //tell the mocked object which methods to mock and what to return
        $this->mediaAPI->expects($this->once())
                ->method('getCachedListings')
                ->will($this->returnValue($cachedXMLResponse));

        $response = $this->mediaAPI->getListings($this->mediaSelection);
        $this->assertEquals($response->item->attributes()->id, 'cachedListings');
    }
    
      

    
    
}

?>
