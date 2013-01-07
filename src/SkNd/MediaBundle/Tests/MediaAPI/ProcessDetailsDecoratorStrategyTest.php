<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * ProcessDetailsDecoratorStrategy test to test extended functionality
 * of getting recommendations and processing the data in a different way
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

class ProcessDetailsDecoratorStrategyTest extends WebTestCase {
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
     */
    public function testConstructWithoutDetailsStrategyThrowsException(){
        
        //$response = $this->mediaAPI->getMock()->setAPIStrategy('bogusAPIKey');
    }
     
    /**
     * @expectedException RuntimeException
     */
    public function testConstructWithoutEntityManagerThrowsException(){
        
        //$response = $this->mediaAPI->getMock()->setAPIStrategy('bogusAPIKey');
    }
    
    public function processMediaWithCachedRecommendationsReturnsCache(){
        //todo
    }
    
    public function processMediaWithNonCachedRecommendationsReturnsLiveRecords(){
        //todo
    }
    
    //will be part of the process media method
    public function testGetRecommendationsWithNullIdentifierReturnsNull(){
        $response = $this->mediaAPI->getMock()->getRecommendations();
        $this->assertTrue($response == null, "returned recommendations are null");
    }
    
    public function testRecommendationsForDetailsPageAreAddedToMediaResourceWhenAvailable(){
        $this->mediaAPI = 
                $this->mediaAPI->setMethods(array(
                    'getRecommendations',
                    'getMediaResource',
                    'processMediaResources',
                    'flush',
                    ))
                ->getMock();
        
        $rec1 = new MediaResource();
        $rec1->setId('testRec1');
        $rec1->setAPI($this->mediaSelection->getAPI());
        $rec1->setMediaType($this->mediaSelection->getMediaType());
        
        $rec2 = new MediaResource();
        $rec2->setId('testRec2');
        $rec2->setAPI($this->mediaSelection->getAPI());
        $rec2->setMediaType($this->mediaSelection->getMediaType());
        
        $recommendations = array(
            'exactMatches'     => array(
                'testRec1'  => $rec1,
            ),
            'genericMatches'   => array(
                'testRec2'  => $rec2,
            )
        );
        
        $this->mediaAPI->expects($this->any())
                ->method('getRecommendations')
                ->will($this->returnValue($recommendations));
        $this->mediaAPI->expects($this->any())
                ->method('getMediaResource')
                ->will($this->returnValue($this->mediaResource));
        $this->mediaAPI->expects($this->any())
                ->method('processMediaResources')
                ->will($this->returnValue(true));
        
        $mr = $this->mediaAPI->getDetails(array('ItemId' => 'testMediaResource'), MediaAPI::MEDIA_RESOURCE_RECOMMENDATION);
        
        $relatedMrs = $mr->getRelatedMediaResources();
        $this->assertEquals($relatedMrs['exactMatches']['testRec1']->getId(), 'testRec1', "recommended media resources include testRec1");
    }
    
    public function testBatchOfMediaResourcesAreCachedCorrectly(){
        //todo
    }
   
    
    
    
    
}

?>