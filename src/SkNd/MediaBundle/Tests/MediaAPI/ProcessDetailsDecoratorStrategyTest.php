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
use SkNd\MediaBundle\MediaAPI\ProcessDetailsStrategy;
use SkNd\MediaBundle\MediaAPI\ProcessDetailsDecoratorStrategy;
use SkNd\MediaBundle\MediaAPI\AmazonAPI;
use SkNd\MediaBundle\MediaAPI\YouTubeAPI;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Entity\MediaResource;
use SkNd\MediaBundle\Entity\MediaResourceCache;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session;

class ProcessDetailsDecoratorStrategyTest extends WebTestCase {
    private $processDetailsStrategy;
    private $processDetailsDecoratorStrategy;
    private $mediaAPI;
    private $mediaSelection;
    private $testAmazonAPI;
    private $testYouTubeAPI;
    private $cachedXMLResponse;
    private $liveXMLResponse;
    private $mediaResource;
    private $constructorParams;
    private $decoratorConstructorParams;
    
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
        
        $this->mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'api'   => 'amazonapi',
            'media' => 'film'
        ));
        
 
        $this->constructorParams = array(
            'em'                =>      self::$em,
            'mediaSelection'    =>      $this->mediaSelection,
            'apiStrategy'       =>      $this->testAmazonAPI,
            'itemId'            =>      'testItemId',
            'referrer'          =>      'search',
        );
        
        $this->processDetailsStrategy = $this->getMockBuilder('\\SkNd\MediaBundle\\MediaAPI\\ProcessDetailsStrategy')
                ->setConstructorArgs(array($this->constructorParams))
                ->setMethods(array(
                    'persistMerge'
                ));
        
        //redefined params for the decorator strategy
        $this->decoratorConstructorParams = array(
            'em'                        =>      self::$em,
            'processDetailsStrategy'    =>      $this->processDetailsStrategy->getMock(),            
            'apis'                      =>      array(
                'amazonapi' =>  $this->testAmazonAPI,
                'youtubeapi'=>  $this->testYouTubeAPI,
            )
        );
        
        $this->processDetailsDecoratorStrategy = $this->getMockBuilder('\\SkNd\MediaBundle\\MediaAPI\\ProcessDetailsDecoratorStrategy')
                ->setConstructorArgs(array($this->decoratorConstructorParams))
                ->setMethods(array(
                    'persistMerge'
                ));
        
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
        unset($this->constructorParams['processDetailsStrategy']);
        $this->processDetailsDecoratorStrategy = $this->processDetailsDecoratorStrategy->setConstructorArgs(
                array(
                    $this->constructorParams
                ))->getMock();
    }
     
    /**
     * @expectedException RuntimeException
     */
    public function testConstructWithoutEntityManagerThrowsException(){
        unset($this->constructorParams['em']);
        $this->processDetailsDecoratorStrategy = $this->processDetailsDecoratorStrategy->setConstructorArgs(
                array(
                    $this->constructorParams
                ))->getMock();
    }
    
    public function testProcessMediaResourcesWithNoRelatedMediaResourcesDoesntSaveRecommendations(){
        //set up a new media resource 
        $mr = new MediaResource();
        $mr->setId('MediaResourcesWithNoRelatedMediaResources');
        $mr->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->findOneBy(array('id' => 1)));
        $mr->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->findOneBy(array('id' => 1)));
        $mr->setDecade(self::$em->getRepository('SkNdMediaBundle:Decade')->findOneBy(array('id' => 1)));
        
        $emptyRecs = array(
            'genericMatches' => array(),
            'exactMatches'  => array(),
        );
        
        $this->processDetailsDecoratorStrategy = $this->processDetailsDecoratorStrategy->setConstructorArgs(
                array(
                    $this->constructorParams
                ))->getMock();
        
        $this->processDetailsDecoratorStrategy->expects($this->any())
                ->method('persistMerge')
                ->will($this->returnValue(true));
        $this->processDetailsDecoratorStrategy->expects($this->any())
                ->method('getMediaResource')
                ->will($this->returnValue($mr));
        $this->processDetailsDecoratorStrategy->expects($this->any())
                ->method('getRecommendations')
                ->will($this->returnValue($emptyRecs));
        
        $recs = $mr->getRelatedMediaResources();
        $this->assertTrue(is_null($recs), "recommendations were saved incorrectly");        
    }
    
    
        
    public function testProcessMediaResourceWithCachedRecommendationsReturnsCache(){
        //set up a new media resource 
        $mr = new MediaResource();
        $mr->setId('CachedMediaResource');
        $mr->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->findOneBy(array('id' => 1)));
        $mr->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->findOneBy(array('id' => 1)));
        $mr->setDecade(self::$em->getRepository('SkNdMediaBundle:Decade')->findOneBy(array('id' => 1)));
        
        $rec = clone $mr;
        $rec->setId('RecMediaResource');
        
        $cachedResource = new MediaResourceCache();
        $cachedResource->setXmlData($this->cachedXMLResponse->asXML());
        $cachedResource->setId('RecMediaResource');
        $cachedResource->setTitle('RecMediaResource');
        $cachedResource->setDateCreated(new \DateTime("now"));
        $rec->setMediaResourceCache($cachedResource);
        
        $recs = array(
            'genericMatches' => array(
                'RecMediaResource' => $rec,
            ),
        );
        
        $this->processDetailsDecoratorStrategy = $this->processDetailsDecoratorStrategy->setConstructorArgs(
                array(
                    $this->constructorParams
                ))->getMock();
        
        $this->processDetailsDecoratorStrategy->expects($this->any())
                ->method('persistMerge')
                ->will($this->returnValue(true));
        $this->processDetailsDecoratorStrategy->expects($this->any())
                ->method('getMediaResource')
                ->will($this->returnValue($mr));
        $this->processDetailsDecoratorStrategy->expects($this->any())
                ->method('getRecommendations')
                ->will($this->returnValue($recs));
        
        $recs = $mr->getRelatedMediaResources();
        $this->assertTrue(!is_null($recs), "recommendations weren't saved");
        $this->assertEquals((string)$recs[0]->getMediaResourceCache()->getXmlData()->item->attributes()->id, 'cachedData');
    }
    
    public function testProcessMediaWithNonCachedRecommendationsReturnsLiveRecords(){
        //set up a new media resource 
        $mr = new MediaResource();
        $mr->setId('CachedMediaResource');
        $mr->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->findOneBy(array('id' => 1)));
        $mr->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->findOneBy(array('id' => 1)));
        $mr->setDecade(self::$em->getRepository('SkNdMediaBundle:Decade')->findOneBy(array('id' => 1)));
        
        $rec = clone $mr;
        $rec->setId('RecMediaResource');
        
        $recs = array(
            'genericMatches' => array(
                'RecMediaResource' => $rec,
            ),
        );
        
        $this->processDetailsDecoratorStrategy = $this->processDetailsDecoratorStrategy->setConstructorArgs(
                array(
                    $this->constructorParams
                ))->getMock();
        
        $this->processDetailsDecoratorStrategy->expects($this->any())
                ->method('persistMerge')
                ->will($this->returnValue(true));
        $this->processDetailsDecoratorStrategy->expects($this->any())
                ->method('getMediaResource')
                ->will($this->returnValue($mr));
        $this->processDetailsDecoratorStrategy->expects($this->any())
                ->method('getRecommendations')
                ->will($this->returnValue($recs));
        
        $recs = $mr->getRelatedMediaResources();
        $this->assertTrue(!is_null($recs), "recommendations weren't saved");
        $this->assertEquals((string)$recs[0]->getMediaResourceCache()->getXmlData()->item->attributes()->id, 'liveData');
    }
          
    
   
    
    
}

?>
