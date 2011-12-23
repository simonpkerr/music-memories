<?php

namespace ThinkBack\MediaBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');

        $this->assertTrue($crawler->filter('html:contains("MyDay")')->count() > 0);
    }
    
    /*public function testError(){
        $client = static::createClient();
        
        $crawler = $client->request('GET', '/error');
        
        $this->assertTrue($crawler->filter('html:contains("Oh dear")')->count() > 0);
    }*/
}
