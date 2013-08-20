<?php

/*
 * Original code Copyright (c) 2014 Simon Kerr
 * MemoryWall gets and sets memory walls
 * @author Simon Kerr
 * @version 1.0
 */
namespace SkNd\UserBundle\Entity;

use SkNd\UserBundle\Entity\User;
use SkNd\MediaBundle\Entity\Decade;
use SkNd\MediaBundle\Entity\MediaResource;
use Doctrine\Common\Collections\ArrayCollection;

class MemoryWall
{
    const PRIVATE_WALLS = '0';
    const PUBLIC_WALLS = '1';
    const ALL_WALLS = '2';
    protected $id;
    protected $user;
    protected $name;
    protected $slug;
    protected $description;
    protected $dateCreated;
    protected $isPublic;
    protected $lastUpdated;
    protected $associatedDecade;
    //protected $memoryWallUGC;
    //protected $memoryWallMediaResources;
    protected $memoryWallContent;

    public function __construct(User $user = null, $memoryWallName = null){
        $this->mediaResources = new ArrayCollection();
        $this->memoryWallMediaResources = new ArrayCollection();
        $this->memoryWallUGC = new ArrayCollection();
        $this->memoryWallContent = new ArrayCollection();
        
        $this->setIsPublic(true);
        if($user != null){
            if(!is_null($memoryWallName)){
                $this->setName($memoryWallName);
            }else {
                $this->setName($this->getRandomName($user));
            }
            $this->setUser($user);
        }
    }
 
    public function getId()
    {
        return $this->id;
    }
    
    private function getRandomName(User $user){
        $randomAdjectives = array(
            'cool','funky','nice','ace','well-good','rockin','original','awesome','bodacious','wick','tasty', 'old school', 'memorable'
        );
        $wallname = !is_null($user->getFirstname()) ? $user->getFirstname() : $user->getUsername();
        $wallname .= 's ' . $randomAdjectives[rand(0, count($randomAdjectives)-1)] . ' wall';

        return $wallname;
    }
    
    public function getMemoryWallContent(){
        return $this->memoryWallContent;
    }
    
    public function setMemoryWallContent(ArrayCollection $mwc){
        $this->memoryWallContent = $mwc;
    }
    
    
    /**
     * gets all referenced mediaresources for this memory wall
     * for the purpose of batch processing
     * @return type arraycollection
     */
    public function getMediaResources($apiId = null){
        /*$mrs = new ArrayCollection();
        foreach($this->memoryWallMediaResources as $mwMr){
            $mrs->set($mwMr->getMediaResource()->getId(), $mwMr->getMediaResource());
        }
        return $mrs->toArray();*/
        
        if($apiId != null){
            return array_map(function($mwc) use ($apiId){
                $mr = $mwc->getMediaResource();
                if(!is_null($mr) && $mr->getApi()->getId() == $apiId){
                    return $mr;
                }
            }, $this->memoryWallContent->toArray());
    
        }        
        
        return array_map(function($mwc){
            $mr = $mwc->getMediaResource();
            if(!is_null($mr)){
                return $mr;
            }
        }, $this->memoryWallContent->toArray());
    }
    
    public function getUGC(){
        return $this->memoryWallContent->filter(function($mwc){
            return !is_null($mwc->getComments());
        })->toArray();
    }
    
    public function getMediaResourceById($mrId){
        if(!isset($this->memoryWallMediaResources[$mrId]))
            throw new \InvalidArgumentException('Media Resource not found');
        
        return $this->memoryWallMediaResources[$mrId]->getMediaResource();
    }
    
    /*public function getMemoryWallMediaResources($apiId = null){
        if($apiId != null){
            return $this->memoryWallMediaResources->filter(function($mwmr) use ($apiId){
                return $mwmr->getApi_id() == $apiId;
            })->toArray();
        }
        
        return $this->memoryWallMediaResources->toArray();                
    }*/
    
    /*public function setMemoryWallMediaResources(ArrayCollection $mwmrs){
        $this->memoryWallMediaResources = $mwmrs;
    }
    
    public function setMemoryWallUGC(ArrayCollection $mwUGC){
        $this->memoryWallUGC = $mwUGC;
    }*/
    
 
    public function addMediaResource(MediaResource $mr){
        if(isset($this->memoryWallMediaResources[$mr->getId()]))
            throw new \InvalidArgumentException('Duplicate Media Resource found');
        
        if($mr->getAPI()->getName() == 'amazonapi' && count($this->getMediaResources('amazonapi')) >= 10)
            throw new \RuntimeException('Only 10 Amazon items can be added to a wall');
        
        $mr->incrementSelectedCount();
        //$mwMr = new MemoryWallMediaResource($this, $mr);        
        $mwc = new MemoryWallContent(array(
            'mw'    =>  $this,
            'mr'    =>  $mr,
        ));
        
        //$this->memoryWallMediaResources->set($mr->getId(), $mwMr);
        $this->memoryWallContent->set($mr->getId(), $mwc);
    }
    
    public function deleteMediaResourceById($id){
        if(!isset($this->memoryWallMediaResources[$id]))
            throw new \InvalidArgumentException('Media Resource not found');
        
        $this->memoryWallMediaResources->remove($id);
    }
    

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }
    
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    public function getDateCreated()
    {
        return $this->dateCreated;
    }
    
    public function setAssociatedDecade(Decade $associatedDecade = null)
    {
        $this->associatedDecade = $associatedDecade;
    }

    public function getAssociatedDecade()
    {
        return $this->associatedDecade;
    }

    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    public function getIsPublic()
    {
        return $this->isPublic;
    }

    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
    }

    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }
}