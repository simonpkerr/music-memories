<?php

namespace ThinkBack\MediaBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');

        $this->assertTrue($crawler->filter('html:contains("ThinkBack")')->count() > 0);
    }
}
