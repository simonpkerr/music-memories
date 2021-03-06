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
use SkNd\MediaBundle\MediaAPI\YouTubeAPI;
use SkNd\MediaBundle\Entity\MediaResource;
use SkNd\MediaBundle\Entity\MediaResourceCache;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProcessDetailsStrategyTest extends WebTestCase {
    private $processDetailsStrategy;
    private $mediaAPI;
    private $mediaSelection;
    private $testAmazonAPI;
    private $testYouTubeAPI;
    private $cachedXMLResponse;
    private $liveXMLResponse;
    private $mediaResource;
    private $constructorParams;
    private $xmlFileManager;
    
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
                        'bundles/SkNd/cache/test/'))
                ->setMethods(array(
                    'flush',
                ));
        
        $this->mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'api'   => 'amazonapi',
            'media' => 'film'
        ));
        
        $this->xmlFileManager = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\XMLFileManager')
                ->setConstructorArgs(array(
                    'bundles/SkNd/cache/test/',
                ))
                ->setMethods(array(
                    'createXmlRef',
                    'deleteXmlData',
                    'getXmlData',                    
                ))
                ->getMock();
 
        $this->constructorParams = array(
            'em'                =>      self::$em,
            'mediaSelection'    =>      $this->mediaSelection,
            'apiStrategy'       =>      $this->testAmazonAPI,
            'itemId'            =>      'testItemId',
            'referrer'          =>      'search',
            'title'             =>      'willy-wonka-chocolate-factory',
            'xmlFileManager'    =>      $this->xmlFileManager,
        );
        
        $this->processDetailsStrategy = $this->getMockBuilder('\\SkNd\MediaBundle\\MediaAPI\\ProcessDetailsStrategy')
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
        unset($this->processDetailsStrategy);
        
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testConstructWithoutEntityManagerThrowsException(){
        unset($this->constructorParams['em']);
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(
                array(
                    $this->constructorParams
                ))->getMock();
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testConstructWithoutMediaSelectionThrowsException(){
        unset($this->constructorParams['mediaSelection']);
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(
                array(
                    $this->constructorParams
                ))->getMock();
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testConstructWithoutAPIStrategyThrowsException(){
        unset($this->constructorParams['apiStrategy']);
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(
                array(
                    $this->constructorParams
                ))->getMock();
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testConstructWithoutItemIdThrowsException(){
        unset($this->constructorParams['itemId']);
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(
                array(
                    $this->constructorParams
                ))->getMock();
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testConstructWithoutItemTitleThrowsException(){
        unset($this->constructorParams['title']);
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(
                array(
                    $this->constructorParams
                ))->getMock();
    }
    
    
    /**
     * @expectedException RuntimeException
     */
    public function testGetNullMediaResourceThrowsException(){
        $this->processDetailsStrategy = $this->processDetailsStrategy->getMock();
        $this->processDetailsStrategy->getMedia();
    }
    
    public function testGetMediaResourceWithNonExistentIdReturnsNewMediaResource(){
        $this->constructorParams['itemId'] = 'MediaResourceWithNonExistentId';
        
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(
                array($this->constructorParams))->getMock();
        
        $mr = $this->processDetailsStrategy->getMediaResource();
        $this->assertEquals($mr->getId(), 'MediaResourceWithNonExistentId', 'new media resource was returned');
        $this->assertEquals($mr->getViewCount(), 0, 'media resources has been viewed more than once');
    }
    
    public function testGetCachedMediaResourceWithVagueDetailsNotUpdatedIfNotReferredFromSearch(){
        //set up a new media resource with certain vague details (mediatype and decade)
        $mr = new MediaResource();
        $mr->setId('CachedMediaResourceWithVagueDetails');
        $mr->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->findOneBy(array('id' => 1)));
        $mr->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->findOneBy(array('id' => 1)));
        $mr->setDecade(self::$em->getRepository('SkNdMediaBundle:Decade')->findOneBy(array('id' => 1)));
        
        self::$em->persist($mr);
        self::$em->flush();
        
        $this->mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'api'   => 'amazonapi',
            'media' => 'film',
            'decade'=> '1960s',
            'genre' => 'comedy',
        ));
     
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(array(
            array_merge(
                $this->constructorParams,
                array(
                    'itemId'            =>      'CachedMediaResourceWithVagueDetails',
                    'referrer'          =>      'details',
                    'mediaSelection'    =>      $this->mediaSelection,
                )
            )))->getMock();
                
       $mr = $this->processDetailsStrategy->getMediaResource();
       $this->assertFalse($mr->getDecade()->getSlug() == '1960s', 'decade was changed to 1960s');
        
        self::$em->remove($mr);
        self::$em->flush();
    }

    public function testGetMediaResourceWithVagueDetailsDoesNotUpdateIfNoReferrer(){
        unset($this->constructorParams['referrer']);
        $this->mediaAPI = $this->mediaAPI->getMock();
        
        $this->mediaSelection = $this->mediaAPI->setMediaSelection(array(
            'media' => 'film',
            'decade'=> '1980s',
            'genre' => 'drama',
        ));
        
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(array(
            array_merge(
                $this->constructorParams,
                array(
                    'mediaSelection'    => $this->mediaSelection,
                    'itemId'            => 'VagueMediaResourceNotUpdatedIfNoReferrer',
                )
            )))->getMock();
        
        //create and insert a dummy mediaresource
        $mr = new MediaResource();
        $mr->setId('VagueMediaResourceNotUpdatedIfNoReferrer');
        $mr->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->findOneBy(array('id' => 1)));
        $mr->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->findOneBy(array('id' => 1)));
        
        self::$em->persist($mr);
        self::$em->flush();
        
        $updatedMr = $this->processDetailsStrategy->getMediaResource();
        $this->assertNull($updatedMr->getDecade(), "Decade was updated to 1980");
        
        self::$em->remove($mr);
        self::$em->flush();
        
    }
    
    public function testGetCachedMediaResourceWithVagueDetailsUpdatesMediaResourceIfSpecificMediaTypeSet(){
        $this->mediaAPI = $this->mediaAPI->getMock();
        
        $this->mediaSelection = $this->mediaAPI->setMediaSelection(array(
            'media' => 'film',
            'decade'=> '1980s',
            'genre' => 'drama',
        ));
        
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(array(
            array_merge(
                $this->constructorParams,
                array(
                    'mediaSelection'    => $this->mediaSelection,
                    'itemId'            => 'mediaAPITestMR1',
                    )
                )
            ))->getMock();
                
        //create and insert a dummy mediaresource
        $mr = new MediaResource();
        $mr->setId('mediaAPITestMR1');
        $mr->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->findOneBy(array('id' => 1)));
        $mr->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->findOneBy(array('id' => 1)));
        
        self::$em->persist($mr);
        self::$em->flush();
        
        $updatedMr = $this->processDetailsStrategy->getMediaResource();
        $this->assertEquals($updatedMr->getDecade()->getDecadeName(), '1980', "Decade was not updated to 1980");
        
        self::$em->remove($mr);
        self::$em->flush();
    }
    
    public function testGetVagueCachedMediaResourceUpdatesIfDecadeSet(){
        //set up a new media resource with certain vague details (mediatype and decade)
        $mr = new MediaResource();
        $mr->setId('VagueCachedMediaResourceUpdatesIfDecadeSet');
        $mr->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->findOneBy(array('id' => 1)));
        $mr->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->findOneBy(array('id' => 1)));
        self::$em->persist($mr);
        self::$em->flush();
        
        $this->mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'api'   => 'amazonapi',
            'media' => 'film',
            'decade'=> '1960s',
        ));
        
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(array(
            array_merge(
                $this->constructorParams,
                array(
                    'itemId'            =>      'VagueCachedMediaResourceUpdatesIfDecadeSet',
                    'mediaSelection'    =>      $this->mediaSelection,
                )
            )))->getMock();
                
       $processedMr = $this->processDetailsStrategy->getMediaResource();
       $this->assertEquals($processedMr->getDecade()->getSlug(), '1960s', 'decade was not changed to 1960s');
        
        self::$em->remove($mr);
        self::$em->flush();
    }
    
    public function testGetVagueCachedMediaResourceUpdatesIfGenreSet(){
        //set up a new media resource with certain vague details (mediatype and decade)
        $mr = new MediaResource();
        $mr->setId('MediaResourceUpdatesIfGenreSet');
        $mr->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->findOneBy(array('id' => 1)));
        $mr->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->findOneBy(array('id' => 1)));
        self::$em->persist($mr);
        self::$em->flush();
        
        $this->mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'api'   => 'amazonapi',
            'media' => 'film',
            'genre' => 'comedy',
        ));
        
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(array(
            array_merge(
                $this->constructorParams,
                array(
                    'itemId'            =>      'MediaResourceUpdatesIfGenreSet',
                    'mediaSelection'    =>      $this->mediaSelection,
                )
            )))->getMock();
                
       $processedMr = $this->processDetailsStrategy->getMediaResource();
       $this->assertEquals($processedMr->getGenre()->getSlug(), 'comedy', 'genre was not changed to comedy');
        
        self::$em->remove($mr);
        self::$em->flush();
    }
    
    public function testGetMediaResourceUpdates1990sDecadeFromTitle(){
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(array(
            array_merge(
                $this->constructorParams,
                array(
                    'itemId'            =>      'mediaResourceFrom1990s',
                    'mediaSelection'    =>      $this->mediaSelection,
                    'title'             =>      'willy-wonka-chocolate-factory-1995-dvd',
                )
            )))->getMock();
                
       $processedMr = $this->processDetailsStrategy->getMediaResource();
       $this->assertEquals($processedMr->getDecade()->getSlug(), '1990s', 'decade was not updated to 1990s');
    }
    
    public function testGetMediaResourceUpdates1990sDecadeFromTitleWhenTwoYearsPresentInTitle(){
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(array(
            array_merge(
                $this->constructorParams,
                array(
                    'itemId'            =>      'mediaResourceFrom1990s',
                    'mediaSelection'    =>      $this->mediaSelection,
                    'title'             =>      'willy-wonka-chocolate-factory-1995-1996',
                )
            )))->getMock();
                
       $processedMr = $this->processDetailsStrategy->getMediaResource();
       $this->assertEquals($processedMr->getDecade()->getSlug(), '1990s', 'decade was not updated to 1990s');

    }
    
    public function testGetMediaResourceUpdatesDecadeTo2000sFromTitle(){
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(array(
            array_merge(
                $this->constructorParams,
                array(
                    'itemId'            =>      'mediaResourceFrom2000s',
                    'mediaSelection'    =>      $this->mediaSelection,
                    'title'             =>      'some-programme-in-2005',
                )
            )))->getMock();
                
       $processedMr = $this->processDetailsStrategy->getMediaResource();
       $this->assertEquals($processedMr->getDecade()->getSlug(), '2000s', 'decade was not updated to 2000s');

    }
    
    public function testGetMediaResourceWithInvalidDecadeInTitleDoesNotUpdateDecade(){
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(array(
            array_merge(
                $this->constructorParams,
                array(
                    'itemId'            =>      'mediaResourceFrom1900s',
                    'mediaSelection'    =>      $this->mediaSelection,
                    'title'             =>      'some-programme-in-1909',
                )
            )))->getMock();
                
       $processedMr = $this->processDetailsStrategy->getMediaResource();
       $this->assertNull($processedMr->getDecade(), 'decade was updated to 1900s');
        
    }
    
    public function testGetMediaResourceWithNoDecadeInTitleDoesNotUpdateDecade(){
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(array(
            array_merge(
                $this->constructorParams,
                array(
                    'itemId'            =>      'mediaResourceWithNoDecadeInTitle',
                    'mediaSelection'    =>      $this->mediaSelection,
                    'title'             =>      'some-programme-with-no-decade',
                )
            )))->getMock();
                
       $processedMr = $this->processDetailsStrategy->getMediaResource();
       $this->assertNull($processedMr->getDecade(), 'decade was updated');
        
    }
    
    public function testGetMediaResourceWithExpiredCacheDeletesCache(){
        $this->xmlFileManager->expects($this->any())
                ->method('xmlRefExists')
                ->will($this->returnValue(true));
        
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(array(
            array_merge(
                    $this->constructorParams,
                    array(
                        'mediaSelection'    => $this->mediaSelection,
                        'itemId'            => 'mediaAPITestMR2',
                        'xmlFileManager'    => $this->xmlFileManager,
                    )
            )))->getMock();
               
        $cachedResource = new MediaResourceCache();
        $cachedResource->setXmlRef('expiredCacheXmlRef');
        $cachedResource->setId('mediaAPITestMR2');
        $cachedResource->setTitle('mediaAPITestMR2');
        $cachedResource->setDateCreated(new \DateTime("1st Jan 1980"));
        
        //create and insert a dummy mediaresource
        $mr = new MediaResource();
        $mr->setId('mediaAPITestMR2');
        $mr->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->findOneBy(array('id' => 1)));
        $mr->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->findOneBy(array('id' => 1)));
        $mr->setMediaResourceCache($cachedResource);
        self::$em->persist($mr);
        self::$em->flush();
        
        $updatedMr = $this->processDetailsStrategy->getMediaResource();
        $this->assertTrue($updatedMr->getMediaResourceCache() == null, "cache was deleted");
        
        self::$em->remove($mr);
        self::$em->remove($cachedResource);
        self::$em->flush();
        
    }
    
    public function testNonExistentMediaResourceCallsLiveAPI(){
        $this->xmlFileManager->expects($this->any())
                ->method('xmlRefExists')
                ->will($this->returnValue(false));
        $this->xmlFileManager->expects($this->any())
                ->method('getXmlData')
                ->will($this->returnValue($this->liveXMLResponse));
        $this->xmlFileManager->expects($this->any())
                ->method('createXmlRef')
                ->will($this->returnValue('liveDataXmlRef'));
        
        
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(array(
            array_merge(
                    $this->constructorParams,
                    array(
                        'xmlFileManager'    => $this->xmlFileManager,
                    )
            )))->getMock();
        
        $this->processDetailsStrategy->expects($this->any())
                ->method('persistMergeFlush')
                ->will($this->returnValue(true));

        $this->processDetailsStrategy->processMedia();
        $this->processDetailsStrategy->cacheMedia();
        $mr = $this->processDetailsStrategy->getMedia();
        
        $this->assertEquals($mr->getMediaResourceCache()->getXmlRef(), 'liveDataXmlRef', 'xml reference was wrong');
        $this->assertEquals((string)$mr->getMediaResourceCache()->getXmlData()->item->attributes()->id, 'liveData', 'live data was not returned');
    }
    
    public function testExistingMediaResourceAndNonexistentCachedResourceCallsLiveAPI(){
        $this->mediaResource = new MediaResource();
        $this->mediaResource->setId('existingMediaResourceWithNoCache');
        $this->mediaResource->setAPI($this->mediaSelection->getAPI());
        $this->mediaResource->setMediaType($this->mediaSelection->getMediaType());
        
        $this->xmlFileManager->expects($this->any())
                ->method('xmlRefExists')
                ->will($this->returnValue(true));
        $this->xmlFileManager->expects($this->any())
                ->method('getXmlData')
                ->will($this->returnValue($this->liveXMLResponse));
        $this->xmlFileManager->expects($this->any())
                ->method('createXmlRef')
                ->will($this->returnValue('liveDataXmlRef'));
                
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(array(
            array_merge(
                $this->constructorParams,
                array(
                    'xmlFileManager'    => $this->xmlFileManager,
                )
            )))
            ->setMethods(array(
                'getMediaResource',
                'persistMergeFlush',
                ))
            ->getMock();
        
        $this->processDetailsStrategy->expects($this->once())
                ->method('getMediaResource')
                ->will($this->returnValue($this->mediaResource));
        $this->processDetailsStrategy->expects($this->any())
                ->method('persistMergeFlush')
                ->will($this->returnValue(true));

        $this->processDetailsStrategy->processMedia();
        $this->processDetailsStrategy->cacheMedia();
        $mr = $this->processDetailsStrategy->getMedia();
        
        $this->assertEquals($mr->getMediaResourceCache()->getXmlRef(), 'liveDataXmlRef', 'xml reference was wrong');
        $this->assertEquals((string)$mr->getMediaResourceCache()->getXmlData()->item->attributes()->id, 'liveData');
    }
    
    public function testExistingMediaResourceAndExistingValidCachedResourceReturnsCache(){
        $this->mediaResource = new MediaResource();
        $this->mediaResource->setId('ExistingMediaResourceAndExistingValidCachedResource');
        $this->mediaResource->setAPI($this->mediaSelection->getAPI());
        $this->mediaResource->setMediaType($this->mediaSelection->getMediaType());
        
        $cachedResource = new MediaResourceCache();
        $cachedResource->setXmlRef('cachedMediaResourceXmlRef');
        $cachedResource->setXmlData($this->cachedXMLResponse);
        $cachedResource->setId($this->mediaResource->getId());
        $cachedResource->setDateCreated(new \DateTime("now"));
        $cachedResource->setTitle('ExistingMediaResourceAndExistingValidCachedResource');
        $this->mediaResource->setMediaResourceCache($cachedResource);
        
        $this->xmlFileManager->expects($this->any())
                ->method('xmlRefExists')
                ->will($this->returnValue(true));
        $this->xmlFileManager->expects($this->any())
                ->method('getXmlData')
                ->will($this->returnValue($this->cachedXMLResponse));
        $this->xmlFileManager->expects($this->any())
                ->method('createXmlRef')
                ->will($this->returnValue('cachedMediaResourceXmlRef'));
                
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(array(
            array_merge(
                $this->constructorParams,
                array(
                    'xmlFileManager'    => $this->xmlFileManager,
                )
            )))
            ->setMethods(array(
                'getMediaResource',
                'persistMergeFlush',
                ))
            ->getMock(); 

        //tell the mocked object which methods to mock and what to return
        $this->processDetailsStrategy->expects($this->any())
                ->method('getMediaResource')
                ->will($this->returnValue($this->mediaResource));
        $this->processDetailsStrategy->expects($this->any())
                ->method('persistMergeFlush')
                ->will($this->returnValue(true));        
        
        $this->processDetailsStrategy->processMedia();
        $this->processDetailsStrategy->cacheMedia();
        $mr = $this->processDetailsStrategy->getMedia();
        
        $this->assertEquals($mr->getMediaResourceCache()->getXmlRef(), 'cachedMediaResourceXmlRef', 'xml reference was wrong');
        $this->assertEquals((string)$mr->getMediaResourceCache()->getXmlData()->item->attributes()->id, 'cachedData');
        
    }
    
   
    public function testCacheMediaResourceWithNullDecadeAndXmlContainingDecadeUpdatesMediaResource(){
        //when caching a media resource, if a decade is not set, the api strategy will try to find a title based on 
        //which api it is and use that to categorise the media resource.
        /*$this->testAmazonAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\AmazonAPI')
                ->disableOriginalConstructor()
                ->setMethods(array(
                    'getListings',
                    'getDetails',
                    'getImageUrlFromXML',
                    'getItemTitleFromXML',
                    'getDecadeFromXML'
                ))
                ->getMock();
        
        $this->testAmazonAPI->expects($this->any())
                ->method('getDecadeFromXML')
                ->will($this->returnValue('1980s'));
       
        $this->mediaResource = new MediaResource();
        $this->mediaResource->setId('testMediaResourceWithNoCache');
        $this->mediaResource->setAPI($this->mediaSelection->getAPI());
        $this->mediaResource->setMediaType($this->mediaSelection->getMediaType());
        
        $this->xmlFileManager->expects($this->any())
                ->method('xmlRefExists')
                ->will($this->returnValue(true));
        $this->xmlFileManager->expects($this->any())
                ->method('getXmlData')
                ->will($this->returnValue($this->liveXMLResponse));
        $this->xmlFileManager->expects($this->any())
                ->method('createXmlRef')
                ->will($this->returnValue('liveXmlRef'));
                
        $this->processDetailsStrategy = $this->processDetailsStrategy->setConstructorArgs(array(
            array_merge(
                    array(
                        'em'                =>      self::$em,
                        'mediaSelection'    =>      $this->mediaSelection,
                        'apiStrategy'       =>      $this->testAmazonAPI,
                        'itemId'            =>      'testMediaResourceWithNoCache',
                        'xmlFileManager'    =>      $this->xmlFileManager,
                    ), 
                    $this->constructorParams)
            ))
                ->setMethods(array(
                    'getMediaResource',
                    'persistMergeFlush',
                    ))
                ->getMock(); 
        
        $this->processDetailsStrategy->expects($this->once())
                ->method('getMediaResource')
                ->will($this->returnValue($this->mediaResource));
        $this->processDetailsStrategy->expects($this->any())
                ->method('persistMergeFlush')
                ->will($this->returnValue(true));

        $this->processDetailsStrategy->processMedia();
        $this->processDetailsStrategy->cacheMedia();
        $mr = $this->processDetailsStrategy->getMedia();
        
        $this->assertEquals((string)$mr->getDecade()->getSlug(), '1980s');*/
    }
    
    
    //ADD TESTS FOR YOUTUBE AND OTHER API PROVIDERS
    
    
}

?>
