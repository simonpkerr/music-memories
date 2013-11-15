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
    protected $memoryWallUGC;
    protected $memoryWallMediaResources;
    protected $memoryWallContent;
   
    public function __construct(User $user = null, $memoryWallName = null){
        //$this->mediaResources = new ArrayCollection();
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
    
    //get aggregated media resources and ugc sorted by date or title
    public function getAllMemoryWallContent($sort = 'dateCreated', $order = 'ASC'){
        $allContent = array_merge($this->memoryWallUGC->toArray(), $this->memoryWallMediaResources->toArray());
        return $allContent;
    }
    
    public function getMemoryWallContent(){
        return $this->memoryWallContent;
    }
    
    public function setMemoryWallContent(ArrayCollection $mwc){
        $this->memoryWallContent = $mwc;
    }
    
    
    public function getMemoryWallUGC(){
        return $this->memoryWallUGC;
        
    }
    
    public function setMemoryWallUGC(ArrayCollection $mwugc){
        $this->memoryWallUGC = $mwugc;
    }
    
    public function addMemoryWallUGC(MemoryWallUGC $mwugc){
        $this->memoryWallUGC->set($mwugc->getId(), $mwugc);
    }
    
    /**
     * gets all referenced mediaresources for this memory wall
     * for the purpose of batch processing
     * @return type arraycollection
     */
    public function getMediaResources($apiId = null){
        if($this->memoryWallMediaResources->count() === 0)
            return null;
        
        $mrs = $this->memoryWallMediaResources->filter(function($mwmr) use ($apiId){
            if(!is_null($apiId)){
                return !is_null($mwmr->getMediaResource()) && $mwmr->getMediaResource()->getApi()->getId() == $apiId;
            }
            
            return !is_null($mwmr->getMediaResource());
        })->toArray();
        
        return array_map(function ($mwmr){
            return $mwmr->getMediaResource();
        }, $mrs);
    }
    
    //get array collection based on string passed to function (mwc, ugc, mr)
    private function getContentArray($type){
        switch($type){
            case 'mr':
                return $this->memoryWallMediaResources;
                break;
            case 'ugc':
                return $this->memoryWallUGC;
                break;
            default:
                return $this->memoryWallContent;               
        } 
    }
    
    public function getMWContentById($mrId, $type = 'mwc'){
        $contentArray = $this->getContentArray($type);
        $c = $contentArray->count();
        
        if(!isset($contentArray[$mrId]))
            throw new \InvalidArgumentException('Memory Wall Content not found');
        
        return $contentArray[$mrId];
    }
    
    public function deleteMWContentById($id, $type = 'mwc'){
        $contentArray = $this->getContentArray($type);
        if(!isset($contentArray[$id]))
            throw new \InvalidArgumentException('Memory Wall Content not found');
        
        $contentArray->remove($id);
    }
 
    public function addMediaResource(MediaResource $mr){
        if(isset($this->memoryWallMediaResources[$mr->getId()]))
            throw new \InvalidArgumentException('Duplicate Media Resource found');
        
        if($mr->getAPI()->getName() == 'amazonapi' && count($this->getMediaResources('amazonapi')) >= 10)
            throw new \RuntimeException('Only 10 Amazon items can be added to a wall');
        
        $mr->incrementSelectedCount();
        $mwmr = new MemoryWallMediaResource(array(
            'mw'    =>  $this,
            'mr'    =>  $mr,
        ));
        
        $this->memoryWallMediaResources->set($mr->getId(), $mwmr);
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