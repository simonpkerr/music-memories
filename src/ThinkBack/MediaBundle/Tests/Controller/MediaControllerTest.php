<?php
namespace ThinkBack\MediaBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
require_once 'src\ThinkBack\MediaBundle\MediaAPI\AmazonSignedRequest.php';
//require_once 'Zend/Loader.php';

class MediaControllerTest extends WebTestCase
{
    private $client;
    
    public function setup(){
        //load the youtube api
        //\Zend_Loader::loadClass('Zend_Gdata_YouTube');
        
        $this->client = static::createClient();
        //$this->client->insulate();
        //use the below line to inject mock services into a controller, to avoid calling live apis
        
        $amazonapi = $this->client->getContainer()->get('think_back_media.amazonapi');
        $valid_xml_data_set = simplexml_load_file('src\ThinkBack\MediaBundle\Tests\MediaAPI\valid_xml_response.xml');
        $testASR = $this->getMockBuilder('AmazonSignedRequest')
                ->setMethods(array(
                    'aws_signed_request',
                ))
                ->getMock();
        
        $testASR->expects($this->any())
                ->method('aws_signed_request')
                ->will($this->returnValue($valid_xml_data_set));
        $amazonapi->setAmazonSignedRequest($testASR);
        $this->client->getContainer()->set('think_back_media.amazonapi', $amazonapi);
        
        
        /*
         * use the below to inject a dummy Zend_Gdata_YouTube into the YouTubeAPI class
         */
        $yt = $this->client->getContainer()->get('think_back_media.youtubeapi');
        $ytReqObj = $this->getMock('\Zend_Gdata_YouTube',
                array(
                    'getVideoFeed'
                ));

        $ytReqObj->expects($this->any())
                ->method('getVideoFeed')
                ->will($this->returnValue(array()));
        
        $yt->setRequestObject($ytReqObj);
        $this->client->getContainer()->set('think_back_media.youtubeapi', $yt);
        
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
        $this->client->followRedirects(true);
        $crawler = $this->client->request('GET', '/index');
        
        
        $form = $crawler->selectButton('Search MyDay')->form();
        $form['mediaSelection[decades]']->select('1');//all decades
        $form['mediaSelection[mediaTypes]']->select('1');//Film
        $form['mediaSelection[selectedMediaGenres]']->select('1');//All Genres
        
        $crawler = $this->client->submit($form);        
        $this->assertTrue($crawler->filter('html:contains("Results")')->count() > 0);
    }
       
    public function testMediaSelectionWithKeywordsGoesToListings(){
        $crawler = $this->client->request('GET', '/index');
        
        $form = $crawler->selectButton('Search MyDay')->form();
        $form['mediaSelection[decades]']->select('1');//all decades
        $form['mediaSelection[mediaTypes]']->select('1');//Film
        $form['mediaSelection[selectedMediaGenres]']->select('1');//All Genres
        $form['mediaSelection[keywords]'] = 'sherlock';
        
        $crawler = $this->client->submit($form);       
        
        $this->assertTrue($crawler->filter('html:contains("Results")')->count() > 0);
    }
    
    /*
     * if no session data exists for the media selection form, the search should take 
     * place based on querystring values, which are then used to set the session
     */
    public function testSearchWithInvalidMediaTypeAndNoSessionThrowsException(){
        $crawler = $this->client->request('GET', '/search/funk/1990/classics');
        
        $this->assertTrue($crawler->filter('html:contains("Error")')->count() > 0);
    }
    
    /*
     * querystring params will always override session values but if invalid
     * will throw an exception
     */
    public function testSearchWithInvalidDecadeAndNoSessionThrowsException(){
        //todo
    }
    
    public function testSearchWithInvalidGenreNoSessionThrowsException(){
        //todo
    }
    
    //a default decade of all-decades should still override a session decade
    public function testSearchWithDefaultDecadeAndNonDefaultSessionDecadeOverridesSessionDecade(){
        //todo
    }
    
    public function testSearchWithDefaultGenreAndNonDefaultSessionGenreOverridesSessionGenre(){
        //todo
    }
       
    public function testMediaSelectionWithPageAndNoKeywordsGoesToListings(){
        $crawler = $this->client->request('GET', '/search/film/all-decades/classics/-/2');
        
        $this->assertTrue($crawler->filter('html:contains("Results")')->count() > 0);
    }    
    
    public function testMediaSelectionOutOfBoundsReturnsError(){
        
        $crawler = $this->client->request('GET', '/search/film/all-decades/classics/-/12');
        
        $this->assertTrue($crawler->filter('html:contains("Sorry")')->count() > 0);
    }
    
    public function testMediaDetailsWithValidRouteGoesToDetailsPage(){
        //todo
    }
    
    public function testMediaDetailsWithInvalidParametersThrowsException(){
       //todo 
    }
    
    
    
    
    
}

?>
