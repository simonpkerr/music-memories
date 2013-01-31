<?php
/*
 * Original code Copyright (c) 2013 Simon Kerr
 * MediaResourceRepositoryTest tests
 * @author Simon Kerr
 * @version 1.0
 * debug cmd: set XDEBUG_CONFIG=idekey=netbeans-xdebug 
 * 
 */

namespace SkNd\MediaBundle\Repository;
use SkNd\MediaBundle\DataFixtures\ORM;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Entity\Decade;
use SkNd\MediaBundle\Entity\Genre;
use SkNd\MediaBundle\Entity\MediaType;
use SkNd\MediaBundle\Entity\MediaResource;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class MediaResourceRepositoryTest extends WebTestCase {

    protected static $kernel;
    protected static $em;
    private $mediaSelection;
    private $api;
    
    public static function setUpBeforeClass(){
        self::$kernel = static::createKernel();
        self::$kernel->boot();
        self::$em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        
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
    
    private function getMr(array $params){
        $mr = new MediaResource();
        if(isset($params['id']))
            $mr->setId($params['id']);
        else
            $mr->setId('mrrTest');
        
        if(isset($params['api']))
            $mr->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->getAPIByName($params['api']));
        else{
            $mr->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->getAPIByName('amazonapi'));
        }
                
        if(isset($params['media']))
            $mr->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->getMediaTypeBySlug($params['media']));
        
        if(isset($params['decade']))
            $mr->setDecade(self::$em->getRepository('SkNdMediaBundle:Decade')->getDecadeBySlug($params['decade']));
        
        if(isset($params['genre']))
            $mr->setGenre(self::$em->getRepository('SkNdMediaBundle:Genre')->getGenreBySlugAndMedia($params['genre'],$params['media']));
        
        return $mr;
    }
    
    public function testGetRecommendationsWithNoDecadeDoesntSaveRecommendations(){
        $mr = $this->getMr(array(
            'media' => 'film',
        ));
        
        $result = self::$em->getRepository('SkNdMediaBundle:MediaResource')->getMediaResourceRecommendations($mr);
        $this->assertTrue(count($result['genericMatches']) == 0 && count($result['exactMatches']) == 0, 'recommendations were returned');
    }
    
    public function testGetNoRecommendationsReturnsEmptyArray(){
        $mr = $this->getMr(array(
            'media' => 'film',
            'decade'=> '1980s',
        ));
        
        $result = self::$em->getRepository('SkNdMediaBundle:MediaResource')->getMediaResourceRecommendations($mr);
        $this->assertTrue(count($result['genericMatches']) == 0 && count($result['exactMatches']) == 0, 'recommendations were returned');
    }
    
    public function testGetRecommendationsWithSpecificGenreGetsExactMatches(){
        $mr = $this->getMr(array(
            'id'    => 'specificGenre',
            'media' => 'film',
            'decade'=> '1980s',
            'genre' => 'comedy',
        ));
        self::$em->persist($mr);
        self::$em->flush();
        
        $mr2 = clone $mr;
        $mr2->setId('anotherMr');
                
        $result = self::$em->getRepository('SkNdMediaBundle:MediaResource')->getMediaResourceRecommendations($mr2);
        
        self::$em->remove($mr);
        self::$em->flush();
                
        $this->assertTrue(count($result['genericMatches']) == 0, 'generic recommendations were returned');
        $this->assertTrue(count($result['exactMatches']) > 0, 'exact recommendations were not returned');
        
        
    }
    
    public function testGetRecommendationsFiltersOutMRPassedAsAParameter(){
        $mr = $this->getMr(array(
            'id'    => 'specificGenre',
            'media' => 'film',
            'decade'=> '1980s',
            'genre' => 'comedy',
        ));
        self::$em->persist($mr);
        self::$em->flush();
        
        $result = self::$em->getRepository('SkNdMediaBundle:MediaResource')->getMediaResourceRecommendations($mr);
        
        self::$em->remove($mr);
        self::$em->flush();
                
        $this->assertTrue(count($result['genericMatches']) == 0, 'generic recommendations were returned');
        $this->assertTrue(count($result['exactMatches']) == 0, 'exact recommendations were returned');
    }
       
    
}
?>
