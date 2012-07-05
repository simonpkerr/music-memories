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
    private $testYouTubeAPI;
    private $cachedXMLResponse;
    private $liveXMLResponse;
    private $mediaResource;
    private $session;
    private $mediaAPIService;
    
    protected function setUp(){
  
        $this->cachedXMLResponse = new \SimpleXMLElement('<?xml version="1.0" ?><items><item id="cachedData"></item></items>');
        $this->liveXMLResponse = new \SimpleXMLElement('<?xml version="1.0" ?><items><item id="amazonLiveData"></item></items>');
        
        $kernel = static::createKernel();
        $kernel->boot();
        
        $this->session = $kernel->getContainer()->get('session');
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
        
        $this->testYouTubeAPI = new YouTubeAPI(new \SkNd\MediaBundle\MediaAPI\TestYouTubeRequest());
        
        $this->mediaAPI = new MediaAPI('true', $this->em, $this->session, array(
            'amazonapi'     =>  $this->testAmazonAPI,
            'youtubeapi'    =>  $this->testYouTubeAPI,
        ));
        
        $this->mediaSelection = $this->mediaAPI->getMediaSelection(array(
            'media' => 'film'
        ));
        
        $this->mediaResource = new MediaResource();
        $this->mediaResource->setId('testMediaResource');
        $this->mediaResource->setAPI($this->em->getRepository('SkNdMediaBundle:API')->getAPIByName('amazonapi'));
        $this->mediaResource->setMediaType($this->mediaSelection->getMediaType());
        
        $this->mediaAPIService = $kernel->getContainer()->get('sk_nd_media.mediaapi');
        
        //$this->session->set('mediaSelection', $this->mediaSelection);
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
                            'youtubeapi'    =>  $this->testYouTubeAPI,
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
                            'youtubeapi'    =>  $this->testYouTubeAPI,
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
                            'youtubeapi'    =>  $this->testYouTubeAPI,
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
                array('ItemId'  =>  '1'));
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
                            'youtubeapi'    =>  $this->testYouTubeAPI,
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
                array('ItemId'  =>  '1'));
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
                            'youtubeapi'    =>  $this->testYouTubeAPI,
                        )))
                ->setMethods(array(
                    'getMediaResource',
                    'cacheMediaResource',
                    ))
                ->getMock();
        //tell the mocked object which methods to mock and what to return
        $this->mediaAPI->expects($this->once())
                ->method('getMediaResource')
                ->will($this->returnValue($this->mediaResource));

        $response = $this->mediaAPI->getDetails(
                array('ItemId'  =>  '1'));
        $this->assertEquals($response->item->attributes()->id, 'cachedData');
        
    }
    
    public function testCacheMediaResourceWithMoreSpecificMediaSelectionUpdatesMediaResource(){
        //get a media resource that has vague params, set specific params on the media selection entity
        //then update the media resource
        $cachedResource = new MediaResourceCache();
        $cachedResource->setXmlData($this->cachedXMLResponse->asXML());
        $cachedResource->setId($this->mediaResource->getId());
        $cachedResource->setDateCreated(new \DateTime("now"));
        $this->mediaResource->setMediaResourceCache($cachedResource);
        
        //set more specific params on media selection
        $this->mediaAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\MediaAPI')
                ->setConstructorArgs(array(
                        'true', 
                        $this->em, 
                        $this->session,
                        array(
                            'amazonapi'     =>  $this->testAmazonAPI,
                            'youtubeapi'    =>  $this->testYouTubeAPI,
                        )))
                ->setMethods(array(
                    'getMediaResource',
                    'flush',
                    ))
                ->getMock();
        //tell the mocked object which methods to mock and what to return
        $this->mediaAPI->expects($this->once())
                ->method('getMediaResource')
                ->will($this->returnValue($this->mediaResource));
        

        $this->mediaAPI->getMediaSelection(array(
            'media'  => 'film',
            'decade' => '1980s',
            'genre'  => 'drama',
        ));
        
        $response = $this->mediaAPI->getDetails(
                array('ItemId'  =>  '1'));
        
        $this->assertEquals($this->mediaResource->getDecade()->getDecadeName(), '1980');
    }
    
    public function testProcessMediaResourcesWith1CachedAmazonResourceReturnsFalse(){
        //add a media resource and cached record first
        //then update the media resource
        $cachedResource = new MediaResourceCache();
        $cachedResource->setXmlData($this->cachedXMLResponse->asXML());
        $cachedResource->setId($this->mediaResource->getId());
        $cachedResource->setDateCreated(new \DateTime("now"));
        $this->mediaResource->setMediaResourceCache($cachedResource);
        
        $mediaResources = new \Doctrine\Common\Collections\ArrayCollection();
        $mediaResources->add($this->mediaResource);
            
        $updatesMade = $this->mediaAPIService->processMediaResources($mediaResources);
        
        $this->assertEquals($updatesMade, false);
        
    }
    
    public function testProcessMediaResourcesWith1CachedOutOfDateAmazonResourceCallsLiveAPICachesResourcesAndReturnsTrue(){
        //set more specific params on media selection
        $this->mediaAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\MediaAPI')
                ->setConstructorArgs(array(
                        'true', 
                        $this->em, 
                        $this->session,
                        array(
                            'amazonapi'     =>  $this->testAmazonAPI,
                            'youtubeapi'    =>  $this->testYouTubeAPI,
                        )))
                ->setMethods(array(
                    'flush',
                    'cacheMediaResourceBatch',
                    ))
                ->getMock();
               
        $cachedResource = new MediaResourceCache();
        $cachedResource->setXmlData($this->cachedXMLResponse->asXML());
        $cachedResource->setId($this->mediaResource->getId());
        $cachedResource->setDateCreated(new \DateTime("1st jan 1980"));
        $this->mediaResource->setMediaResourceCache($cachedResource);
        
        $mediaResources = new \Doctrine\Common\Collections\ArrayCollection();
        $mediaResources->add($this->mediaResource);
            
        $updatesMade = $this->mediaAPI->processMediaResources($mediaResources);
        
        $this->assertEquals($updatesMade, true);
    }
    
    public function testGetAmazonListingRecommendationsWhenCallingLiveAPIReturnsResponse(){
        
    }
    
    public function testGetAmazonListingRecommendationsWhenGettingCachedListingsReturnsResponse(){
        
    }
    
    public function testGetAmazonDetailRecommendationsWhenCallingLiveAPIReturnsResponse(){
        
    }
    
    public function testGetAmazonDetailsRecommendationsWhenNoExactMatchesButGeneralMatchesReturnsResponse(){
        
    }
    
    
    public function testGetRecommendationsOnExactParamsReturnsData(){
        
    }
    
    public function testGetRecommendationsOnGenericParamsReturnsData(){
        
    }
    
    public function testGetRecommendationsOnAgeReturnsData(){
        
    }
    
    public function testGetRecommendationsOnValidAmazonRecordsReturnsData(){
        
    }
    
    public function testGetRecommendationsOnTimestampExpiredAmazonRecordsLooksUpAmazonData(){
        
    }
    
    
    
    
}

?>
