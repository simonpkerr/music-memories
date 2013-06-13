<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * MediaResource saves and retrieves items from any api - amazon, youtube, google images, 7digital etc,
 * checking for cached versions of details 
 * @author Simon Kerr
 * @version 1.0
 */

namespace SkNd\MediaBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Util;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use SkNd\MediaBundle\MediaAPI\MediaAPI;
use SkNd\MediaBundle\Entity\Genre;
use SkNd\MediaBundle\Entity\Decade;
use SkNd\MediaBundle\Entity\MediaType;
use SkNd\MediaBundle\Entity\API;
use SkNd\MediaBundle\Entity\MediaResourceCache;
use Symfony\Component\HttpKernel\Exception;

class MediaResource
{

    protected $id;

    protected $viewCount;

    protected $selectedCount;
    
    protected $lastUpdated;
    
    protected $dateCreated;
    
    protected $mediaType;
    
    protected $decade;
    
    protected $genre;
    
    protected $api;
    
    protected $mediaResourceCache;
    
    protected $memoryWalls;

    protected $memoryWallMediaResources;
    
    //by setting this as private, it is private to the class only and 
    //not persisted to the db
    private $relatedMediaResources;
    
    public function __construct(){
        $this->viewCount = 0;
        $this->selectedCount = 0;
        $this->memoryWalls = new ArrayCollection();
        $this->memoryWallMediaResources = new ArrayCollection();
        $this->memoryWalls = $this->getMemoryWalls();
        
    }
        
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function setRelatedMediaResources(array $mediaResources){
        $this->relatedMediaResources = $mediaResources;
    }
    
    public function getRelatedMediaResources(){
        return $this->relatedMediaResources;
    }
    
    public function getMemoryWalls(){
        return $this->memoryWallMediaResources;
    }
    
    /*public function getDetailsPageParams(){
        $params = array();
        $params['id'] = $this->getId();
        
        $params['media'] = $this->mediaType->getSlug();
        if($this->decade != null)
            $params['decade'] = $this->decade->getSlug();
        if($this->genre != null)
            $params['genre'] = $this->genre->getSlug();
        
        
        return $params;
    }*/
    
    public function setMediaResourceCache(MediaResourceCache $mediaResourceCache = null){
        $this->mediaResourceCache = $mediaResourceCache;
    }
    
    public function getMediaResourceCache(){
        return $this->mediaResourceCache;
    }
    
    public function deleteMediaResourceCache(){
        $this->mediaResourceCache->deleteXmlRef();        
        $this->mediaResourceCache = null;
    }
    
    public function setMediaType(MediaType $mediaType)
    {
        $this->mediaType = $mediaType;
    }

    public function getMediaType()
    {
        return $this->mediaType;
    }


    public function setDecade(Decade $decade = null)
    {
        $this->decade = $decade;
    }


    public function getDecade()
    {
        return $this->decade;
    }

    public function setGenre(Genre $genre = null)
    {
        $this->genre = $genre;
    }

    public function getGenre()
    {
        return $this->genre;
    }
    
    public function setAPI(API $api)
    {
        $this->api = $api;
    }

    public function getAPI()
    {
        return $this->api;
    }

    public function incrementViewCount()
    {
        $this->viewCount++;
    }

    public function getViewCount()
    {
        return $this->viewCount;
    }
    
    public function incrementSelectedCount()
    {
        $this->selectedCount++;
    }

    public function getSelectedCount()
    {
        return $this->selectedCount;
    }

    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
    }

    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }


    public function getDateCreated()
    {
        return $this->dateCreated;
    }
    
    public function urlize($title){
        return Util\Urlizer::urlize((string)$title);
    }
        
    
}