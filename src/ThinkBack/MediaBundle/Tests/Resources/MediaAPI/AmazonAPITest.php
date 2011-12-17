<?php

namespace ThinkBack\MediaBundle\Tests\Resources\MediaAPI;
use ThinkBack\MediaBundle\Resources\MediaAPI\AmazonAPI;
require_once 'src\ThinkBack\MediaBundle\Resources\MediaAPI\AmazonAPI.php';

class AmazonAPITest extends \PHPUnit_Framework_TestCase {

    private $testContainer;
    
    protected function setUp(){
        $this->testContainer = new TestContainer();
    }
    
    /**
     * @expectedException BadFunctionCallException 
     * @expectedExceptionMessage Required reference to container 
     */
    public function testConstructorWithNoReferencedContainer(){
        //the above statement can be used by phpunit to test
        //exceptions that are expected
        $api = new AmazonAPI();
    }
    
    
//    /**
//     * @expectedException RuntimeException 
//     * @expectedExceptionMessage Could not connect to Amazon 
//     */
    public function testGetRequestNoConnection(){
        $params = array(
           'BrowseNode'     =>      '1',
           'SearchIndex'    =>      'Video',
        );
        $testASR = $this->getMockBuilder('AmazonAPI')
                //->setMockClassName('AmazonAPI')
                ->setMethods(array(
                    'queryAmazon',
                    '__construct',
                    'getRequest',
                    'verifyXmlResponse',
                    ))
                ->setConstructorArgs(array($this->testContainer))
                ->getMock();
        
        $testASR->expects($this->any())
                ->method('queryAmazon')
                ->will($this->returnValue(False));
        /*$testASR->expects($this->any())
                ->method('__construct')
                ->with($this->equalTo($this->testContainer));
        $testASR->expects($this->any())
                ->method('getRequest')
                ->with($this->any($params));
        $testASR->expects($this->any())
                ->method('verifyXmlResponse')
                ->will($this->returnValue(False)); */                       
                        
        
        //$api = new AmazonAPI($this->testContainer);
        $response = $testASR->getRequest($params);
        //$this->assert
        
    }
    
    /**
     * @expectedException LengthException
     * @expectedExceptionMessage No results were returned
     */
    /*public function testGetRequestReturnsEmptyDataSet(){
        $empty_xml_data_set = simplexml_load_file('src\ThinkBack\MediaBundle\Tests\Resources\MediaAPI\empty_xml_response.xml');
        
        $params = array(
           'BrowseNode'     =>      '1',//not a known browse node
           'SearchIndex'    =>      'Video',
        );
        $testASR = $this->getMock('AmazonSignedRequest', array('execCurl'));
        $testASR->expects($this->any())
                ->method('execCurl')
                ->will($this->returnValue($empty_xml_data_set));
        
        $api = new AmazonAPI($this->testContainer);
        $response = $api->getRequest($params);
    }*/
   
  
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
