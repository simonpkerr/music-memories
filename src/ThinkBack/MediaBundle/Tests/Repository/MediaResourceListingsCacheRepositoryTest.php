<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaResourceListingsCacheRepositoryTest tests
 * @author Simon Kerr
 * @version 1.0
 * @description Tests for the media resource listings cache, checking if listings exist in the cache table
 * Fixtures should be loaded first before running the tests to ensure timestamp dependent tests work
 */

namespace ThinkBack\MediaBundle\Repository;
use ThinkBack\MediaBundle\DataFixtures\ORM;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MediaResourceListingsCacheRepositoryTest extends WebTestCase {

    /*
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    
    public function setUp(){
        $kernel = static::createKernel();
        $kernel->boot();
        $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
          
    }
    
    public function testAmazonAPIFilmsCachedListingsExistsReturnsXML(){
        $results = $this->em
            ->getRepository('ThinkBackMediaBundle:MediaResourceListingsCache')
            ->getCachedListings(array(
                'media'     => 'film',
                'decade'    => 'all-decades',
                'genre'     =>  'all-genres',
                'keywords'  => '-',
                'page'      => 1
            ), 'amazonapi')
        ;
        $this->assertTrue($results != null);
    }
    
    public function testAmazonAPITVCachedListingsExistsInvalidTimestampReturnsNull(){
        $results = $this->em
            ->getRepository('ThinkBackMediaBundle:MediaResourceListingsCache')
            ->getCachedListings(array(
                'media'     => 'tv',
                'decade'    => 'all-decades',
                'genre'     => 'all-genres',
                'keywords'  => '-',
                'page'      => 1
            ), 'amazonapi')
        ;
        $this->assertTrue($results == null);
    }
    
    public function testAmazonAPITVSpecificDecadeCachedListingsNotExistsReturnsNull(){
        $results = $this->em
            ->getRepository('ThinkBackMediaBundle:MediaResourceListingsCache')
            ->getCachedListings(array(
                'media'     => 'tv',
                'decade'    => '1990',
                'genre'     => 'all-genres',
                'keywords'  => '-',
                'page'      => 1
            ), 'amazonapi')
        ;
        $this->assertTrue($results == null);
    }
    
    public function testAmazonAPIFilmsSpecificDecadeAndGenreCachedListingsExistsReturnsXML(){
        $results = $this->em
            ->getRepository('ThinkBackMediaBundle:MediaResourceListingsCache')
            ->getCachedListings(array(
                'media'     => 'film',
                'decade'    => '1980',
                'genre'     => 'science-fiction',
                'keywords'  => '-',
                'page'      => 1
            ), 'amazonapi')
        ;
        $this->assertTrue($results != null);
    }
    
    public function testAmazonAPIFilmsSpecificDecadeGenreKeywordsCachedListingsExistsReturnsXML(){
        //todo
    }
    
    public function testAmazonAPIFilmsSpecificDecadeGenreKeywordsPageCachedListingsInvalidTimestampReturnsNull(){
        //todo
    }
    
    public function testYouTubeAPIFilmsCachedListingsExistReturnsXML(){
        //todo
    }
    
    public function testYouTubeAPITVSpecificDecadeValidTimestampCachedListingsNotExistReturnsNull(){
        //todo
    }
    
    
}
?>
