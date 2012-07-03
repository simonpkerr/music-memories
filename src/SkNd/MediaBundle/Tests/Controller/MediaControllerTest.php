<?php
namespace SkNd\MediaBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use SkNd\MediaBundle\MediaAPI\MediaAPI;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Entity\MediaResource;
require_once 'src\SkNd\MediaBundle\MediaAPI\AmazonSignedRequest.php';
//require_once 'Zend/Loader.php';

class MediaControllerTest extends WebTestCase
{
    private $client;
    private $mediaSelection;
    private $testAmazonAPI;
    private $testYouTubeAPI;
    private $cachedXMLResponse;
    private $liveXMLResponse;
    private $mediaResource;
    private $session;
    
    public function setup(){
        //load the youtube api
        //\Zend_Loader::loadClass('Zend_Gdata_YouTube');
               
        $this->client = static::createClient();
        $this->client->followRedirects(true);
        
        $this->cachedXMLResponse = new \SimpleXMLElement('<?xml version="1.0" ?><items><item id="cachedData"></item></items>');
        $this->liveXMLResponse = new \SimpleXMLElement('<?xml version="1.0" ?><items><item id="amazonLiveData"></item></items>');
        
        $this->cachedYouTubeXMLResponse = new \SimpleXMLElement('<?xml version="1.0" ?><items><item id="cachedYouTubeData"></item></items>');
        $this->liveYouTubeXMLResponse = new \SimpleXMLElement('<?xml version="1.0" ?><items><item id="youTubeLiveData"></item></items>');
        
        $kernel = static::createKernel();
        $kernel->boot();
        $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        
        //for the mock object, need to provide a fully qualified path 
        $this->testAmazonAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\AmazonAPI')
                ->disableOriginalConstructor()
                ->setMethods(array(
                    'getListings',
                    'getDetails',
                ))
                ->getMock();
        //always make the getListings method of amazon api return the sample xml data
        $this->testAmazonAPI->expects($this->any())
                ->method('getListings')
                ->will($this->returnValue($this->liveXMLResponse));              
        
        //always make the getDetails method of amazon api return the sample xml data
        $this->testAmazonAPI->expects($this->any())
                ->method('getDetails')
                ->will($this->returnValue($this->liveXMLResponse));
        
        //create mock youtube object
        $this->testYouTubeAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\YouTubeAPI')
                ->disableOriginalConstructor()
                ->setMethods(array(
                    'getListings',
                    'getDetails',
                ))
                ->getMock();
        $this->testYouTubeAPI->expects($this->any())
                ->method('getListings')
                ->will($this->returnValue($this->liveYouTubeXMLResponse));              
        
        $this->testYouTubeAPI->expects($this->any())
                ->method('getDetails')
                ->will($this->returnValue($this->liveYouTubeXMLResponse));
                
        $this->session = $kernel->getContainer()->get('session');
          
        $mediaType = $this->em->getRepository('SkNdMediaBundle:MediaType')->getMediaTypeBySlug('film');
        $this->mediaSelection = new MediaSelection();
        $this->mediaSelection->setMediaType($mediaType);
        
        $this->mediaResource = new MediaResource();
        $this->mediaResource->setAPI($this->em->getRepository('SkNdMediaBundle:API')->getAPIByName('amazonapi'));
        $this->mediaResource->setMediaType($this->mediaSelection->getMediaType());
        
        $this->session->set('mediaSelection', $this->mediaSelection);
        
        $this->client->getContainer()->get('sk_nd_media.mediaapi')->setAPIs(array(
            'amazonapi'     =>  $this->testAmazonAPI,
            'youtubeapi'    =>  $this->testYouTubeAPI,
        ));
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
        
        $form = $crawler->selectButton('Search noodleDig')->form();
        $form['mediaSelection[decades]']->select('1');//all decades
        $form['mediaSelection[mediaTypes]']->select('1');//Film
        $form['mediaSelection[SelectedMediaGenre]']->select('1');//All Genres
        
        $crawler = $this->client->submit($form);        
        $this->assertTrue($crawler->filter('html:contains("Results")')->count() > 0);
    }
       
    public function testMediaSelectionWithKeywordsGoesToListings(){
        $crawler = $this->client->request('GET', '/index');
        
        $form = $crawler->selectButton('Search noodleDig')->form();
        $form['mediaSelection[decades]']->select('1');//all decades
        $form['mediaSelection[mediaTypes]']->select('1');//Film
        $form['mediaSelection[SelectedMediaGenre]']->select('1');//All Genres
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
        $crawler = $this->client->request('GET', '/search/film/invalid-decade/classics');
        
        $this->assertTrue($crawler->filter('html:contains("Error")')->count() > 0);
    }
    
    public function testSearchWithInvalidGenreNoSessionThrowsException(){
        $crawler = $this->client->request('GET', '/search/film/1990/invalid-genre');
        
        $this->assertTrue($crawler->filter('html:contains("Error")')->count() > 0);
        
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
        $crawler = $this->client->request('GET', '/mediaDetails/film/1990/all-genres/B003TO5414');
        
       $this->assertTrue($crawler->filter('html:contains("Details")')->count() > 0);
    }
    
    public function testMediaDetailsWithInvalidParametersThrowsException(){
       $crawler = $this->client->request('GET', '/mediaDetails/film/invalid-decade/all-genres/B003TO5414');
        
       $this->assertTrue($crawler->filter('html:contains("Error")')->count() > 0);
    }
    
    
    
    
    
    
    
}

?>
