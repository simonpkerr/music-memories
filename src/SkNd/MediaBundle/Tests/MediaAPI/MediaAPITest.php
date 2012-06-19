<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * Base class for all media api's tests
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Tests\MediaAPI;
use SkNd\MediaBundle\MediaAPI\MediaAPI;
use SkNd\MediaBundle\MediaAPI\AmazonAPI;
use SkNd\MediaBundle\MediaAPI\YouTubeAPI;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Entity\MediaResource;
use SkNd\MediaBundle\Entity\MediaResourceCache;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session;

class MediaAPITests extends WebTestCase {
    private $em;
    private $mediaAPI;
    private $mediaSelection;
    private $testAmazonAPI;
    private $cachedXMLResponse;
    private $liveXMLResponse;
    private $mediaResource;
    private $session;
    
    protected function setUp(){
        $this->cachedXMLResponse = new \SimpleXMLElement('<?xml version="1.0" ?><items><item id="cachedData"></item></items>');
        $this->liveXMLResponse = new \SimpleXMLElement('<?xml version="1.0" ?><items><item id="amazonLiveData"></item></items>');
        
        $kernel = static::createKernel();
        $kernel->boot();
        $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        //for the mock object, need to provide a fully qualified path 
        $this->testAmazonAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\AmazonAPI')
                ->disableOriginalConstructor()
                ->setMethods(array(
                    'getListings',
                    'getDetails',
                ))
                ->getMock();
        //always make the getListings method of amazon api return the sample xml data
        $this->testAmazonAPI->expects($this->any())
                ->method('getListings')
                ->will($this->returnValue($this->liveXMLResponse));              
        
        //always make the getDetails method of amazon api return the sample xml data
        $this->testAmazonAPI->expects($this->any())
                ->method('getDetails')
                ->will($this->returnValue($this->liveXMLResponse));
        
        $this->session = $kernel->getContainer()->get('session');
        
        $this->mediaAPI = new MediaAPI('true', $this->em, $this->session, array(
            'amazonapi'     =>  $this->testAmazonAPI,
            'youtubeapi'    =>  2,
        ));
        
        $mediaType = $this->em->getRepository('SkNdMediaBundle:MediaType')->getMediaTypeBySlug('film');
        $this->mediaSelection = new MediaSelection();
        $this->mediaSelection->setMediaTypes($mediaType);
        
        $this->mediaResource = new MediaResource();
        $this->mediaResource->setAPI($this->em->getRepository('SkNdMediaBundle:API')->getAPIByName('amazonapi'));
        $this->mediaResource->setMediaType($this->mediaSelection->getMediaTypes());
        
        $this->session->set('mediaSelection', $this->mediaSelection);
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
        $this->mediaAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\MediaAPI')
                ->setConstructorArgs(array(
                        'true', 
                        $this->em, 
                        $this->session,
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
        
        $response = $this->mediaAPI->getListings();
        $this->assertEquals($response->item->attributes()->id, 'amazonLiveData');
        
    }
    
    
    public function testExistingValidCachedListingsReturnedFromSameQueryReturnsListings(){
    
        $this->mediaAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\MediaAPI')
                ->setConstructorArgs(array(
                        'true', 
                        $this->em, 
                        $this->session,
                        array(
                            'amazonapi'     =>  $this->testAmazonAPI,
                            'youtubeapi'    =>  2,
                        )))
                ->setMethods(array('getCachedListings'))
                ->getMock();
        //tell the mocked object which methods to mock and what to return
        $this->mediaAPI->expects($this->once())
                ->method('getCachedListings')
                ->will($this->returnValue($this->cachedXMLResponse));

        $response = $this->mediaAPI->getListings();
        $this->assertEquals($response->item->attributes()->id, 'cachedData');
    }
    
      
    public function testNonexistentMediaResourceCallsLiveAPI(){
        $this->mediaAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\MediaAPI')
                ->setConstructorArgs(array(
                        'true', 
                        $this->em,
                        $this->session,
                        array(
                            'amazonapi'     =>  $this->testAmazonAPI,
                            'youtubeapi'    =>  2,
                        )))
                ->setMethods(array(
                    'getMediaResource',
                    'cacheMediaResource'))
                ->getMock();
        //tell the mocked object which methods to mock and what to return
        $this->mediaAPI->expects($this->once())
                ->method('getMediaResource')
                ->will($this->returnValue(null));

        $response = $this->mediaAPI->getDetails(
                array('ItemId'  =>  '1'), 
                $this->mediaSelection);
        $this->assertEquals($response->item->attributes()->id, 'amazonLiveData');
    }
    
    public function testExistingMediaResourceAndNonexistentCachedResourceCallsLiveAPI(){
     
        $this->mediaAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\MediaAPI')
                ->setConstructorArgs(array(
                        'true', 
                        $this->em, 
                        $this->session,
                        array(
                            'amazonapi'     =>  $this->testAmazonAPI,
                            'youtubeapi'    =>  2,
                        )))
                ->setMethods(array(
                    'getMediaResource',
                    'cacheMediaResource'))
                ->getMock();
        //tell the mocked object which methods to mock and what to return
        $this->mediaAPI->expects($this->once())
                ->method('getMediaResource')
                ->will($this->returnValue($this->mediaResource));

        $response = $this->mediaAPI->getDetails(
                array('ItemId'  =>  '1'), 
                $this->mediaSelection);
        $this->assertEquals($response->item->attributes()->id, 'amazonLiveData');
    }
    
    public function testExistingMediaResourceAndExistingValidCachedResourceReturnsCache(){
        $cachedResource = new MediaResourceCache();
        $cachedResource->setXmlData($this->cachedXMLResponse->asXML());
        $cachedResource->setId($this->mediaResource->getId());
        $cachedResource->setDateCreated(new \DateTime("now"));
        $this->mediaResource->setMediaResourceCache($cachedResource);
        
        $this->mediaAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\MediaAPI')
                ->setConstructorArgs(array(
                        'true', 
                        $this->em, 
                        $this->session,
                        array(
                            'amazonapi'     =>  $this->testAmazonAPI,
                            'youtubeapi'    =>  2,
                        )))
                ->setMethods(array(
                    'getMediaResource',
                    'cacheMediaResource',
                    'deleteCachedResource'))
                ->getMock();
        //tell the mocked object which methods to mock and what to return
        $this->mediaAPI->expects($this->once())
                ->method('getMediaResource')
                ->will($this->returnValue($this->mediaResource));

        $response = $this->mediaAPI->getDetails(
                array('ItemId'  =>  '1'), 
                $this->mediaSelection);
        $this->assertEquals($response->item->attributes()->id, 'cachedData');
        
    }
    
    public function testProcessMediaResourcesWith1CachedAmazonResourceReturnsFalse(){
        
    }
    
    public function testProcessMediaResourcesWith2UncachedAmazonResourcesCallsLiveAPICachesResourceAndReturnsTrue(){
        
    }
    
    public function testProcessMediaResourcesWith5CachedAnd5UncachedAmazonResourcesUpdatesUncachedResourcesReturnsTrue(){
        
    }
       
    public function testProcessMediaResourcesWith5CachedAnd11UncachedAmazonResourcesUpdates10UncachedResourcesReturnsTrue(){
        
    }
    
    
    
}

?>
