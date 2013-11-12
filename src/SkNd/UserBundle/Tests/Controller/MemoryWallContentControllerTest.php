<?php

namespace SkNd\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use SkNd\MediaBundle\Entity\MediaResource;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use SkNd\UserBundle\Entity\MemoryWallUGC;
use SkNd\UserBundle\Entity\MemoryWall;
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MWCMediaResourcesTest for all operations of memory walls related to mediaresources
 * the DataFixtures/ORM/LoadUsers fixtures file should be loaded first
 * php app/console doctrine:fixtures:load --fixtures=src/SkNd/UserBundle/DataFixtures/ORM --append --env=test
 * @author Simon Kerr
 * @version 1.0
 * debug cmd: set XDEBUG_CONFIG=idekey=netbeans-xdebug 
 */


class MemoryWallMediaResourcesTest extends WebTestCase
{
    protected static $kernel;
    protected $client;
    protected static $router;
    protected static $em;
    protected static $mediaapi;
    protected static $session;
    protected static $container;

    public static function setUpBeforeClass(){
        self::$kernel = static::createKernel();
        self::$kernel->boot();
        
        self::$router = self::$kernel->getContainer()->get('router');
        self::$mediaapi = self::$kernel->getContainer()->get('sk_nd_media.mediaapi');
        self::$em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        self::$session = self::$kernel->getContainer()->get('session');
        self::$container = self::$kernel->getContainer();
        
        $loadUsers = new \SkNd\UserBundle\DataFixtures\ORM\LoadUsers();
        $loadUsers->setContainer(self::$container);
        $loadUsers->load(self::$em);
    }
    
    public static function tearDownAfterClass(){
        self::$router = null;
        self::$mediaapi = null;
        self::$em = null;
        self::$session = null;
        self::$container = null;
                
    }
    
    public function setUp(){
        $this->client = static::createClient();
        $this->client->followRedirects(true);
    }
    public function tearDown(){
        unset($this->client);
    }
    
