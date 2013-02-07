<?php

/*
 * Original code Copyright (c) 2013 Simon Kerr
 * ProcessListingsStrategy to test retrieval and caching of listings from apis
 * @author Simon Kerr
 * @version 1.0
 * Run UserBundle Fixtures first
 * php app/console doctrine:fixtures:load --fixtures=src/SkNd/UserBundle/DataFixtures/ORM --append
 */

namespace SkNd\MediaBundle\Tests\MediaAPI;
use SkNd\MediaBundle\MediaAPI\MediaAPI;
use SkNd\MediaBundle\MediaAPI\AmazonAPI;
use SkNd\MediaBundle\MediaAPI\YouTubeAPI;
use SkNd\MediaBundle\MediaAPI\ProcessListingsStrategy;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\UserBundle\Entity\MemoryWall;
use SkNd\MediaBundle\Entity\MediaResourceListingsCache;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session;

class ProcessListingsStrategyTest extends WebTestCase {
    private $mediaAPI;
    private $mediaSelection;
    private $testAmazonAPI;
    private $testYouTubeAPI;
    private $cachedXMLResponse;
    private $liveXMLResponse;
    private $constructorParams;
    private $processListingsStrategy;
    
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
        
        $q = self::$em->createQuery('DELETE from SkNd\MediaBundle\Entity\MediaResourceListingsCache');
        $q->execute();
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
            'media' => 'film',
        ));
        
        $this->constructorParams = array(
            'em'                =>      self::$em,
            'mediaSelection'    =>      $this->mediaSelection,
            'apiStrategy'       =>      $this->testAmazonAPI,
        );
        
        $this->processListingsStrategy = $this->getMockBuilder('\\SkNd\MediaBundle\\MediaAPI\\ProcessListingsStrategy')
        ->setConstructorArgs(array($this->constructorParams))
        ->setMethods(array(
            'persistMergeFlush'
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
    public function testConstructWithoutEntityManagerThrowsException(){
        unset($this->constructorParams['em']);
        $pls = new ProcessListingsStrategy($this->constructorParams);
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testConstructWithoutMediaSelectionThrowsException(){
        unset($this->constructorParams['mediaSelection']);
        $pls = new ProcessListingsStrategy($this->constructorParams);
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testConstructWithoutAPIStrategyThrowsException(){
        unset($this->constructorParams['apiStrategy']);
        $pls = new ProcessListingsStrategy($this->constructorParams);
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testGetMediaWhenNotSetThrowsException(){
        $pls = new ProcessListingsStrategy($this->constructorParams);
        $pls->getMedia();
    }
    
    
    public function testNonExistentCachedListingsCallsLiveAPI(){
        $pls = $this->processListingsStrategy->getMock();
        
        $pls->processMedia();
        $listings = $pls->getMedia();
        $this->assertEquals((string)$listings['listings']->getXmlData()->item->attributes()->id, 'liveData');
    }
    
    public function testExistingCachedListingsReturnsCache(){
        $this->mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'api'     => 'amazonapi',
            'media'   => 'film',
            'keywords'=> 'testExistingCachedListingsReturnsCache',
        ));
        
        $this->constructorParams = array(
            'em'                =>      self::$em,
            'mediaSelection'    =>      $this->mediaSelection,
            'apiStrategy'       =>      $this->testAmazonAPI,
        );
        
        $pls = $this->processListingsStrategy
                ->setConstructorArgs(array($this->constructorParams))
                ->getMock();
        
        $listings = new MediaResourceListingsCache();
        $listings->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->getAPIByName('amazonapi'));
        $listings->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->getMediaTypeBySlug('film'));
        $listings->setKeywords('testExistingCachedListingsReturnsCache');
        $listings->setXmlData($this->cachedXMLResponse->asXML());
        $listings->setLastModified(new \DateTime("now"));
        
        self::$em->persist($listings);
        self::$em->flush();
        
        $pls = $this->processListingsStrategy->getMock();
        
        $pls->processMedia();
        $result = $pls->getMedia();
        $this->assertEquals((string)$result['listings']->getXmlData()->item->attributes()->id, 'cachedData');
        
        self::$em->remove($listings);
        self::$em->flush();
    }
    
    public function testExistingOutOfDateCachedListingsUpdatesCacheFromLiveAPI(){
        //insert some listings
        $listings = new MediaResourceListingsCache();
        $listings->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->getAPIByName('amazonapi'));
        $listings->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->getMediaTypeBySlug('film'));
        $listings->setXmlData($this->cachedXMLResponse->asXML());
        $listings->setLastModified(new \DateTime("1st Jan 1980"));
        
        self::$em->persist($listings);
        self::$em->flush();
        
        $pls = $this->processListingsStrategy->getMock();
        
        $pls->processMedia();
        $result = $pls->getMedia();
        $this->assertEquals((string)$result['listings']->getXmlData()->item->attributes()->id, 'liveData');
        
        self::$em->remove($listings);
        self::$em->flush();
    }
    
    public function testGetRecommendationsReturnsNullIfNoDecadeSet(){
        $pls = $this->processListingsStrategy->getMock();
        
        $pls->processMedia();
        $result = $pls->getMedia();
        $this->assertNull($result['recommendations']);
        
    }
    
    public function testGetRecommendationsReturnsNullIfNothingFound(){
        $this->mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'api'   => 'amazonapi',
            'media' => 'film',
            'decade'=> '1980s',
        ));
        
        $this->constructorParams = array(
            'em'                =>      self::$em,
            'mediaSelection'    =>      $this->mediaSelection,
            'apiStrategy'       =>      $this->testAmazonAPI,
        );
        
        $pls = $this->processListingsStrategy
                ->setConstructorArgs(array($this->constructorParams))
                ->getMock();
        
        $pls->processMedia();
        $result = $pls->getMedia();
        $this->assertNull($result['recommendations']);
    }
    
    public function testGetRecommendationsReturnsMemoryWallsIfFound(){
        $mw = new MemoryWall();
        $mw->setAssociatedDecade(self::$em->getRepository('SkNdMediaBundle:Decade')->getDecadeBySlug('1980s'));
        self::$em->persist($mw);
        self::$em->flush();
        
        $this->mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'api'   => 'amazonapi',
            'media' => 'film',
            'decade'=> '1980s',
        ));
        
        $this->constructorParams = array(
            'em'                =>      self::$em,
            'mediaSelection'    =>      $this->mediaSelection,
            'apiStrategy'       =>      $this->testAmazonAPI,
        );
        
        $pls = $this->processListingsStrategy
                ->setConstructorArgs(array($this->constructorParams))
                ->getMock();
        
        $pls->processMedia();
        $result = $pls->getMedia();
        $this->assertEquals($result['recommendations'][0]->getAssociatedDecade()->getSlug(), '1980s');
        
        self::$em->remove($mw);
        self::$em->flush();
                
    }
    
    
    
    
}

?>
