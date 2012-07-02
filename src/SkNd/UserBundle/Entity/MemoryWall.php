<?php

namespace SkNd\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SkNd\UserBundle\Entity\User;
use SkNd\MediaBundle\Entity\Decade;
use SkNd\MediaBundle\Entity\MediaResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpKernel\Exception;

/**
 * SkNd\UserBundle\Entity\MemoryWall
 */
class MemoryWall
{
    public static $PRIVATE_WALLS = '0';
    public static $PUBLIC_WALLS = '1';
    public static $ALL_WALLS = '2';
    /**
     * @var integer $id
     */
    protected $id;

    protected $user;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $slug
     */
    protected $slug;

    /**
     * @var string $description
     */
    protected $description;

    /**
     * @var datetime $dateCreated
     */
    protected $dateCreated;

    /**
     * @var boolean $isPublic
     */
    protected $isPublic;

    /**
     * @var datetime $lastUpdated
     */
    protected $lastUpdated;
    
    protected $associatedDecade;
    
    protected $mediaResources;
    
    protected $memoryWallMediaResources;

    public function __construct(User $user = null){
        $this->mediaResources = new ArrayCollection();
        $this->memoryWallMediaResources = new ArrayCollection();
        
        $this->setName('My Memory Wall');
        $this->setIsPublic(true);
        if($user != null)
            $this->setUser($user);
        
        //$this->mediaResources = $this->getMediaResources();
    }
 
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * gets all referenced mediaresources for this memory wall
     * for the purpose of batch processing
     * @return type arraycollection
     */
    public function getMediaResources(){
        $mrs = new ArrayCollection();
        foreach($this->memoryWallMediaResources as $mwMr){
            $mrs->set($mwMr->getMediaResource()->getId(), $mwMr->getMediaResource());
        }
        return $mrs;
    }
    
    public function getMediaResourceById($mrId){
        if(!isset($this->memoryWallMediaResources[$mrId]))
            throw new \InvalidArgumentException('Media Resource not found');
        
        return $this->memoryWallMediaResources[$mrId]->getMediaResource();
    }
    
    public function getMemoryWallMediaResources($apiId = null){
        if($apiId != null){
            try{
                return $this->memoryWallMediaResources->filter(function($mwmr) use ($apiId){
                    return $mwmr->getApi_id() == $apiId;
                });
            } catch (\Exception $ex){
                throw new \InvalidArgumentException('there was a problem with the given api name');
            }
        }
        
        return $this->memoryWallMediaResources;                
    }
 
    public function addMediaResource(MediaResource $mr){
        if(isset($this->memoryWallMediaResources[$mr->getId()]))
            throw new \InvalidArgumentException('Duplicate Media Resource found');
        
        if($mr->getAPI()->getName == 'amazonapi' && $this->getMemoryWallMediaResources('amazonapi')->count() >= 10)
            throw new \RuntimeException('Only 10 Amazon items can be added to a wall');
        
        $mr->incrementSelectedCount();
        $mwMr = new MemoryWallMediaResource($this, $mr);        
        $this->memoryWallMediaResources->set($mr->getId(), $mwMr);
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

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string 
     */
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
    
    
    public function setAssociatedDecade(Decade $associatedDecade)
    {
        $this->associatedDecade = $associatedDecade;
    }

    public function getAssociatedDecade()
    {
        return $this->associatedDecade;
    }
    

    /**
     * Set isPublic
     *
     * @param boolean $isPublic
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    /**
     * Get isPublic
     *
     * @return boolean 
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * Set lastUpdated
     *
     * @param datetime $lastUpdated
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
    }

    /**
     * Get lastUpdated
     *
     * @return datetime 
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }
}