<?php

namespace SkNd\UserBundle\Entity;
use SkNd\UserBundle\Entity\MemoryWall;

/**
 * MemoryWallContent
 */
class MemoryWallContent
{

    protected $id;
    protected $coords;
    protected $dateCreated;
    protected $flaggedComments;
    protected $flaggedDate;
    protected $isFlagged;
    protected $lastModified;
    protected $wallIndex;
    protected $slug;
    protected $title;
    protected $comments;
    protected $memoryWall;
    //protected $parent --to implement when doing item UGC
    
    public function __construct(MemoryWall $mw){
        $this->memoryWall = $mw;
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
     * Set coords
     *
     * @param array $coords
     * @return MemoryWallContent
     */
    public function setCoords($coords)
    {
        $this->coords = $coords;
        return $this;
    }

    /**
     * Get coords
     *
     * @return array 
     */
    public function getCoords()
    {
        return $this->coords;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     * @return MemoryWallContent
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime 
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Set flaggedComments
     *
     * @param string $flaggedComments
     * @return MemoryWallContent
     */
    public function setFlaggedComments($flaggedComments)
    {
        $this->flaggedComments = $flaggedComments;
    
        return $this;
    }

    /**
     * Get flaggedComments
     *
     * @return string 
     */
    public function getFlaggedComments()
    {
        return $this->flaggedComments;
    }

    /**
     * Set flaggedDate
     *
     * @param \DateTime $flaggedDate
     * @return MemoryWallContent
     */
    public function setFlaggedDate($flaggedDate)
    {
        $this->flaggedDate = $flaggedDate;
    
        return $this;
    }

    /**
     * Get flaggedDate
     *
     * @return \DateTime 
     */
    public function getFlaggedDate()
    {
        return $this->flaggedDate;
    }

    /**
     * Set isFlagged
     *
     * @param boolean $isFlagged
     * @return MemoryWallContent
     */
    public function setIsFlagged($isFlagged)
    {
        $this->isFlagged = $isFlagged;
    
        return $this;
    }

    /**
     * Get isFlagged
     *
     * @return boolean 
     */
    public function getIsFlagged()
    {
        return $this->isFlagged;
    }

    /**
     * Set lastModified
     *
     * @param \DateTime $lastModified
     * @return MemoryWallContent
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;
    
        return $this;
    }

    /**
     * Get lastModified
     *
     * @return \DateTime 
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * Set wallIndex
     *
     * @param integer $wallIndex
     * @return MemoryWallContent
     */
    public function setWallIndex($wallIndex)
    {
        $this->wallIndex = $wallIndex;
    
        return $this;
    }

    /**
     * Get wallIndex
     *
     * @return integer 
     */
    public function getWallIndex()
    {
        return $this->wallIndex;
    }
    
    public function getSlug(){
        return $this->slug;
        
    }
    
    public function setTitle($title){
        $this->title = $title;
    }
    
    public function getTitle(){
        return $this->title;
    }
    
    public function setComments($comments){
        $this->comments = $comments;
    }
    
    public function getComments(){
        return $this->comments;
    }
    
    public function setMemoryWall(MemoryWall $memoryWall)
    {
        $this->memoryWall = $memoryWall;
    }

    public function getMemoryWall()
    {
        return $this->memoryWall;
    }

}
