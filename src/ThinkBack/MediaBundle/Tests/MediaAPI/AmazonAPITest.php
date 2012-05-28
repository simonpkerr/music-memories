<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * AmazonAPI tests
 * @author Simon Kerr
 * @version 1.0
 */

namespace ThinkBack\MediaBundle\Tests\MediaAPI;
use ThinkBack\MediaBundle\MediaAPI\AmazonAPI;
use ThinkBack\MediaBundle\Entity\MediaSelection;
use ThinkBack\MediaBundle\Entity\Decade;
use ThinkBack\MediaBundle\Entity\Genre;
use ThinkBack\MediaBundle\Entity\MediaType;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
require_once 'src\ThinkBack\MediaBundle\MediaAPI\AmazonSignedRequest.php';

class AmazonAPITest extends WebTestCase {

    private $access_params;
    private $mediaSelection;
    private $em;
    
    protected function setUp(){
        $this->access_params = array(
            'amazon_public_key'     => 'apk',
            'amazon_private_key'    => 'aupk',
            'amazon_associate_tag'  => 'aat',
        );
        
        $kernel = static::createKernel();
        $kernel->boot();
        $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        
        $mediaType = $this->em->getRepository('ThinkBackMediaBundle:MediaType')->getMediaTypeBySlug('film');
        $this->mediaSelection = new MediaSelection();
        $this->mediaSelection->setMediaTypes($mediaType);
               
    }
    
    private function getBasicTestASR(){
        return $this->getMock('AmazonSignedRequest');
    }
    
    /**
     * @expectedException RuntimeException 
     * @expectedExceptionMessage Could not connect to Amazon 
     */
    public function testGetListingsNoConnectionThrowsException(){
        
        $testASR = $this->getMockBuilder('AmazonSignedRequest')
                ->setMethods(array(
                    'aws_signed_request',
                ))
                ->getMock();
        
        $testASR->expects($this->any())
                ->method('aws_signed_request')
                ->will($this->returnValue(False));
        
        $api = new AmazonAPI($this->access_params, $testASR);
        $response = $api->getListings($this->mediaSelection);
        
    }
    
    /**
     * @expectedException LengthException
     * @expectedExceptionMessage No results were returned
     */
    public function testGetListingsEmptyDataSetThrowsException(){
        $empty_xml_data_set = simplexml_load_file('src\ThinkBack\MediaBundle\Tests\MediaAPI\empty_xml_response.xml');
        
        $testASR = $this->getMockBuilder('AmazonSignedRequest')
                ->setMethods(array(
                    'aws_signed_request',
                ))
                ->getMock();
        
        $testASR->expects($this->any())
                ->method('aws_signed_request')
                ->will($this->returnValue($empty_xml_data_set));
        
        $api = new AmazonAPI($this->access_params, $testASR);
        $response = $api->getListings($this->mediaSelection);
    }   
    
    public function testGetListingsValidDataSetReturnsResponse(){
        $valid_xml_data_set = simplexml_load_file('src\ThinkBack\MediaBundle\Tests\MediaAPI\valid_xml_response.xml');
        
        $testASR = $this->getMockBuilder('AmazonSignedRequest')
                ->setMethods(array(
                    'aws_signed_request',
                ))
                ->getMock();
        
        $testASR->expects($this->any())
                ->method('aws_signed_request')
                ->will($this->returnValue($valid_xml_data_set));
        
        $api = new AmazonAPI($this->access_params, $testASR);
        $response = $api->getListings($this->mediaSelection);
        $this->assertTrue($response->Items->TotalResults > 0);
    }
    
    public function testGetDetailsWithValidDataSetReturnsResponse(){
        
    }
    
    public function testGetDetailsWithInvalidDataSetReturnsResponse(){
        
    }
    
    public function testGetRecommendationsOnExactParamsReturnsData(){
        
    }
    
    public function testGetRecommendationsOnGenericParamsReturnsData(){
        
    }
    
    public function testGetRecommendationsOnAgeReturnsData(){
        
    }
    
    public function testGetRecommendationsOnValidAmazonRecordsReturnsData(){
        
    }
    
    public function testGetRecommendationsOnTimestampExpiredAmazonRecordsLooksUpAmazonData(){
        
    }
    
    public function testSetRecommendationsForNewAmazonProductStoresMediaResourceAndCachedResource(){
        
    }
    
    public function testSetRecommendationsForExistingAmazonProductUpdatesMediaResourceViewCountAndUpdatesCachedResource(){
        
    }
    
}


?>