    private function login($u, $p){
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => $u,
            '_password' => $p,
        );
        $crawler = $this->client->submit($form, $params);
    }
    
    private function getNewMediaResource($id = 'testMR'){
        $mr = new MediaResource();
        $mr->setId($id);
        $mr->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->find(1));
        $mr->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->find(1));
        return $mr;
    }
    
    private function getNewUGC(MemoryWall $mw){
        $ugc = new MemoryWallUGC(array(
            'mw' => $mw));
        $ugc->setTitle('test title');
        $ugc->setComments('test comments');
        return $ugc;
    }
    
    public function testAddMediaResourceToMemoryWallWhenNotLoggedInRedirectsToLogin(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall');
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall',
            'api'   => 'amazonapi',
            'id'    => '12345',
            'title' => 'the-title',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->selectButton('Login')->count() > 0);
    }

    public function testAddMediaResourceUsingInvalidAPIThrowsException(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $this->login('testuser3', 'testuser3');
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall-2',
            'api'   => 'invalid-api',
            'id'    => 'testMR',
            'title' => 'invalid-api-title',                    
        ));
        $crawler = $this->client->request('GET', $url);
        //indicates runtime error
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
    }
    
    public function testAddMediaResourceAddsResourceIfOnlyOneWallExists(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $this->login('testuser3', 'testuser3');
        
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall-2',
            'api'   => 'amazonapi',
            'id'    => 'testMR',
            'title' => 'valid-mr-title',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('li.mwc:contains("Elf")')->count() > 0);
    }
  
    public function testAddMediaResourceToMemoryWallWhenNotLoggedInRedirectsToLoginThenToSelectWallViewIfMoreThanOneWallExists(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall');       
        
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'api'   => 'amazonapi',
            'id'    => '111',
            'title' => 'the-title',
        ));
        $crawler = $this->client->request('GET', $url);
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
        );
        $crawler = $this->client->submit($form, $params);
        
        $this->assertTrue($crawler->filter('h1:contains("Select a Memory Wall")')->count() > 0);
        //$this->assertTrue($crawler->filter('ul.bullet-list li')->count() > 1);
    }
    
    public function testAddMediaResourceToNonExistentWallThrowsException(){
        
        $this->login('testuser3', 'testuser3');
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'mwid'  => 1,
            'slug'  => 'non-existent-wall',
            'api'   => 'amazonapi',
            'id'    => 'testMR',
            'title' => 'non-existent-wall',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }
    
    public function testAddMediaResourceToOthersWallThrowsException(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-1');  
        $this->login('testuser', 'testuser');
        
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall-1',
            'api'   => 'amazonapi',
            'id'    => 'testMR',
            'title' => 'product-title',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testAddIdenticalMediaResourceTwiceToMemoryWallThrowsException(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $this->login('testuser3', 'testuser3');
        
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall-2',
            'api'   => 'amazonapi',
            'id'    => 'testMR',
            'title' => 'valid-mr-title',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('div.flashMessages li.notice')->count() > 0);
 
    }
    
    //this test simulates the function of getting records from the live api, caching them and then showing
    public function testAddYouTubeThenAmazonItemCorrectlyAddsItemsToWall(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $this->login('testuser3', 'testuser3');
        
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall-2',
            'api'   => 'youtubeapi',
            'id'    => 'testYTMR1',
            'title' => 'youtube-item-title',
        ));
        $crawler = $this->client->request('GET', $url);
        
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall-2',
            'api'   => 'amazonapi',
            'id'    => 'testAMR1',
            'title' => 'amazon-item-title',
        ));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($crawler->filter('li.mwc:contains("Elf")')->count() > 0, 'amazon not added');
        $this->assertTrue($crawler->filter('li.mwc:contains("The Running Man")')->count() > 0, 'youtube not added');
    }
    
    //testuser-mr1 is a cached record in the db
    public function testMultipleUsersCanAddTheSameMediaResourceToTheirWalls(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $this->login('testuser3', 'testuser3');        
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall-2',
            'api'   => 'amazonapi',
            'id'    => 'testuser-mr1',
            'title' => 'testuser-mr1',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('li.mwc:contains("Elf")')->count() > 0);
        
    }
    
    //from fixtures, the media resource testuser-mr1 has already been added to one wall of the user
    public function testIdenticalMediaResourcesCanBeAddedToDifferentWallsOfSameUser(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('private-wall');
        $this->login('testuser', 'testuser');        
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'private-wall',
            'api'   => 'amazonapi',
            'id'    => 'testuser-mr1',
            'title' => 'testuser-mr1',
        ));
        
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('li.mwc:contains("Elf")')->count() > 0);
    }
    
    /*
     * media resources added to memory walls should have their params updated.
     * Scenario - mr discovered through vague search (media type only). then, 
     */
    public function testMediaResourceAddedToWallDoesNotHaveVagueParametersUpdated(){
        
    }

    public function testRemoveMediaResourceFromMemoryWallShowsConfirmation(){
        
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $this->login('testuser3', 'testuser3');        
        $url = self::$router->generate('memoryWallDeleteContent', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall-2',
            'type'  => 'mr',
            'id'    => 'testuser-mr3',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue(strtolower($crawler->filter('h1')->text()) == 'delete an item from your memory wall');
    }
    
    public function testConfirmRemoveLastMediaResourceFromMemoryWallShowsWall(){
        
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall');
        $this->login('testuser', 'testuser');        
        $url = self::$router->generate('memoryWallDeleteContentConfirm', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall',
            'id'    => 'testuser-mr1',
            'type'  => 'mr',
            'confirmed' => "true",
        ));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($crawler->filter('div.flashMessages li.notice')->count() > 0, 'flash messages not shown');
        $this->assertTrue($crawler->filter('li.mwc')->count() == 0, 'media resources not all removed');
        $this->assertTrue($crawler->filter('div#memoryWallContents p:contains("Sorry")')->count() > 0, 'Contents div not empty');
    }
    
    public function testRemoveOtherUsersMediaResourceFromMemoryWallThrowsException(){
        
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall');
        $this->login('testuser', 'testuser');
        $url = self::$router->generate('memoryWallDeleteContentConfirm', array(
            'mwid'      => $mw->getId(),
            'slug'      => 'my-memory-wall',
            'type'      => 'mr',
            'id'        => 'testuser-mr2',
            'confirmed' => "true",
        ));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }
    
    public function testRemoveMediaResourceWhenNotLoggedInRedirectsToLogin(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall');
        
        $url = self::$router->generate('memoryWallDeleteContent', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall',
            'id'    => 'testuser-mr2',
            'type'      => 'mr',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->selectButton('Login')->count() > 0);
    }
    
    //similar to re-tweeting, the same item is added to the users wall
    public function testAddingAMediaResourceFromOtherWallAddsMediaResource(){
        //memory-wall-1 belongs to testuser2
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-1');  
        
        //login as testuser3
        $this->login('testuser3', 'testuser3');
        
        //wall belonging to testuser3
        $url = self::$router->generate('memoryWallShow', array(
            'id'    => $mw->getId(),
            'slug'  => $mw->getSlug(),
        ));
        $crawler = $this->client->request('GET', $url);
        $addLink = $crawler->filter('a.add-it')->first()->link();
        $this->client->click($addLink);
        
        $this->assertTrue($crawler->filter('li.mwc > strong > a')->first()->text() == 'Elf [DVD] [2003]', 'item not added');
                
    }
    
    public function testAddMWUGCWhenNotLoggedInRedirectsToLogin(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $url = self::$router->generate('addUGC', array(
            'mwid'  => $mw->getId(),
            'slug'  => $mw->getSlug(),
        ));
        $crawler = $this->client->request('GET', $url);
        //$this->assertTrue($crawler->filter(('Login')->count() > 1);
        $this->assertTrue($crawler->filter('div.flashMessages li:contains("Please log in first")')->count() > 0, 'did not redirect to login');
        
    }
    
    //only wall owners can add memory wall UGC (notes, comments, photos)
    public function testAddMWUGCToUnauthorisedWallThrowsException(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-1');  
        
        $this->login('testuser3', 'testuser3');        
        $url = self::$router->generate('addUGC', array(
            'mwid'  => $mw->getId(),
            'slug'  => $mw->getSlug(),
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        
    }
    
    public function testAddMWUGCToNonExistentWallThrowsException(){
        $this->login('testuser3', 'testuser3');
        $url = self::$router->generate('addUGC', array(
            'mwid'  => 12345,
            'slug'  => 'nonexistentmw',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isNotFound());
        
    }
    
    //for UGC that is added by wall owners (photos, notes)
    public function testAddMWUGCWithMissingTitleThrowsException(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $this->login('testuser3', 'testuser3');
        $url = self::$router->generate('addUGC', array(
            'mwid'  => $mw->getId(),
            'slug'  => $mw->getSlug(),
        ));
        $crawler = $this->client->request('GET', $url);
        //select the form
        $form = $crawler->selectButton('Add it')->form();
        $crawler->selectButton('Add it')->addContent('formnovalidate="formnovalidate"');
        $params = array(
            'memoryWallUGC[comments]' => 'some comments',                        
        );
        $crawler = $this->client->submit($form, $params);
                
        $this->assertTrue($crawler->filter('ul.form-errors:contains("You have to give a title you know")')->count() > 0, 'did not show error message');
    }
    
    public function testAddMWUGCWithTooLongTitleThrowsException(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $this->login('testuser3', 'testuser3');
        $url = self::$router->generate('addUGC', array(
            'mwid'  => $mw->getId(),
            'slug'  => $mw->getSlug(),
        ));
        $crawler = $this->client->request('GET', $url);
        //select the form
        $form = $crawler->selectButton('Add it')->form();
        $crawler->selectButton('Add it')->addContent('formnovalidate="formnovalidate"');
        $params = array(
            'memoryWallUGC[title]' => 'some long title, some long title, some long title, some long title, some long title, some long title, some long title, some long title, some long title, some long title',                        
        );
        $crawler = $this->client->submit($form, $params);
                
        $this->assertTrue($crawler->filter('ul.form-errors:contains("too long")')->count() > 0, 'did not show max length error message');
    }
    
    public function testAddMWUGCWithTooShortTitleThrowsException(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $this->login('testuser3', 'testuser3');
        $url = self::$router->generate('addUGC', array(
            'mwid'  => $mw->getId(),
            'slug'  => $mw->getSlug(),
        ));
        $crawler = $this->client->request('GET', $url);
        //select the form
        $form = $crawler->selectButton('Add it')->form();
        $crawler->selectButton('Add it')->addContent('formnovalidate="formnovalidate"');
        $params = array(
            'memoryWallUGC[title]' => 'a',                        
        );
        $crawler = $this->client->submit($form, $params);
                
        $this->assertTrue($crawler->filter('ul.form-errors:contains("too short")')->count() > 0, 'did not show min length error message');
    }
    
    public function testAddMWUGCWithTooShortCommentsFieldThrowsException(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $this->login('testuser3', 'testuser3');
        $url = self::$router->generate('addUGC', array(
            'mwid'  => $mw->getId(),
            'slug'  => $mw->getSlug(),
        ));
        $crawler = $this->client->request('GET', $url);
        //select the form
        $form = $crawler->selectButton('Add it')->form();
        $crawler->selectButton('Add it')->addContent('formnovalidate="formnovalidate"');
        $params = array(
            'memoryWallUGC[title]' => 'a title',
            'memoryWallUGC[comments]' => 'a',
        );
        $crawler = $this->client->submit($form, $params);
                
        $this->assertTrue($crawler->filter('ul.form-errors:contains("comments are a bit short")')->count() > 0, 'did not show min length error message');
    }
    
    public function testAddMWUGCWithTooLongCommentsFieldThrowsException(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $this->login('testuser3', 'testuser3');
        $url = self::$router->generate('addUGC', array(
            'mwid'  => $mw->getId(),
            'slug'  => $mw->getSlug(),
        ));
        $crawler = $this->client->request('GET', $url);
        //select the form
        $form = $crawler->selectButton('Add it')->form();
        $crawler->selectButton('Add it')->addContent('formnovalidate="formnovalidate"');
        $params = array(
            'memoryWallUGC[title]' => 'a title',
            'memoryWallUGC[comments]' => 'too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments, too long comments,',
        );
        $crawler = $this->client->submit($form, $params);
                
        $this->assertTrue($crawler->filter('ul.form-errors:contains("comments are too long")')->count() > 0, 'did not show max length error message');
    }
    
    public function testAddMWUGCWithInvalidFileTypeThrowsException() {
        //only jpg,gif,png file types allowed
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $this->login('testuser3', 'testuser3');
        $url = self::$router->generate('addUGC', array(
            'mwid'  => $mw->getId(),
            'slug'  => $mw->getSlug(),
        ));
        $crawler = $this->client->request('GET', $url);
        //select the form
        $form = $crawler->selectButton('Add it')->form();
        $invalidImage = new UploadedFile(
                'src\SkNd\UserBundle\Tests\Controller\SampleImages\invalidimage.txt',
                'invalidimage.txt',
                'text/plain'
                );
        $params = array(
            'memoryWallUGC[title]' => 'a title',
            'memoryWallUGC[image]' => $invalidImage,
            
        );
        $crawler = $this->client->submit($form, $params);
        $this->assertTrue($crawler->filter('ul.form-errors:contains("Sorry, we can only accept jpgs, gifs or pngs")')->count() > 0, 'did not show file type error message');
    }
    
//    public function testAddUGCCommentsToMemoryWallUGCDoesNotRequireTitleOrImageField() {
//        
//    }
    
    public function testDeleteMWUGCWhenNotLoggedInRedirectsToLogin(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $url = self::$router->generate('memoryWallDeleteContent', array(
            'mwid'  => $mw->getId(),
            'slug'  => $mw->getSlug(),
            'type'  => 'ugc',
            'id'    => 'ugc-random'
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('div.flashMessages li:contains("You have to log in first")')->count() > 0, 'did not redirect to login');
        
    }
    
    public function testDeleteNonExistentMWUGCThrowsException(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $this->login('testuser3', 'testuser3');
        $url = self::$router->generate('memoryWallDeleteContent', array(
            'mwid'  => $mw->getId(),
            'slug'  => $mw->getSlug(),
            'type'  => 'ugc',
            'id'    => 'ugc-nonexistent'
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isNotFound());
        
    }
    
    public function testDeleteMWUGCOnNonExistentWallThrowsException(){
        $this->login('testuser3', 'testuser3');
        $url = self::$router->generate('memoryWallDeleteContent', array(
            'mwid'  => 'non-extistent-wall',
            'slug'  => 'bogus-slug',
            'type'  => 'ugc',
            'id'    => 'ugc-nonexistent',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }
    
    public function testDeleteMWUGCOnOthersWallThrowsException(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $ugc = $this->getNewUGC($mw);
        $mw->addMemoryWallUGC($ugc);
        self::$em->persist($mw);
        self::$em->flush();
        
        $this->client->request('GET', 'logout');
        $this->login('testuser', 'testuser');
        
        $this->client->request('GET', self::$router->generate('memoryWallDeleteContent', array(
            'mwid'  => $mw->getId(),
            'slug'  => $mw->getSlug(),
            'type'  => 'ugc',
            'id'    => $ugc->getId(),
        )));
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        
        $mw->deleteMWContentById($ugc->getId(), 'ugc');
        self::$em->persist($mw);
        self::$em->flush();
    }
    
    public function testDeleteMWUGCWhenSuperAdminDeletesUGC(){
        //create ugc as testuser 3
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $ugc = $this->getNewUGC($mw);
        $mw->addMemoryWallUGC($ugc);
        self::$em->persist($mw);
        self::$em->flush();
        
        //login as testadmin and try to delete ugc
        $this->login('testadmin', 'testadmin');
        $crawler = $this->client->request('GET', self::$router->generate('memoryWallDeleteContentConfirm', array(
            'mwid'  => $mw->getId(),
            'slug'  => $mw->getSlug(),
            'type'  => 'ugc',
            'id'    => $ugc->getId(),
            'confirmed' => 'true',
        )));
        $this->assertTrue($crawler->filter('div.flashMessages li:contains("The item was successfully deleted")')->count() > 0, 'did not delete item');
                
        $mw->deleteMWContentById($ugc->getId(), 'ugc');
        self::$em->persist($mw);
        self::$em->flush();
    }
    
    //test not working, (works in dev)    
    public function testDeleteMWUGCAlsoDeletesAssociatedImage(){
        //create ugc with image and store image location
        /*$mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $image = new UploadedFile(
                'src\SkNd\UserBundle\Tests\Controller\SampleImages\validimage.jpg',
                'validimage.jpg',
                'image/jpeg'
                );
        
        
        $this->login('testuser3', 'testuser3');
        $url = self::$router->generate('addUGC', array(
            'mwid'  => $mw->getId(),
            'slug'  => $mw->getSlug(),
        ));
        
        $this->client->request(
                'POST', 
                $url,
                array('title' => 'a valid title'),
                array('image' => $image)
        );
                
        //get ugc
        $ugc = $mw->getMemoryWallUGC()->last();
                
        //assert that image exists
        $this->assertTrue(file_exists($ugc->getAbsolutePath()), "image does not exist");
        
        //delete ugc
        //$this->client->click($crawler->filter('#memoryWallContents ul li:first-child a.button')->link());
        //click confirm
        //$this->client->click($crawler->filter('#content ul li:first-child a')->link());
        $mw->deleteMWContentById($ugc->getId(), 'ugc');
        self::$em->persist($mw);
        self::$em->flush();
        
        
        //assert that image doesn't exist
        $this->assertTrue(!file_exists($ugc->getAbsolutePath()), "image still exists");
        */
    }
    
    public function testEditMWUGCOnOthersWallThrowsException() {
        
    }
    
    public function testEditMWUGCWithMissingTitleThrowsException() {
        
    }
    
    public function testEditMWUGCClearImageDeletesImage(){
        
    }
    
    public function testEditMWUGCChooseNewImageWhenOldOneExistsOverwritesOldImage(){
        
    }
    
    //only applies to comments on items
//    public function testEditOwnUGCWithinEditingTimeThresholdAllowsEdit() {
//        
//    }
    
//    public function testDeleteOwnUGCUpdatesWallContent() {
//        
//    }    
        
    //if a photo/note etc has a thread of comments coming off it
//    public function testDeleteOwnMemoryWallUGCDeletesAllRelatedUGCComments() {
//        
//    }
    
        
    //entire set of comments on an item can be hidden by super admin or wall owner
//    public function testHideCommentsOnUGCIfSuperAdminOrWallOwnerHidesComments() {
//        
//    }
    
//    public function testHideCommentsOnUGCOnOthersWallThrowsException() {
//        
//    }
    
    public function testTurnOffMWCommentsOnOthersWallThrowsException() {
        
    }
    
    public function testCloseMWCommentsOnOthersWallThrowsException(){
        
    }
    
    public function testAddCommentsToClosedMWCommentsThrowsException(){
        
    }
    
    public function testAddCommentsToMWWhenNotLoggedInRedirectsToLogin(){
        
    }
    
    public function testAddCommentsToNonExistentWallThrowsException(){
        
    }
    
    
    
//    public function testAddCommentsToClosedCommentsMemoryWallThrowsException(){
//        
//    }
    
//    public function testFlagUGCWithMissingCommentsFieldThrowsException() {
//        
//    }
    
//    public function testFlagAFlaggedUGCShowsMessageInsteadOfForm() {
//        
//    }
    
//    public function testFlagUGCEmailsWallOwnerAndUGCCreator(){
//        
//    }
//    
//    public function testFlagNonExistentUGCThrowsException(){
//        
//    }
//    
//    public function testUneditedFlaggedUGCIsRemovedAfterTheFlaggingExpirationThresholdExpires() {
//        
//    }
    
//    public function testEditFlaggedUGCHasTheFlagRemoved() {
//        
//    }
    
    //when moving items around or clicking the 'generate' layout button, users still need to publish the wall to save changes
    public function testMoveOwnMemoryWallItemsSavesCoordsOnPublish(){
        
    }
    
    public function testMoveOwnMemoryWallItemsShowsSaveConfirmationMessageIfUserTriesToNavigateAway(){
        
    }    
    
    public function testUpdateOthersWallItemPositionsThrowsException(){
        
    }
    
    public function testChangeIndexOfMRInGridViewUpdatesIndex(){
        
    }
    
    public function testChangeIndexOfMROnOthersWallThrowsException(){
        
    }
        
    //each item on a wall only loads the most recent comment, and lazily loads others if necessary
//    public function testExpandUGCCommentsLoadsLatest20Comments(){
//        
//    }
    
    //once lazily loaded, should not re-load again
//    public function testExpandUGCCommentsTwiceDoesNotLoadTwice(){
//        
//    }
    
  
    
    
    
    
}

?>