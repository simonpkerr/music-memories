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


class MediaResourceListingsCacheRepositoryTest extends WebTestCase {

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
    
    private function setUpMediaSelection(array $options){
        $mediaType = self::$em->getRepository('SkNdMediaBundle:MediaType')->getMediaTypeBySlug($options['media']);
        $this->mediaSelection->setMediaType($mediaType);
        
        $apiType = self::$em->getRepository('SkNdMediaBundle:API')->findOneBy(array('id' => 1));
        $this->mediaSelection->setAPI($apiType);
        
        if(array_key_exists('decade', $options)){
            $decade = self::$em->getRepository('SkNdMediaBundle:Decade')->getDecadeBySlug($options['decade']);
            $this->mediaSelection->setDecade($decade);
        }
        
        if(array_key_exists('genre', $options)){
            $genre = self::$em->getRepository('SkNdMediaBundle:Genre')->getGenreBySlugAndMedia($options['genre'], $options['media']);
            $this->mediaSelection->setSelectedMediaGenre($genre);
        }
        
        if(array_key_exists('keywords', $options))
            $this->mediaSelection->setKeywords($options['keywords']);
        
        if(array_key_exists('page', $options))
            $this->mediaSelection->setPage($options['page']);

        
    }
    
    public function testAmazonAPIFilmsCachedListingsExistsReturnsXML(){
        $this->setUpMediaSelection(array(
                'media'     => 'film',
            ));        
        
        $results = self::$em
            ->getRepository('SkNdMediaBundle:MediaResourceListingsCache')
            ->getCachedListings($this->mediaSelection, $this->api)
        ;
        $this->assertTrue($results != null);
    }
    
    public function testAmazonAPITVSpecificDecadeCachedListingsNotExistsReturnsNull(){
        $this->setUpMediaSelection(array(
                'media'     => 'tv',
                'decade'    => '1990s',
            ));   
                
        $results = self::$em
            ->getRepository('SkNdMediaBundle:MediaResourceListingsCache')
            ->getCachedListings($this->mediaSelection, $this->api)
        ;
        $this->assertTrue($results == null);
    }
    
    public function testAmazonAPIFilmsSpecificDecadeAndGenreCachedListingsExistsReturnsXML(){
        $this->setUpMediaSelection(array(
                'media'     => 'film',
                'decade'    => '1980s',
                'genre'     => 'science-fiction',
            )); 
        
        $results = self::$em
            ->getRepository('SkNdMediaBundle:MediaResourceListingsCache')
            ->getCachedListings($this->mediaSelection, $this->api)
        ;
        $this->assertTrue($results != null);
    }
    
    public function testAmazonAPIFilmsSpecificDecadeGenreKeywordsCachedListingsExistsReturnsXML(){
        $this->setUpMediaSelection(array(
                'media'     => 'film',
                'decade'    => '1980s',
                'genre'     => 'science-fiction',
                'keywords'  => 'aliens',
            )); 
        
        $results = self::$em
            ->getRepository('SkNdMediaBundle:MediaResourceListingsCache')
            ->getCachedListings($this->mediaSelection, $this->api)
        ;
        $this->assertTrue($results != null);
    }
    
    public function testAmazonAPIFilmsSpecificDecadeGenreKeywordsPageCachedListingsExistsReturnsXML(){
        $this->setUpMediaSelection(array(
                'media'     => 'film',
                'decade'    => '1980s',
                'genre'     => 'science-fiction',
                'keywords'  => 'aliens',
                'page'      => 2,
            )); 
        
        $results = self::$em
            ->getRepository('SkNdMediaBundle:MediaResourceListingsCache')
            ->getCachedListings($this->mediaSelection, $this->api)
        ;
        $this->assertTrue($results != null);
    }
    
    
    
       
    
}
?>
