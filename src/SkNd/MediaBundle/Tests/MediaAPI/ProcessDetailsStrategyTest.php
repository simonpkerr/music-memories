<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * ProcessDetailsStrategy test to test basic
 * aspects of getting, processing and caching details
 * for items
 * @author Simon Kerr
 * @version 1.0
 * Run UserBundle Fixtures first
 * php app/console doctrine:fixtures:load --fixtures=src/SkNd/UserBundle/DataFixtures/ORM --append
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

class ProcessDetailsStrategyTest extends WebTestCase {
    private $mediaAPI;
    private $mediaSelection;
    private $testAmazonAPI;
    private $testYouTubeAPI;
    private $cachedXMLResponse;
    private $liveXMLResponse;
    private $mediaResource;
    
    protected static $kernel;
    protected static $em;
    protected static $session;
    
    
    public static function setUpBeforeClass(){
        self::$kernel = static::createKernel();
        self::$kernel->boot();
        self::$em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        self::$session = self::$kernel->getContainer()->get('session');
        
        $loadUsers = new \SkNd\UserBundle\DataFixtures\ORM\LoadUsers();
        $loadUsers->setContainer(self::$kernel->getContainer());
        $loadUsers->load(self::$em);
    }
    
    public static function tearDownAfterClass(){
        self::$kernel = null;
        self::$em = null;
        self::$session = null;
    }
    
    protected function setUp(){
  
        $this->cachedXMLResponse = new \SimpleXMLElement('<?xml version="1.0" ?><items><item id="cachedData"></item></items>');
        $this->liveXMLResponse = new \SimpleXMLElement('<?xml version="1.0" ?><items><item id="liveData"></item></items>');
        
        //for the mock object, need to provide a fully qualified path 
        $this->testAmazonAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\AmazonAPI')
                ->disableOriginalConstructor()
                ->setMethods(array(
                    'getListings',
                    'getDetails',
                    'getImageUrlFromXML',
                    'getItemTitleFromXML',
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
        
        $this->testAmazonAPI->expects($this->any())
                ->method('getImageUrlFromXML')
                ->will($this->returnValue('imageUrl'));
        
        $this->testAmazonAPI->expects($this->any())
                ->method('getItemTitleFromXML')
                ->will($this->returnValue('itemTitle'));
        
        $this->testYouTubeAPI = new YouTubeAPI(new \SkNd\MediaBundle\MediaAPI\TestYouTubeRequest());
        
        $this->mediaAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\MediaAPI')
                ->setConstructorArgs(array(
                        'true', 
                        self::$em, 
                        self::$session,
                        array(
                            'amazonapi'     =>  $this->testAmazonAPI,
                            'youtubeapi'    =>  $this->testYouTubeAPI,
                        )))
                ->setMethods(array(
                    'flush',
                ));
        
        $this->mediaSelection = $this->mediaAPI->getMock()->getMediaSelection(array(
            'api'   => 'amazonapi',
            'media' => 'film'
        ));
        
        $this->mediaResource = new MediaResource();
        $this->mediaResource->setId('testMediaResource');
        $this->mediaResource->setAPI($this->mediaSelection->getAPI());
        $this->mediaResource->setMediaType($this->mediaSelection->getMediaType());
    }
    
    public function tearDown(){
        unset($this->cachedXMLResponse);
        unset($this->liveXMLResponse);
        unset($this->testAmazonAPI);
        unset($this->testYouTubeAPI);
        unset($this->mediaAPI);
        unset($this->mediaSelection);
        unset($this->mediaResource);
        
    }
    
    /**
     * @expectedException RuntimeException
     * @exceptedExceptionMessage api key not found 
     */
    public function testSetAPIStrategyOnNonExistentAPIThrowsException(){
        
        $response = $this->mediaAPI->getMock()->setAPIStrategy('bogusAPIKey');
    }
        
    public function testGetDBNonExistentMediaResourceReturnsNewMediaResource(){
        $this->mediaAPI = $this->mediaAPI->getMock();
        $mr = $this->mediaAPI->getMediaResource('nonexistentID');
        $this->assertEquals($mr->getId(), 'nonexistentID', 'new media resource was returned');
    }
    
    public function testGetMediaResourceInDBWithVagueDetailsUpdatesMediaResource(){
        $this->mediaAPI = $this->mediaAPI->getMock();
        
        $this->mediaSelection = $this->mediaAPI->getMediaSelection(array(
            'media' => 'film',
            'decade'=> '1980s',
            'genre' => 'drama',
        ));
        
        //create and insert a dummy mediaresource
        $mr = new MediaResource();
        $mr->setId('mediaAPITestMR1');
        $mr->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->findOneBy(array('id' => 1)));
        $mr->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->findOneBy(array('id' => 1)));
        //$mr->setMediaResourceCache($this->getCache($id));
        self::$em->persist($mr);
        self::$em->flush();
        
        $updatedMr = $this->mediaAPI->getMediaResource('mediaAPITestMR1');
        $this->assertEquals($updatedMr->getDecade()->getDecadeName(), '1980', "Decade was updated to 1980");
        
        self::$em->remove($mr);
        self::$em->flush();
        
    }
    
