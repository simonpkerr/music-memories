<?php

namespace ThinkBack\MediaBundle\Tests\Resources\MediaAPI;
use ThinkBack\MediaBundle\Resources\MediaAPI\AmazonAPI;
require_once 'src\ThinkBack\MediaBundle\Resources\MediaAPI\AmazonSignedRequest.php';

class AmazonAPITest extends \PHPUnit_Framework_TestCase {

    private $testContainer;
    private $params;
    
    protected function setUp(){
        $this->testContainer = new TestContainer();
        $this->params = array(
           'BrowseNode'     =>      '1',
           'SearchIndex'    =>      'Video',
        );
    }
    
    /**
     * @expectedException BadFunctionCallException 
     * @expectedExceptionMessage Required reference to container 
     */
    public function testConstructorNoReferencedContainerThrowsException(){
        //the above statement can be used by phpunit to test
        //exceptions that are expected
        $api = new AmazonAPI();
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
        
        $api = new AmazonAPI($this->testContainer, $testASR);
        $response = $api->getRequest($this->params);
        
    }
    
    /**
     * @expectedException LengthException
     * @expectedExceptionMessage No results were returned
     */
    public function testGetRequestEmptyDataSetThrowsException(){
        $empty_xml_data_set = simplexml_load_file('src\ThinkBack\MediaBundle\Tests\Resources\MediaAPI\empty_xml_response.xml');
        
        $testASR = $this->getMockBuilder('AmazonSignedRequest')
                ->setMethods(array(
                    'aws_signed_request',
                ))
                ->getMock();
        
        $testASR->expects($this->any())
                ->method('aws_signed_request')
                ->will($this->returnValue($empty_xml_data_set));
        
        $api = new AmazonAPI($this->testContainer, $testASR);
        //$api->asr = $testASR;
        $response = $api->getRequest($this->params);
    }   
    
    public function testGetRequestValidDataSetReturnsResponse(){
        $valid_xml_data_set = simplexml_load_file('src\ThinkBack\MediaBundle\Tests\Resources\MediaAPI\valid_xml_response.xml');
        
        $testASR = $this->getMockBuilder('AmazonSignedRequest')
                ->setMethods(array(
                    'aws_signed_request',
                ))
                ->getMock();
        
        $testASR->expects($this->any())
                ->method('aws_signed_request')
                ->will($this->returnValue($valid_xml_data_set));
        
        $api = new AmazonAPI($this->testContainer, $testASR);
        $response = $api->getRequest($this->params);
        $this->assertTrue($response->Items->TotalResults > 0);
    }   
  
}

/**
 * test container for mimicking the parameters in the 
 * actual container object for the controller
 */
class TestContainer {
    public $parameters = array(
        'amazon_public_key'     => 'apk',
        'amazon_uk_private_key' => 'aupk',
        'amazon_associate_tag'  => 'aat',
    );
}

?>
