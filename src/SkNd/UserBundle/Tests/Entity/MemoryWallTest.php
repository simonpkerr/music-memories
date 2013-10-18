<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * @author Simon Kerr
 * @version 1.0
 */
use SkNd\UserBundle\Entity\MemoryWall;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SkNdMediaExtensionTest extends WebTestCase
{

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetNonExistentMediaResourceByIdThrowsException(){
        $mw = new MemoryWall();
        $mw->getMWContentById('id', 'mwmr');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddDuplicateMediaResourceToMemoryWallThrowsException(){
        $mockAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\Entity\\API')
                ->setMethods(array(
                    'getName',
                    'getId'
                    ))
                ->getMock();
        $mockAPI->expects($this->any())
                    ->method('getName')
                    ->will($this->returnValue('amazonapi'));
        $mockAPI->expects($this->any())
                    ->method('getId')
                    ->will($this->returnValue('1'));
        
        $mr = $this->getMockBuilder('\\SkNd\\MediaBundle\\Entity\\MediaResource')
                ->setMethods(array(
                    'getId',
                    'getAPI'
                    ))
                ->getMock();
        $mr->expects($this->any())
                    ->method('getId')
                    ->will($this->returnValue('mrId'));       
        $mr->expects($this->any())
                    ->method('getAPI')
                    ->will($this->returnValue($mockAPI));
        
        $mw = new MemoryWall();
        $mw->addMediaResource($mr);
        
        $mw->addMediaResource($mr);
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testAddMoreThan10AmazonMediaResourcesThrowsException(){
        $mockAPI = $this->getMockBuilder('\\SkNd\\MediaBundle\\Entity\\API')
                ->setMethods(array(
                    'getName',
                    'getId'
                    ))
                ->getMock();
        $mockAPI->expects($this->any())
                    ->method('getName')
                    ->will($this->returnValue('amazonapi'));
        $mockAPI->expects($this->any())
                    ->method('getId')
                    ->will($this->returnValue('1'));
        
        $mr = $this->getMockBuilder('\\SkNd\\MediaBundle\\Entity\\MediaResource')
                ->setMethods(array(
                    'getId',
                    'getAPI'
                    ))
                ->getMock();
        $mr->expects($this->any())
                    ->method('getId')
                    ->will($this->returnValue('mrId'));       
        $mr->expects($this->any())
                    ->method('getAPI')
                    ->will($this->returnValue($mockAPI));
        
        $mw = $this->getMockBuilder('\\SkNd\\UserBundle\\Entity\\MemoryWall')
                ->setMethods(array('getMemoryWallMediaResources'))
                ->getMock();
        $mw->expects($this->any())
                    ->method('getMemoryWallMediaResources')
                    ->will($this->returnValue(array(1,2,3,4,5,6,7,8,9,10,11)));
        
        $mw->addMediaResource($mr);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */    
    public function testDeleteNonExistentMediaResourceThrowsException(){
        $mw = new MemoryWall();
        $mw->deleteMediaResourceById('id');
    }
    
    
    
}

?>
