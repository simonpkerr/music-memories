<?php
namespace ThinkBack\MediaBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MediaControllerTest extends WebTestCase
{
    /*
        media selection action is a partial that is called by the base template
        to show the media, genre and decade selects
    */
    public function testMediaSelectionGet()
    {
        $client = static::createClient('test', true);
        $client->getContainer()->
        $crawler = $client->request('GET', '/index');
                
        $this->assertTrue($crawler->filter('select#mediaSelection_mediaTypes')->count() > 0);
    }
    
    public function testMediaSelectionPostGoesToListings(){
        
        $client = static::createClient();
        
        $crawler = $client->request('GET', '/index');
        $form = $crawler->selectButton('Find')->form();
        $form['mediaSelection[decades]']->select('1');//1930
        $form['mediaSelection[mediaTypes]']->select('1');//Film
        $form['mediaSelection[selectedMediaGenres]']->select('1');//All
        
        $crawler = $client->submit($form);        
        $this->assertTrue($crawler->filter('html:contains("Listings")')->count() > 0);
    }
    
    public function testMediaSearchGoesToSearchPage(){
        $client = static::createClient();
        
        $crawler = $client->request('GET', '/index');
        $form = $crawler->selectButton('Explore')->form();
        $form['mediaSearch[searchKeywords]'] = 'bach fugues';
        
        $crawler = $client->submit($form);        
        $this->assertTrue($crawler->filter('html:contains("Results")')->count() > 0);
    }
    
    public function testMediaSelectionOutOfBoundsReturnsError(){
        $client = static::createClient();
        $crawler = $client->request('GET', '/mediaListings/tv/1980/childrens-tv/12');
        
        $this->assertTrue($crawler->filter('html:contains("Sorry")')->count() > 0);
    }
    
}

?>
