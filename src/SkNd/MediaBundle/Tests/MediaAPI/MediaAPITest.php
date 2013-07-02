<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * Base class for all media api's tests
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

class MediaAPITests extends WebTestCase {
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
                        ),
                        'bundles/SkNd/cache/test',))
                ->setMethods(array(
                    'getMedia',
                ));
        
        $this->mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
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
    
    
    /* how to test these? they all return a media selection object */
    /*public function testGetMediaSelectionWhenNotNullReturnsMediaSelection(){
        $this->assertTrue($this->mediaSelection->getMediaSelection);
    }
    
    public function testGetMediaSelectionWhenMediaSelectionIsNullButInSessionReturnsMediaSelection(){
        
    }
    
    public function testGetMediaSelectionWhenNonExistentReturnsNewMediaSelection(){
        
    }*/
    
    /**
     * @expectedException RuntimeException
     * @exceptedExceptionMessage api key not found 
     */
    public function testSetAPIStrategyOnNonExistentAPIThrowsException(){
        
        $response = $this->mediaAPI->getMock()->setAPIStrategy('bogusAPIKey');
    }
    
    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @exceptedExceptionMessage There was a problem with that address 
     */
    public function testSetMediaSelectionWithInvalidParametersThrowsException(){
        $response = $this->mediaAPI->getMock()->setMediaSelection(array(
            'media' => 'invalid-media',
        ));
        
    }
    
    public function testSetMediaSelectionWithSpecificDecadeOverridesSessionParameters(){
        $mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'media' => 'film',
            'decade'=> '1980s'
        ));
        
        $this->assertTrue($mediaSelection->getDecade()->getDecadeName() == '1980', "decade was updated");
        
    }
    
    public function testSetMediaSelectionWithDefaultDecadeOverridesSpecificSessionParameters(){
        $mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'media' => 'film',
            'decade'=> '1980s'
        ));
        
        $mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'media' => 'film',
        ));
        
        $this->assertTrue($mediaSelection->getDecade() == null, "decade was updated to default");
    }
    
    public function testSetMediaSelectionWithDefaultGenreOverridesSpecificGenreSessionParameters(){
        $mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'media' => 'film',
            'genre'=> 'drama'
        ));
        
        $mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'media' => 'film',
        ));
        
        $this->assertTrue($mediaSelection->getSelectedMediaGenre() == null, "genre was updated to default");
    }
    
    public function testSetMediaSelectionWithKeywordsUpdatesMediaSelection(){
        $mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'media' => 'film',
            'keywords'=> 'some keywords'
        ));
       
        $this->assertTrue($mediaSelection->getKeywords() == 'some keywords', "keywords were added");
    }
    
    public function testSetMediaSelectionWithNoKeywordsOverridesSpecificKeywordsSessionParameter(){
        $mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'media' => 'film',
            'keywords'=> 'some keywords'
        ));
        
        $mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'media' => 'film',
        ));
        
        $this->assertTrue($mediaSelection->getKeywords() == null, "keywords were removed");
    }
    
    public function testGetMediaSelectionParamsReturnsArray(){
        $this->mediaAPI = $this->mediaAPI->getMock();
        $mediaSelection = $this->mediaAPI->setMediaSelection(array(
            'media' => 'film',
        ));
        
        $response = $this->mediaAPI->getMediaSelectionParams();
        $this->assertTrue($response['media'] == 'film', "film not returned as media type");
    }
    
    public function testGetMediaSelectionParamsForNonExistentMediaSelectionReturnsDefaultsArray(){
        self::$session->remove('mediaSelection');
        
        $response = $this->mediaAPI->getMock()->getMediaSelectionParams();
        $this->assertTrue($response['media'] == 'film-and-tv', "default film and tv was returned as media type");
    }
  
}

?>
