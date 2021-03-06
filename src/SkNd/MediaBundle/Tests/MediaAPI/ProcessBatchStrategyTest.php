<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * ProcessBatchStrategyTest test to test aspects of getting, processing and caching
 * batches of items from db and live apis
 * @author Simon Kerr
 * @version 1.0
 * Run UserBundle Fixtures first
 * php app/console doctrine:fixtures:load --fixtures=src/SkNd/UserBundle/DataFixtures/ORM --append
 */

namespace SkNd\MediaBundle\Tests\MediaAPI;
use SkNd\MediaBundle\MediaAPI\YouTubeAPI;
use SkNd\MediaBundle\MediaAPI\ProcessBatchStrategy;
use SkNd\MediaBundle\Entity\MediaResource;
use SkNd\MediaBundle\Entity\MediaResourceCache;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProcessBatchStrategyTest extends WebTestCase {
    private $mediaAPI;
    private $mediaSelection;
    private $testAmazonAPI;
    private $testYouTubeAPI;
    private $cachedXMLResponse;
    private $liveXMLResponse;
    private $mediaResource;
    private $constructorParams;
    private $pbs;
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
  
        $this->cachedXMLResponse = new \SimpleXMLElement(
        '<?xml version="1.0" ?>
        <items>
            <item id="cachedData">
                <ASIN>cachedData</ASIN>
            </item>
        </items>');
        $this->liveXMLResponse = new \SimpleXMLElement(
        '<?xml version="1.0" ?>
        <items>
            <item id="liveData">
                <ASIN>liveData</ASIN>
            </item>
        </items>');
        
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
                    'flush',
                ));
        
        $this->mediaSelection = $this->mediaAPI->getMock()->setMediaSelection(array(
            'api'   => 'amazonapi',
            'media' => 'film'
        ));
        
        $this->mediaResource = new MediaResource();
        $this->mediaResource->setId('testMediaResource');
        $this->mediaResource->setAPI($this->mediaSelection->getAPI());
        $this->mediaResource->setMediaType($this->mediaSelection->getMediaType());
        
        $this->xmlFileManager = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\XMLFileManager')
                ->setConstructorArgs(array(
                    'bundles/SkNd/cache/test/',
                ))
                ->setMethods(array(
                    'createXmlRef',
                    'deleteXmlData',
                    'getXmlData',
                    'xmlRefExists',
                ))
                ->getMock();
        
        $this->constructorParams = array(
                'em'                =>  self::$em,
                'apis'              =>  array(
                    'amazonapi'     =>  $this->testAmazonAPI,
                    'youtubeapi'    =>  $this->testYouTubeAPI,
                ),
                'xmlFileManager'    =>  $this->xmlFileManager,
        );
        
        $this->pbs = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\ProcessBatchStrategy')
                ->setConstructorArgs(array(
                    $this->constructorParams
                ))
                ->setMethods(array(
                    'persistMergeFlush',
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
        $pbs = new ProcessBatchStrategy($this->constructorParams);
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testConstructWithoutAPIsArrayThrowsException(){
        unset($this->constructorParams['apis']);
        $pbs = new ProcessBatchStrategy($this->constructorParams);
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testGetMediaBeforeBeingSetThrowsException(){
        $pbs = new ProcessBatchStrategy($this->constructorParams);
        $pbs->getMedia();
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testProcessMediaBeforeMediaResourcesSetThrowsException(){
        $pbs = new ProcessBatchStrategy($this->constructorParams);
        $pbs->processMedia();
    }
    
    public function testConstructWithoutMediaResourcesDoesntThrowException(){
        $this->constructorParams['mediaResources'] = array(
            'testMediaResource' => $this->mediaResource,
        );
        $this->pbs = $this->pbs
            ->setConstructorArgs(array(
                $this->constructorParams
            ))->setMethods(array(
                'persistMergeFlush'
            ))->getMock();

        $this->assertTrue(!is_null($this->pbs->getMedia()), 'media resources were null');
    }   
       
    public function testProcessMediaResourcesWith1CachedAmazonResourceReturnsCache(){
        //add a media resource and cached record first
        //then update the media resource
        $cachedResource = new MediaResourceCache();
        $cachedResource->setXmlRef('oneCachedAmazonResource');
        $cachedResource->setId($this->mediaResource->getId());
        $cachedResource->setDateCreated(new \DateTime("now"));
        $this->mediaResource->setMediaResourceCache($cachedResource);
        
        $this->xmlFileManager->expects($this->any())
            ->method('xmlRefExists')
            ->will($this->returnValue(true));
        $this->xmlFileManager->expects($this->any())
            ->method('getXmlData')
            ->will($this->returnValue($this->cachedXMLResponse));
        
        $this->pbs = $this->pbs
            ->setConstructorArgs(array(
                array_merge(
                    $this->constructorParams,
                    array(
                        'mediaResources' => array(
                            'testMediaResource' => $this->mediaResource,
                        ),
                        'xmlFileManager' => $this->xmlFileManager,
                    )
                )
            ))->setMethods(array(
                'persistMergeFlush'
            ))->getMock();
             
        $this->pbs->processMedia();
        $this->pbs->cacheMedia();
        $mrs = $this->pbs->getMedia();
         
        $mr = array_pop($mrs);
        $this->assertEquals((string)$mr->getMediaResourceCache()->getXmlData()->item->attributes()->id, 'cachedData', 'media resources was updated');
        $this->assertEquals((string)$mr->getMediaResourceCache()->getXmlRef(), 'oneCachedAmazonResource', 'media resource was not the cached version');
        
    }
    
    public function testProcessMediaResourcesWith1CachedOutOfDateAmazonResourceCallsLiveAPI(){
        $this->mediaResource->setId('liveData');
        $cachedResource = new MediaResourceCache();
        $cachedResource->setId($this->mediaResource->getId());
        $cachedResource->setDateCreated(new \DateTime("1st jan 1980"));
        $cachedResource->setXmlRef('oneOutOfDateCachedAmazonResource');
        $this->mediaResource->setMediaResourceCache($cachedResource);
        
        $this->xmlFileManager->expects($this->any())
            ->method('xmlRefExists')
            ->will($this->returnValue(true));
        $this->xmlFileManager->expects($this->any())
            ->method('createXmlRef')
            ->will($this->returnValue('liveDataXmlRef'));
        $this->xmlFileManager->expects($this->any())
            ->method('getXmlData')
            ->will($this->returnValue($this->liveXMLResponse));
                    
        $this->pbs = $this->pbs
            ->setConstructorArgs(array(
                array_merge(
                    $this->constructorParams,
                    array(
                        'mediaResources' => array(
                            'liveData' => $this->mediaResource,
                        ),
                        'xmlFileManager' => $this->xmlFileManager,
                    )
                )
            ))->setMethods(array(
                'persistMergeFlush'
            ))->getMock();
        
        $this->pbs->processMedia();
        $this->pbs->cacheMedia();
        $mrs = $this->pbs->getMedia();
                
        $mr = array_pop($mrs);
        $this->assertEquals((string)$mr->getMediaResourceCache()->getXmlData()->item->attributes()->id, 'liveData', 'media resources was not updated from live api');
        
    } 
    
    public function testProcessMediaResourcesWith1NonCachedAmazonResourceCallsLiveAPI(){
        
        $this->xmlFileManager->expects($this->any())
            ->method('xmlRefExists')
            ->will($this->returnValue(false));
        $this->xmlFileManager->expects($this->any())
            ->method('createXmlRef')
            ->will($this->returnValue('nonCachedMR'));
        $this->xmlFileManager->expects($this->any())
            ->method('getXmlData')
            ->will($this->returnValue($this->liveXMLResponse));
        
        $this->mediaResource->setId('nonCachedMR');
        
        $this->pbs = $this->pbs
            ->setConstructorArgs(array(
                array_merge(
                    $this->constructorParams,
                    array(
                        'mediaResources' => array(
                            'liveData' => $this->mediaResource,
                        ),
                        'xmlFileManager' => $this->xmlFileManager,
                    )
                )
            ))->setMethods(array(
                'persistMergeFlush'
            ))->getMock();
             
        $this->pbs->processMedia();
        $this->pbs->cacheMedia();
        $mrs = $this->pbs->getMedia();
                
        $mr = array_pop($mrs);
        $this->assertEquals((string)$mr->getMediaResourceCache()->getXmlData()->item->attributes()->id, 'liveData', 'media resources was not updated from live api');
        $this->assertEquals((string)$mr->getMediaResourceCache()->getXmlRef(), 'nonCachedMR', 'media resource was not updated from live api');
    }
    
    public function testProcessMediaResourcesWith1CachedAmazonResourceAndNoXmlFileCallsLiveAPI(){
        //add a media resource and cached record first
        //then update the media resource
        $cachedResource = new MediaResourceCache();
        $cachedResource->setXmlData($this->cachedXMLResponse->asXML());
        $cachedResource->setXmlRef('oneCachedAmazonResource');
        $cachedResource->setId($this->mediaResource->getId());
        $cachedResource->setDateCreated(new \DateTime("now"));
        $this->mediaResource->setId('liveData');
        $this->mediaResource->setMediaResourceCache($cachedResource);
        
        $this->xmlFileManager->expects($this->any())
            ->method('xmlRefExists')
            ->will($this->returnValue(false));
        $this->xmlFileManager->expects($this->any())
            ->method('createXmlRef')
            ->will($this->returnValue('cachedResourceWithNoXmlFile'));
        $this->xmlFileManager->expects($this->any())
            ->method('getXmlData')
            ->will($this->returnValue($this->liveXMLResponse));
        
        $this->pbs = $this->pbs
            ->setConstructorArgs(array(
                array_merge(
                    $this->constructorParams,
                    array(
                        'mediaResources' => array(
                            'liveData' => $this->mediaResource,
                        ),
                        'xmlFileManager' => $this->xmlFileManager,
                    )
                )
            ))->setMethods(array(
                'persistMergeFlush'
            ))->getMock();
             
        $this->pbs->processMedia();
        $this->pbs->cacheMedia();
        $mrs = $this->pbs->getMedia();
                
        $mr = array_pop($mrs);
        $this->assertEquals((string)$mr->getMediaResourceCache()->getXmlData()->item->attributes()->id, 'liveData', 'media resources was not updated from live');
        $this->assertEquals((string)$mr->getMediaResourceCache()->getXmlRef(), 'cachedResourceWithNoXmlFile', 'media resource was not updated from live api');
        
    }
        
    public function testProcessMediaResourcesWith1OutOfDateCachedYouTubeResourceCallsLiveAPI(){
        //for this to work the xml file loaded by TestYouTubeRequest must have a videoid param : ytBatch
        $this->mediaResource->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->getAPIByName('youtubeapi'));
        $this->mediaResource->setId('ytBatch');
        $cachedResource = new MediaResourceCache();
        $cachedResource->setXmlRef('outOfDataYouTube');
        $cachedResource->setXmlData($this->cachedXMLResponse);
        $cachedResource->setId($this->mediaResource->getId());
        $cachedResource->setDateCreated(new \DateTime("1st jan 1980"));
        $this->mediaResource->setMediaResourceCache($cachedResource);
        
        $this->xmlFileManager->expects($this->any())
            ->method('xmlRefExists')
            ->will($this->returnValue(true));
        $this->xmlFileManager->expects($this->any())
            ->method('createXmlRef')
            ->will($this->returnValue('liveDataYouTube'));
        $this->xmlFileManager->expects($this->any())
            ->method('getXmlData')
            ->will($this->returnValue($this->liveXMLResponse));
        
        $this->pbs = $this->pbs
            ->setConstructorArgs(array(
                array_merge(
                    $this->constructorParams,
                    array(
                        'mediaResources' => array(
                            'ytBatch' => $this->mediaResource,
                        ),
                        'xmlFileManager' => $this->xmlFileManager,
                    )
                )
            ))->setMethods(array(
                'persistMergeFlush'
            ))->getMock();
                    
        $this->pbs->processMedia();
        $this->pbs->cacheMedia();
        $mrs = $this->pbs->getMedia();
                
        $this->assertEquals((string)$mrs['ytBatch']->getMediaResourceCache()->getXmlData()->item->attributes()->id, 'liveData', 'media resources were not updated');
        $this->assertEquals((string)$mrs['ytBatch']->getMediaResourceCache()->getXmlRef(), 'liveDataYouTube', 'media resource was not updated from live api');
           
    }
    
    public function testProcessMediaResourcesWith1NonCachedYouTubeResourceAnd1NonCachedAmazonResourceCallsLiveAPI(){
        $this->mediaResource->setId('liveData');
        $mr2 = clone $this->mediaResource;
        $mr2->setId('ytBatch');
        $mr2->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->getAPIByName('youtubeapi'));
          
        $this->xmlFileManager->expects($this->any())
            ->method('xmlRefExists')
            ->will($this->returnValue(false));
        $this->xmlFileManager->expects($this->any())
            ->method('createXmlRef')
            ->will($this->returnValue('liveData'));
        $this->xmlFileManager->expects($this->any())
            ->method('getXmlData')
            ->will($this->returnValue($this->liveXMLResponse));
        
        $this->pbs = $this->pbs
            ->setConstructorArgs(array(
                array_merge(
                    $this->constructorParams,
                    array(
                        'mediaResources' => array(
                            'liveData' => $this->mediaResource,
                            'ytBatch'  => $mr2,
                        ),
                        'xmlFileManager' => $this->xmlFileManager,
                    )
                )
            ))->setMethods(array(
                'persistMergeFlush'
            ))->getMock();
        
        $this->pbs->processMedia();
        $this->pbs->cacheMedia();
        $mrs = $this->pbs->getMedia();
        
        $this->assertEquals((string)$mrs['ytBatch']->getMediaResourceCache()->getXmlData()->item->attributes()->id, 'liveData', 'youtube media resource was not updated');
        $this->assertEquals((string)$mrs['liveData']->getMediaResourceCache()->getXmlData()->item->attributes()->id, 'liveData', 'amazon media resource was not updated');
    }
    
    public function testCacheMediaWithXmlContainingDecadeUpdatesMediaResource(){
        $this->liveXMLResponse = new \SimpleXMLElement(
        '<?xml version="1.0" ?>
        <Items>
            <Item id="liveData">
                <ASIN>liveData</ASIN>
                <ItemAttributes>
                    <Title>Something with a decade [1959]</Title>
                </ItemAttributes>
            </Item>
        </Items>');
        
        $this->mediaResource->setId('liveData');
        $this->testAmazonAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\AmazonAPI')
                ->disableOriginalConstructor()
                ->setMethods(array(
                    'getListings',
                    'getDetails',
                    'getImageUrlFromXML',
                    'getItemTitleFromXML',
                ))
                ->getMock();
                
        $this->testAmazonAPI->expects($this->any())
                ->method('getDetails')
                ->will($this->returnValue($this->liveXMLResponse));
        
        $this->constructorParams['mediaResources'] = array(
            'liveData' => $this->mediaResource,
        );
        $this->constructorParams['apis'] = array(
            'amazonapi'     =>  $this->testAmazonAPI,
            'youtubeapi'    =>  $this->testYouTubeAPI,
        );
        
        $this->pbs = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\ProcessBatchStrategy')
                ->setConstructorArgs(array(
                    $this->constructorParams
                ))
                ->setMethods(array(
                    'persistMergeFlush',
                ))->getMock();
        
        $this->pbs->processMedia();
        $this->pbs->cacheMedia();
        $mrs = $this->pbs->getMedia();
                
        $this->assertEquals((string)array_pop($mrs)->getDecade()->getSlug(), '1950s', 'media resource was not updated');
        
    }
    
    public function testCacheMediaResourceWithNullDecadeAndNonExistentDecadeInXmlDoesNotUpdateMediaResource(){
        $this->liveXMLResponse = new \SimpleXMLElement(
        '<?xml version="1.0" ?>
        <Items>
            <Item id="liveData">
                <ASIN>liveData</ASIN>
                <ItemAttributes>
                    <Title>Something with no decade [dvd]</Title>
                </ItemAttributes>
            </Item>
        </Items>');
        
        $this->mediaResource->setId('liveData');
        $this->testAmazonAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\AmazonAPI')
                ->disableOriginalConstructor()
                ->setMethods(array(
                    'getListings',
                    'getDetails',
                    'getImageUrlFromXML',
                    'getItemTitleFromXML',
                ))
                ->getMock();
                
        $this->testAmazonAPI->expects($this->any())
                ->method('getDetails')
                ->will($this->returnValue($this->liveXMLResponse));
        
        $this->constructorParams['mediaResources'] = array(
            'liveData' => $this->mediaResource,
        );
        $this->constructorParams['apis'] = array(
            'amazonapi'     =>  $this->testAmazonAPI,
            'youtubeapi'    =>  $this->testYouTubeAPI,
        );
        
        $this->pbs = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\ProcessBatchStrategy')
                ->setConstructorArgs(array(
                    $this->constructorParams
                ))
                ->setMethods(array(
                    'persistMergeFlush',
                ))->getMock();
        
        $this->pbs->processMedia();
        $this->pbs->cacheMedia();
        $mrs = $this->pbs->getMedia();
                
        $this->assertNull(array_pop($mrs)->getDecade(), 'media resource decade exists');
        
    }
    
    //if the decade in the title is not in the db (e.g. 1920)
    public function testCacheMediaResourceWithNullDecadeAndInvalidDecadeInXmlDoesNotUpdateMediaResource(){
        $this->liveXMLResponse = new \SimpleXMLElement(
        '<?xml version="1.0" ?>
        <Items>
            <Item id="liveData">
                <ASIN>liveData</ASIN>
                <ItemAttributes>
                    <Title>Something with invalid decade [1919]</Title>
                </ItemAttributes>
            </Item>
        </Items>');
        
        $this->mediaResource->setId('liveData');
        $this->testAmazonAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\AmazonAPI')
                ->disableOriginalConstructor()
                ->setMethods(array(
                    'getListings',
                    'getDetails',
                    'getImageUrlFromXML',
                    'getItemTitleFromXML',
                ))
                ->getMock();
                
        $this->testAmazonAPI->expects($this->any())
                ->method('getDetails')
                ->will($this->returnValue($this->liveXMLResponse));
        
        $this->constructorParams['mediaResources'] = array(
            'liveData' => $this->mediaResource,
        );
        $this->constructorParams['apis'] = array(
            'amazonapi'     =>  $this->testAmazonAPI,
            'youtubeapi'    =>  $this->testYouTubeAPI,
        );
        
        $this->pbs = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\ProcessBatchStrategy')
                ->setConstructorArgs(array(
                    $this->constructorParams
                ))
                ->setMethods(array(
                    'persistMergeFlush',
                ))->getMock();
        
        $this->pbs->processMedia();
        $this->pbs->cacheMedia();
        $mrs = $this->pbs->getMedia();
                
        $this->assertNull(array_pop($mrs)->getDecade(), 'media resource decade was updated');
        
    }
}

?>
