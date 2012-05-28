<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaResourceListingsCacheRepositoryTest tests
 * @author Simon Kerr
 * @version 1.0
 * @description Tests for the media resource listings cache, checking if listings exist in the cache table
 * Fixtures should be loaded first before running the tests to ensure timestamp dependent tests work
 * cmd: phpunit -c app src/ThinkBack/MediaBundle 
 * debug cmd: set XDEBUG_CONFIG=idekey=netbeans-xdebug 
 * 
 */

namespace ThinkBack\MediaBundle\Repository;
use ThinkBack\MediaBundle\DataFixtures\ORM;
use ThinkBack\MediaBundle\Entity\MediaSelection;
use ThinkBack\MediaBundle\Entity\Decade;
use ThinkBack\MediaBundle\Entity\Genre;
use ThinkBack\MediaBundle\Entity\MediaType;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class MediaResourceListingsCacheRepositoryTest extends WebTestCase {

    /*
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    private $mediaSelection;
    
    public function setUp(){
        $kernel = static::createKernel();
        $kernel->boot();
        $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->mediaSelection = new MediaSelection();
        
    }
    
    private function setUpMediaSelection(array $options){
        $mediaType = $this->em->getRepository('ThinkBackMediaBundle:MediaType')->getMediaTypeBySlug($options['media']);
        $this->mediaSelection->setMediaTypes($mediaType);
        
        if(array_key_exists('decade', $options)){
            $decade = $this->em->getRepository('ThinkBackMediaBundle:Decade')->getDecadeBySlug($options['decade']);
            $this->mediaSelection->setDecades($decade);
        }
        
        if(array_key_exists('genre', $options)){
            $genre = $this->em->getRepository('ThinkBackMediaBundle:Genre')->getGenreBySlugAndMedia($options['genre'], $options['media']);
            $this->mediaSelection->setSelectedMediaGenres($genre);
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
        
        $results = $this->em
            ->getRepository('ThinkBackMediaBundle:MediaResourceListingsCache')
            ->getCachedListings($this->mediaSelection, 'amazonapi')
        ;
        $this->assertTrue($results != null);
    }
    
    public function testAmazonAPITVCachedListingsExistsInvalidTimestampReturnsNull(){
        $this->setUpMediaSelection(array(
                'media'     => 'tv',
            ));   
        
        $results = $this->em
            ->getRepository('ThinkBackMediaBundle:MediaResourceListingsCache')
            ->getCachedListings($this->mediaSelection, 'amazonapi')
        ;
        $this->assertTrue($results == null);
    }
    
    public function testAmazonAPITVSpecificDecadeCachedListingsNotExistsReturnsNull(){
        $this->setUpMediaSelection(array(
                'media'     => 'tv',
                'decade'    => '1990',
            ));   
                
        $results = $this->em
            ->getRepository('ThinkBackMediaBundle:MediaResourceListingsCache')
            ->getCachedListings($this->mediaSelection, 'amazonapi')
        ;
        $this->assertTrue($results == null);
    }
    
    public function testAmazonAPIFilmsSpecificDecadeAndGenreCachedListingsExistsReturnsXML(){
        $this->setUpMediaSelection(array(
                'media'     => 'film',
                'decade'    => '1980',
                'genre'     => 'science-fiction',
            )); 
        
        $results = $this->em
            ->getRepository('ThinkBackMediaBundle:MediaResourceListingsCache')
            ->getCachedListings($this->mediaSelection, 'amazonapi')
        ;
        $this->assertTrue($results != null);
    }
    
    public function testAmazonAPIFilmsSpecificDecadeGenreKeywordsCachedListingsExistsReturnsXML(){
        $this->setUpMediaSelection(array(
                'media'     => 'film',
                'decade'    => '1980',
                'genre'     => 'science-fiction',
                'keywords'  => 'aliens',
            )); 
        
        $results = $this->em
            ->getRepository('ThinkBackMediaBundle:MediaResourceListingsCache')
            ->getCachedListings($this->mediaSelection, 'amazonapi')
        ;
        $this->assertTrue($results != null);
    }
    
    public function testAmazonAPIFilmsSpecificDecadeGenreKeywordsPageCachedListingsExistsReturnsXML(){
        $this->setUpMediaSelection(array(
                'media'     => 'film',
                'decade'    => '1980',
                'genre'     => 'science-fiction',
                'keywords'  => 'aliens',
                'page'      => 2,
            )); 
        
        $results = $this->em
            ->getRepository('ThinkBackMediaBundle:MediaResourceListingsCache')
            ->getCachedListings($this->mediaSelection, 'amazonapi')
        ;
        $this->assertTrue($results != null);
    }
    
    
    
       
    
}
?>
