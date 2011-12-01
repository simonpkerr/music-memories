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
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
                
        $this->assertTrue($crawler->filter('select#mediaSelection_mediaTypes')->count() > 0);
    }
    
    public function testMediaSelectionPost(){
        $client = static::createClient();
        $crawler = $client->request('GET', '/index');
        $form = $crawler->selectButton('submit')->form();
        $crawler = $client->submit($form, $values);        
        $this->assertTrue($crawler->filter('select#mediaSelection_mediaTypes')->count() > 0);
    }
    
    
}

?>
