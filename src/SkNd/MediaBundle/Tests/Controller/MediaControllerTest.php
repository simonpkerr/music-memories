<?php
namespace SkNd\MediaBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use SkNd\MediaBundle\MediaAPI\MediaAPI;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Entity\MediaResource;
require_once 'src\SkNd\MediaBundle\MediaAPI\AmazonSignedRequest.php';

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * All operations related to media selection, searching, details etc
 * @author Simon Kerr
 * @version 1.0
 * debug cmd: set XDEBUG_CONFIG=idekey=netbeans-xdebug 
 */


class MediaControllerTest extends WebTestCase
{
    private $client;
    private $mediaSelection;
    private $mediaResource;
    
    protected static $kernel;
    protected static $em;
    protected static $session;
    
    public static function setUpBeforeClass(){
        self::$kernel = static::createKernel();
        self::$kernel->boot();
        self::$em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        self::$session = self::$kernel->getContainer()->get('session');
    }
    
    public static function tearDownAfterClass(){
        self::$kernel = null;
        self::$em = null;
        self::$session = null;
    }
    
    public function setup(){
        $this->client = static::createClient();
        $this->client->followRedirects(true);
        
        $mediaType = self::$em->getRepository('SkNdMediaBundle:MediaType')->getMediaTypeBySlug('film');
        $this->mediaSelection = new MediaSelection();
        $this->mediaSelection->setMediaType($mediaType);
        
        $this->mediaResource = new MediaResource();
        $this->mediaResource->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->getAPIByName('amazonapi'));
        $this->mediaResource->setMediaType($this->mediaSelection->getMediaType());
        
        self::$session->set('mediaSelection', $this->mediaSelection);
        
    }
    
    public function tearDown(){
        unset($this->mediaResource);
        unset($this->mediaSelection);
        unset($this->client);
    }
    
    /*
     *    media selection action is a partial that is called by the base template
     *    to show the media, genre and decade selects
     */
    public function testMediaSelectionGet()
    {
        $crawler = $this->client->request('GET', '/index');
                
        $this->assertTrue($crawler->filter('select#mediaSelection_mediaType')->count() > 0);
    }
    
    public function testMediaSelectionPostGoesToListings(){
        
        $crawler = $this->client->request('GET', '/index');
        
        $form = $crawler->selectButton('Noodle it!')->form();
        $form['mediaSelection[decade]']->select('1');//all decades
        $form['mediaSelection[mediaType]']->select('1');//Film
        $form['mediaSelection[selectedMediaGenre]']->select('1');//All Genres
        
        $crawler = $this->client->submit($form);        
        $this->assertTrue($crawler->filter('h1:contains("Results")')->count() > 0);
    }
       
    public function testMediaSelectionWithKeywordsGoesToListings(){
        $crawler = $this->client->request('GET', '/index');
        
        $form = $crawler->selectButton('Noodle it!')->form();
        $form['mediaSelection[decade]']->select('1');//all decades
        $form['mediaSelection[mediaType]']->select('1');//Film
        $form['mediaSelection[selectedMediaGenre]']->select('1');//All Genres
        $form['mediaSelection[keywords]'] = 'sherlock';
        
        $crawler = $this->client->submit($form);       
        
        $this->assertTrue($crawler->filter('h1:contains("Results")')->count() > 0);
    }
    
    /*
     * if no session data exists for the media selection form, the search should take 
     * place based on querystring values, which are then used to set the session
     */
    public function testSearchWithInvalidMediaTypeThrowsException(){
        $crawler = $this->client->request('GET', '/search/funk/1990/classics');
        
        $this->assertTrue($crawler->filter('html:contains("Error")')->count() > 0);
    }
    
    /*
     * querystring params will always override session values but if invalid
     * will throw an exception
     */
    public function testSearchWithInvalidDecadeThrowsException(){
        $crawler = $this->client->request('GET', '/search/film/invalid-decade/classics');
        
        $this->assertTrue($crawler->filter('html:contains("Error")')->count() > 0);
    }
    
    public function testSearchWithInvalidGenreThrowsException(){
        $crawler = $this->client->request('GET', '/search/film/1990/invalid-genre');
        
        $this->assertTrue($crawler->filter('html:contains("Error")')->count() > 0);
        
    }
    
    public function testMediaSelectionWithPageAndNoKeywordsGoesToListings(){
        $crawler = $this->client->request('GET', '/search/film/all-decades/classics/-/2');
        
        $this->assertTrue($crawler->filter('html:contains("Results")')->count() > 0);
    }    
    
    public function testMediaSelectionOutOfBoundsReturnsError(){
        
        $crawler = $this->client->request('GET', '/search/film/all-decades/classics/-/12');
        
        $this->assertTrue($crawler->filter('div.flashMessages ul li:contains("wrong with Amazon")')->count() > 0);
    }
    
    public function testMediaDetailsWithValidRouteGoesToDetailsPage(){
        $crawler = $this->client->request('GET', '/mediaDetails/film/1990/all-genres/B003TO5414');
        
       $this->assertTrue($crawler->filter('html:contains("Details")')->count() > 0);
    }
    
    public function testMediaDetailsWithInvalidParametersThrowsException(){
       $crawler = $this->client->request('GET', '/mediaDetails/film/invalid-decade/all-genres/B003TO5414');
        
       $this->assertTrue($crawler->filter('html:contains("Error")')->count() > 0);
    }
    
    
    
    
    
    
    
}

?>