    public function testGetMediaResourceWithExpiredCacheDeletesCache(){
        $this->mediaAPI = $this->mediaAPI->getMock();
       
        $cachedResource = new MediaResourceCache();
        $cachedResource->setXmlData($this->cachedXMLResponse->asXML());
        $cachedResource->setId('mediaAPITestMR1');
        $cachedResource->setTitle('mediaAPITestMR1');
        $cachedResource->setDateCreated(new \DateTime("1st Jan 1980"));
        
        //create and insert a dummy mediaresource
        $mr = new MediaResource();
        $mr->setId('mediaAPITestMR1');
        $mr->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->findOneBy(array('id' => 1)));
        $mr->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->findOneBy(array('id' => 1)));
        $mr->setMediaResourceCache($cachedResource);
        self::$em->persist($mr);
        self::$em->flush();
        
        $updatedMr = $this->mediaAPI->getMediaResource('mediaAPITestMR1');
        $this->assertTrue($updatedMr->getMediaResourceCache() == null, "cache was deleted");
        
        self::$em->remove($mr);
        self::$em->remove($cachedResource);
        self::$em->flush();
        
    }
    
    public function testNonexistentMediaResourceCallsLiveAPI(){
        $this->mediaAPI = 
                $this->mediaAPI->setMethods(array(
                    'getMediaResource',
                    'flush',
                    ))
                ->getMock();
        
        $this->mediaAPI->expects($this->any())
                ->method('getMediaResource')
                ->will($this->returnValue(null));
        $this->mediaAPI->expects($this->any())
                ->method('flush')
                ->will($this->returnValue(true));
        

        $mr = $this->mediaAPI->getDetails(
                array('ItemId'  =>  '1'));
        
        $this->assertEquals((string)$mr->getMediaResourceCache()->getXmlData()->item->attributes()->id, 'liveData');
    }
    
    public function testExistingMediaResourceAndNonexistentCachedResourceCallsLiveAPI(){
        $this->mediaAPI = 
                $this->mediaAPI->setMethods(array(
                    'getMediaResource',
                    'flush',
                    ))
                ->getMock();
        
        $this->mediaAPI->expects($this->once())
                ->method('getMediaResource')
                ->will($this->returnValue($this->mediaResource));
        $this->mediaAPI->expects($this->any())
                ->method('flush')
                ->will($this->returnValue(true));

        $mr = $this->mediaAPI->getDetails(
                array('ItemId'  =>  '1'));
        $this->assertEquals((string)$mr->getMediaResourceCache()->getXmlData()->item->attributes()->id, 'liveData');
    }
    
    public function testExistingMediaResourceAndExistingValidCachedResourceReturnsCache(){
         $this->mediaAPI = 
                $this->mediaAPI->setMethods(array(
                    'getMediaResource',
                    'flush',
                    ))
                ->getMock();
         
        $cachedResource = new MediaResourceCache();
        $cachedResource->setXmlData($this->cachedXMLResponse->asXML());
        $cachedResource->setId($this->mediaResource->getId());
        $cachedResource->setDateCreated(new \DateTime("now"));
        $this->mediaResource->setMediaResourceCache($cachedResource);

        //tell the mocked object which methods to mock and what to return
        $this->mediaAPI->expects($this->any())
                ->method('getMediaResource')
                ->will($this->returnValue($this->mediaResource));
        
        $mr = $this->mediaAPI->getDetails(
                array('ItemId'  =>  '1'));
        $this->assertEquals((string)$mr->getMediaResourceCache()->getXmlData()->item->attributes()->id, 'cachedData');
        
    }
    
    public function testProcessMediaResourcesWith1CachedAmazonResourceReturnsFalse(){
        //add a media resource and cached record first
        //then update the media resource
        $cachedResource = new MediaResourceCache();
        $cachedResource->setXmlData($this->cachedXMLResponse->asXML());
        $cachedResource->setId($this->mediaResource->getId());
        $cachedResource->setDateCreated(new \DateTime("now"));
        $this->mediaResource->setMediaResourceCache($cachedResource);
        
        $mediaResources = array(
            $cachedResource->getId() => $this->mediaResource,
        );
                   
        $updatesMade = $this->mediaAPI->getMock()->processMediaResources($mediaResources);
        
        $this->assertEquals($updatesMade, false);
        
    }
    
    public function testProcessMediaResourcesWith1CachedOutOfDateAmazonResourceCallsLiveAPIReturnsTrue(){
        
         $this->mediaAPI = 
                $this->mediaAPI->setMethods(array(
                    'cacheMediaResourceBatch',
                    'flush',
                    ))
                ->getMock();

        $this->mediaAPI->expects($this->any())
                ->method('cacheMediaResourceBatch')
                ->will($this->returnValue(true));
        $this->mediaAPI->expects($this->any())
                ->method('flush')
                ->will($this->returnValue(true));
        
        $cachedResource = new MediaResourceCache();
        $cachedResource->setXmlData($this->cachedXMLResponse->asXML());
        $cachedResource->setId($this->mediaResource->getId());
        $cachedResource->setDateCreated(new \DateTime("1st jan 1980"));
        $this->mediaResource->setMediaResourceCache($cachedResource);
        
        $mediaResources = array(
            $cachedResource->getId() => $this->mediaResource,
        );
            
        $updatesMade = $this->mediaAPI->processMediaResources($mediaResources);
        
        $this->assertEquals($updatesMade, true);
    } 
    
    
    
    
}

?>
