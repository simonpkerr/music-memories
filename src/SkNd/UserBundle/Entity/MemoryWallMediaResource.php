<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MemoryWallMediaResource gets and sets the named entity, managing the associations between media resources
 * and memory walls
 * @author Simon Kerr
 * @version 1.0
 */
namespace SkNd\UserBundle\Entity;

use SkNd\UserBundle\Entity\MemoryWall;
use SkNd\MediaBundle\Entity\MediaResource;
use SkNd\UserBundle\Entity\MemoryWallContent;

class MemoryWallMediaResource extends MemoryWallContent
{
    protected $api_id;
    protected $mediaResource_id;
    protected $mediaResource;
    
    public function __construct(MemoryWall $mw, MediaResource $mr){
        parent::__construct($mw);
        
        $this->mediaResource = $mr;             
        $this->mediaResource_id = $mr->getId();
        $this->api_id = $mr->getAPI()->getId();
 
    }
    
    public function getMemoryWallId()
    {
        return $this->memoryWall->getId();
    }
    
    public function getMediaResourceId()
    {
        return $this->mediaResource_id;
    }
    
    public function getId(){
        return array(
            $this->memoryWall->getId(),
            $this->mediaResource->getId(),
        );
    }
    
    public function getApi_id(){
        return $this->api_id;
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