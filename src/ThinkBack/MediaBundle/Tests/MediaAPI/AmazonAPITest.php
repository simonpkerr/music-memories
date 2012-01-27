<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * AmazonAPI tests
 * @author Simon Kerr
 * @version 1.0
 */


namespace ThinkBack\MediaBundle\Tests\MediaAPI;
use ThinkBack\MediaBundle\MediaAPI\AmazonAPI;
require_once 'src\ThinkBack\MediaBundle\MediaAPI\AmazonSignedRequest.php';

class AmazonAPITest extends \PHPUnit_Framework_TestCase {

    private $access_params;
    private $params;
    
    protected function setUp(){
        $this->access_params = array(
            'amazon_public_key'     => 'apk',
            'amazon_private_key'    => 'aupk',
            'amazon_associate_tag'  => 'aat',
        );
        
        $this->params = array(
           'BrowseNode'     =>      '1',
           'SearchIndex'    =>      'Video',
        );
    }
    
    private function getBasicTestASR(){
        return $this->getMock('AmazonSignedRequest');
    }
    
    /**
     * @expectedException RuntimeException 
     * @expectedExceptionMessage Could not connect to Amazon 
     */
    public function testGetRequestNoConnectionThrowsException(){
        
        $testASR = $this->getMockBuilder('AmazonSignedRequest')
                ->setMethods(array(
                    'aws_signed_request',
                ))
                ->getMock();
        
        $testASR->expects($this->any())
                ->method('aws_signed_request')
                ->will($this->returnValue(False));
        
        $api = new AmazonAPI($this->access_params, $testASR);
        $response = $api->getRequest($this->params);
        
    }
    
    /**
     * @expectedException LengthException
     * @expectedExceptionMessage No results were returned
     */
    public function testGetRequestEmptyDataSetThrowsException(){
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
        //$api->asr = $testASR;
        $response = $api->getRequest($this->params);
    }   
    
    public function testGetRequestValidDataSetReturnsResponse(){
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
        $response = $api->getRequest($this->params);
        $this->assertTrue($response->Items->TotalResults > 0);
    }   
  
}


?>
