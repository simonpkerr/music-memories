<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * AmazonAPI tests
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Tests\MediaAPI;
use SkNd\MediaBundle\MediaAPI\AmazonAPI;
use SkNd\MediaBundle\Entity\MediaSelection;
use SkNd\MediaBundle\Entity\Decade;
use SkNd\MediaBundle\Entity\Genre;
use SkNd\MediaBundle\Entity\MediaType;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
require_once 'src\SkNd\MediaBundle\MediaAPI\AmazonSignedRequest.php';

class AmazonAPITest extends WebTestCase {

    private $access_params;
    private $mediaSelection;
    private $testASR;
    protected static $kernel;
    protected static $em;
    
    public static function setUpBeforeClass(){
        self::$kernel = static::createKernel();
        self::$kernel->boot();
        self::$em = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }
    
    public static function tearDownAfterClass(){
        self::$kernel = null;
        self::$em = null;
    }
    
    protected function setUp(){
        $this->access_params = array(
            'amazon_public_key'     => 'apk',
            'amazon_private_key'    => 'aupk',
            'amazon_associate_tag'  => 'aat',
        );
    
        $mediaType = self::$em->getRepository('SkNdMediaBundle:MediaType')->getMediaTypeBySlug('film');
        $this->mediaSelection = new MediaSelection();
        $this->mediaSelection->setMediaType($mediaType);
           
        $this->testASR = $this->getMockBuilder('\\SkNd\\MediaBundle\\MediaAPI\\AmazonSignedRequest')
                ->setMethods(array(
                    'aws_signed_request',
                ))
                ->getMock();
    }
    
    protected function tearDown(){
        unset($this->access_params);
        unset($this->mediaSelection);
        unset($this->testASR);                
    }
    
    /**
     * @expectedException RuntimeException 
     * @expectedExceptionMessage Could not connect to Amazon 
     */
    public function testGetListingsNoConnectionThrowsException(){
       
        $this->testASR->expects($this->any())
                ->method('aws_signed_request')
                ->will($this->returnValue(False));
        
        $api = new AmazonAPI($this->access_params, $this->testASR);
        $response = $api->getListings($this->mediaSelection);
        
    }
    
    /**
     * @expectedException LengthException
     * @expectedExceptionMessage No results were returned
     */
    public function testGetListingsEmptyDataSetThrowsException(){
        $empty_xml_data_set = simplexml_load_file('src\SkNd\MediaBundle\Tests\MediaAPI\SampleResponses\empty_xml_response.xml');
        
        $this->testASR->expects($this->any())
                ->method('aws_signed_request')
                ->will($this->returnValue($empty_xml_data_set));
        
        $api = new AmazonAPI($this->access_params, $this->testASR);
        $response = $api->getListings($this->mediaSelection);
    }   
    
    public function testGetListingsValidDataSetReturnsResponse(){
        $valid_xml_data_set = simplexml_load_file('src\SkNd\MediaBundle\Tests\MediaAPI\SampleResponses\valid_xml_response.xml');
        
        $this->testASR->expects($this->any())
                ->method('aws_signed_request')
                ->will($this->returnValue($valid_xml_data_set));
        
        $api = new AmazonAPI($this->access_params, $this->testASR);
        $response = $api->getListings($this->mediaSelection);
        $this->assertTrue((int)$response->TotalResults > 0);
    }
    
    /**
     * @expectedException RuntimeException 
     * @expectedExceptionMessage Could not connect to Amazon 
     */
    public function testGetDetailsWithNoResponseThrowsException(){
        $this->testASR->expects($this->any())
                ->method('aws_signed_request')
                ->will($this->returnValue(False));
        
        $api = new AmazonAPI($this->access_params, $this->testASR);
        $response = $api->getDetails(array());
    }
    
    /**
     * @expectedException RuntimeException 
     */
    public function testGetDetailsWithErrorResponseThrowsException(){
        $invalid_xml_data_set = simplexml_load_file('src\SkNd\MediaBundle\Tests\MediaAPI\SampleResponses\invalidSampleAmazonDetails.xml');
        $this->testASR->expects($this->any())
                ->method('aws_signed_request')
                ->will($this->returnValue($invalid_xml_data_set));
        
        $api = new AmazonAPI($this->access_params, $this->testASR);
        $response = $api->getDetails(array());
    }
    
    public function testGetDecadeFromXMLReturnsDecadeSlugForExistingYear(){
        
    }
    
    public function testGetDecadeFromXMLReturnsNullForNonexistentDecadeInTitle(){
        
    }
    
    /*public function testBatchProcessMoreThanThresholdNumberOfItemsTrimsArray(){
        $ids = array(1,2,3,4,5,6,7,8,9,10,11);
        $api = new AmazonAPI($this->access_params, $this->testASR);
        $response = $api->getBatch($ids);
        
        $this->assertTrue($api->)
    }*/
    
}


?>
