<?php
namespace ThinkBack\MediaBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MediaControllerTest extends WebTestCase
{
    private $client;
    
    public function setup(){
        $this->client = static::createClient();
        //use the below line to inject mock services into a controller, to avoid calling live apis
        //$this->client->getContainer()->set('think_back_media.amazonapi', new \ThinkBack\MediaBundle\MediaAPI\MockAmazonAPI());
    }
    
    /*
     *    media selection action is a partial that is called by the base template
     *    to show the media, genre and decade selects
     */
    public function testMediaSelectionGet()
    {
        $crawler = $this->client->request('GET', '/index');
                
        $this->assertTrue($crawler->filter('select#mediaSelection_mediaTypes')->count() > 0);
    }
    
    public function testMediaSelectionPostGoesToListings(){
        
        $crawler = $this->client->request('GET', '/index');
        
        $form = $crawler->selectButton('Search MyDay')->form();
        $form['mediaSelection[decades]']->select('1');//all decades
        $form['mediaSelection[mediaTypes]']->select('1');//Film
        $form['mediaSelection[selectedMediaGenres]']->select('1');//All Genres
        
        $crawler = $this->client->submit($form);        
        $this->assertTrue($crawler->filter('html:contains("Results")')->count() > 0);
    }
       
    public function testMediaSelectionWithKeywordsGoesToListings(){
        
        //todo
    }
    
    public function testMediaSelectionWithPageAndNoKeywordsGoesToListings(){
        //todo
    }    
    
    public function testMediaSelectionOutOfBoundsReturnsError(){
        
        $crawler = $this->client->request('GET', '/search/film/all-decades/classics/-/12');
        
        $this->assertTrue($crawler->filter('html:contains("Sorry")')->count() > 0);
    }
    
}

?>
