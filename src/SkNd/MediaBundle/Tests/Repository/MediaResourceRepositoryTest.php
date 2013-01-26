<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaResourceListingsCacheRepositoryTest tests
 * @author Simon Kerr
 * @version 1.0
 * @description Tests for the media resource listings cache, checking if listings exist in the cache table
 * Fixtures should be loaded first before running the tests to ensure timestamp dependent tests work
 * cmd: phpunit -c app src/SkNd/MediaBundle 
 * debug cmd: set XDEBUG_CONFIG=idekey=netbeans-xdebug 
 * 
 */

namespace SkNd\MediaBundle\Repository;
use SkNd\MediaBundle\DataFixtures\ORM;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Entity\Decade;
use SkNd\MediaBundle\Entity\Genre;
use SkNd\MediaBundle\Entity\MediaType;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class MediaResourceRepositoryTest extends WebTestCase {

    /*
     * @var \Doctrine\ORM\EntityManager
     */
    protected static $kernel;
    protected static $em;
    private $mediaSelection;
    private $api;
    
    public static function setUpBeforeClass(){
        self::$kernel = static::createKernel();
        self::$kernel->boot();
        self::$em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        
        $loadListingsFixtures = new ORM\LoadCachedListings();
        $loadListingsFixtures->setContainer(self::$kernel->getContainer());
        $loadListingsFixtures->load(self::$em);
    }
    
    public static function tearDownAfterClass(){
        self::$kernel = null;
        self::$em = null;
    }
    
    public function setUp(){
        $this->mediaSelection = new MediaSelection();
        $this->api = new \SkNd\MediaBundle\MediaAPI\AmazonAPI(array(
                'amazon_public_key'     => 1,
                'amazon_private_key'    => 1,
                'amazon_associate_tag'  => 1 
            ),
            new \SkNd\MediaBundle\MediaAPI\TestAmazonSignedRequest());
    }
    
    public function tearDown(){
        unset($this->mediaSelection);
        unset($this->api);
    }
    
    
    /*
     * if the session was destroyed or the url is manually typed in, the recommendations should be based on 
     * the media resource media type, decade and genre, not the media selection
     */
    public function testGetMediaResourceDetailsWhenNoSessionExistsReturnsRecommendationsBasedOnMediaResourceParameters(){
        
    }
       
    
    
    
    
    
       
    
}
?>
