<?php

namespace SkNd\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use SkNd\MediaBundle\Entity\MediaResource;

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MWCMediaResourcesTest for all operations of memory walls related to mediaresources
 * the DataFixtures/ORM/LoadUsers fixtures file should be loaded first
 * php app/console doctrine:fixtures:load --fixtures=src/SkNd/UserBundle/DataFixtures/ORM --append
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
    
    private function getNewMediaResource($id = 'testMR'){
        $mr = new MediaResource();
        $mr->setId($id);
        $mr->setAPI(self::$em->getRepository('SkNdMediaBundle:API')->find(1));
        $mr->setMediaType(self::$em->getRepository('SkNdMediaBundle:MediaType')->find(1));
        return $mr;
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
        
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
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
                
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall-2',
            'api'   => 'amazonapi',
            'id'    => 'testMR',
            'title' => 'valid-mr-title',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('li.mw-MediaResource:contains("Elf")')->count() > 0);
    }
  
    public function testAddMediaResourceToMemoryWallWhenNotLoggedInRedirectsToLoginThenToSelectWallViewIfMoreThanOneWallExists(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');       
        
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'api'   => 'amazonapi',
            'id'    => '111',
            'title' => 'the-title',
        ));
        $crawler = $this->client->request('GET', $url);
        
        //$crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
        );
        $crawler = $this->client->submit($form, $params);
        
        $this->assertTrue($crawler->filter('h1')->text() == 'Select a Memory Wall');
        $this->assertTrue($crawler->filter('ul.bullet-list li')->count() > 1);
    }
    
    public function testAddMediaResourceToNonExistentWallThrowsException(){
        
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
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
        
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
        );
        $crawler = $this->client->submit($form, $params);
        
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
        
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
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
        
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
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
        
        $this->assertTrue($crawler->filter('li.mw-MediaResource:contains("Elf")')->count() > 0);
        $this->assertTrue($crawler->filter('li.mw-MediaResource:contains("The Running Man")')->count() > 0);
    }
    
    //testuser-mr1 is a cached record in the db
    public function testMultipleUsersCanAddTheSameMediaResourceToTheirWalls(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall-2',
            'api'   => 'amazonapi',
            'id'    => 'testuser-mr1',
            'title' => 'testuser-mr1',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('li.mw-MediaResource:contains("Elf")')->count() > 0);
        
    }
    
    //from fixtures, the media resource testuser-mr1 has already been added to one wall of the user
    public function testIdenticalMediaResourcesCanBeAddedToDifferentWallsOfSameUser(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('private-wall');
        
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = self::$router->generate('memoryWallAddMediaResource', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'private-wall',
            'api'   => 'amazonapi',
            'id'    => 'testuser-mr1',
            'title' => 'testuser-mr1',
        ));
        
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('li.mw-MediaResource:contains("Elf")')->count() > 0);
        
    }
    
    /*
     * media resources added to memory walls should have their params updated.
     * Scenario - mr discovered through vague search (media type only). then, 
     */
    public function testMediaResourceAddedToWallDoesNotHaveVagueParametersUpdated(){
        
    }

    public function testRemoveMediaResourceFromMemoryWallShowsConfirmation(){
        
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-2');
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = self::$router->generate('memoryWallDeleteMediaResource', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall-2',
            'id'    => 'testuser-mr3',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue(strtolower($crawler->filter('h1')->text()) == 'delete an item from your memory wall');
    }
    
    public function testConfirmRemoveLastMediaResourceFromMemoryWallShowsWall(){
        
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall');
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = self::$router->generate('memoryWallDeleteMediaResourceConfirm', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall',
            'id'    => 'testuser-mr1',
            'confirmed' => "true",
        ));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($crawler->filter('div.flashMessages li.notice')->count() > 0, 'flash messages not shown');
        $this->assertTrue($crawler->filter('li.mw-MediaResource')->count() == 0, 'media resources not all removed');
        $this->assertTrue($crawler->filter('div#memoryWallContents p:contains("Sorry")')->count() > 0, 'Contents div not empty');
    }
    
    public function testRemoveOtherUsersMediaResourceFromMemoryWallThrowsException(){
        
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall');
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser',
            '_password' => 'testuser',
        );
        $crawler = $this->client->submit($form, $params);
        
        $url = self::$router->generate('memoryWallDeleteMediaResourceConfirm', array(
            'mwid'      => $mw->getId(),
            'slug'      => 'my-memory-wall',
            'id'        => 'testuser-mr2',
            'confirmed' => "true",
        ));
        $crawler = $this->client->request('GET', $url);
        
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }
    
    public function testRemoveMediaResourceWhenNotLoggedInRedirectsToLogin(){
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall');
        
        $url = self::$router->generate('memoryWallDeleteMediaResource', array(
            'mwid'  => $mw->getId(),
            'slug'  => 'my-memory-wall',
            'id'    => 'testuser-mr2',
        ));
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->selectButton('Login')->count() > 0);
    }
    
    //similar to re-tweeting, the same item is added to the users wall
    public function testAddingAMediaResourceFromOtherWallAddsMediaResource(){
        //memory-wall-1 belongs to testuser2
        $mw = self::$em->getRepository('SkNdUserBundle:MemoryWall')->getMemoryWallBySlug('my-memory-wall-1');  
        
        //login as testuser3
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form();
        $params = array(
            '_username' => 'testuser3',
            '_password' => 'testuser3',
        );
        $crawler = $this->client->submit($form, $params);
        
        //wall belonging to testuser3
        $url = self::$router->generate('memoryWallShow', array(
            'id'    => $mw->getId(),
            'slug'  => $mw->getSlug(),
        ));
        $crawler = $this->client->request('GET', $url);
        $addLink = $crawler->filter('a.add-it')->first()->link();
        $this->client->click($addLink);
        
        $this->assertTrue($crawler->filter('li.mw-MediaResource > strong > a')->first()->text() == 'Elf [DVD] [2003]', 'item not added');
                
    }
    
    //only wall owners can add memory wall UGC (notes, comments, photos)
    public function testAddMemoryWallUGCToUnauthorisedWallThrowsException(){
        
    }
    
    public function testAddMemoryWallUGCToNonExistentWallThrowsException(){
        
    }
    
    //for UGC that is added by wall owners (photos, notes)
    public function testAddMemoryWallUGCWithMissingTitleThrowsException(){
        
    }
    
    public function testAddMemoryWallUGCWithTooLongTitleThrowsException(){
        
    }
    
    public function testAddMemoryWallUGCWithTooShortTitleThrowsException(){
        
    }
    
    //for UGC that is added by wall owners (photos, notes)
    public function testAddMemoryWallUGCWithMissingCommentsFieldThrowsException(){
        
    }
    
    public function testAddUGCWithTooShortCommentsFieldThrowsException(){
        
    }
    
    public function testAddUGCWithTooLongCommentsFieldThrowsException(){
        
    }
    
    public function testAddUGCWithInvalidFileTypeThrowsException() {
        //only jpg,gif,png file types allowed
    }
    
    public function testAddUGCCommentsToMemoryWallUGCDoesNotRequireTitleOrImageField() {
        
    }
    
    public function testAddUGCWhenNotLoggedInRedirectsToLogin() {
        
    }
    
    public function testAddMemoryWallUGCToOthersWallThrowsException() {
        
    }
    
    public function testEditMemoryWallUGCOnOthersWallThrowsException() {
        
    }
    
    public function testEditMemoryWallUGCWithMissingTitleThrowsException() {
        
    }
    
    //only applies to comments on items
    public function testEditOwnUGCWithinEditingTimeThresholdAllowsEdit() {
        
    }
    
    public function testDeleteOwnUGCUpdatesWallContent() {
        
    }    
        
    //if a photo/note etc has a thread of comments coming off it
    public function testDeleteOwnMemoryWallUGCDeletesAllRelatedUGCComments() {
        
    }
    
    public function testDeleteUGCOnOthersWallThrowsException() {
        
    }
    
    public function testDeleteUGCOnOthersWallIfSuperAdminDeletesUGC() {
        
    }
    
    //entire set of comments on an item can be hidden by super admin or wall owner
    public function testHideCommentsOnUGCIfSuperAdminOrWallOwnerHidesComments() {
        
    }
    
    public function testHideCommentsOnUGCOnOthersWallThrowsException() {
        
    }
    
    public function testTurnOffMemoryWallCommentsOnOthersWallThrowsException() {
        
    }
    
    public function testCloseCommentsOnOthersWallThrowsException(){
        
    }
    
    public function testAddCommentsToClosedCommentsUGCThrowsException(){
        
    }
    
    public function testAddCommentsToClosedCommentsMemoryWallThrowsException(){
        
    }
    
    public function testFlagUGCWithMissingCommentsFieldThrowsException() {
        
    }
    
    public function testFlagAFlaggedUGCShowsMessageInsteadOfForm() {
        
    }
    
    public function testFlagUGCEmailsWallOwnerAndUGCCreator(){
        
    }
    
    public function testFlagNonExistentUGCThrowsException(){
        
    }
    
    public function testUneditedFlaggedUGCIsRemovedAfterTheFlaggingExpirationThresholdExpires() {
        
    }
    
    public function testEditFlaggedUGCHasTheFlagRemoved() {
        
    }
    
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
    public function testExpandUGCCommentsLoadsLatest20Comments(){
        
    }
    
    //once lazily loaded, should not re-load again
    public function testExpandUGCCommentsTwiceDoesNotLoadTwice(){
        
    }
    
    
    
}

?>