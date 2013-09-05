<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MemoryWallMediaResource gets and sets the named entity, managing the associations between media resources
 * and memory walls
 * @author Simon Kerr
 * @version 1.0
 */
namespace SkNd\UserBundle\Entity;
use SkNd\MediaBundle\Entity\MediaResource;

class MemoryWallMediaResource extends MemoryWallContent
{
    //protected $api_id;
    //protected $mediaResource_id;
    protected $mediaResource;
    //protected $mwmrMemoryWall;
    
    public function __construct($params){
        parent::__construct($params);
        
        if(isset($params['mr']) && $params['mr'] instanceof MediaResource){
            $this->mediaResource = $params['mr'];
        }
        
        //$this->mwmrMemoryWall = $params['mw'];

    }
    
//    public function getMwmrMemoryWall(){
//        return $this->mwmrMemoryWall;
//    }
//    
//    public function setMwmrMemoryWall($mw){
//        $this->mwmrMemoryWall = $mw;
//    }
    
    public function getMemoryWallId()
    {
        return $this->memoryWall->getId();
    }
    
    public function getMediaResourceId()
    {
        return $this->mediaResource_id;
    }
    
    public function setMediaResource(MediaResource $mediaResource)
    {
        $this->mediaResource = $mediaResource;
    }

    public function getMediaResource()
    {
        return $this->mediaResource;
    }
    
}