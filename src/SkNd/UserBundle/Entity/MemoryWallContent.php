<?php

namespace SkNd\UserBundle\Entity;

use SkNd\UserBundle\Entity\MemoryWall;
use SkNd\MediaBundle\Entity\MediaResource;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
    protected $mediaResource;
    protected $thumbnailImageUrl;
    protected $originalImageUrl;
    private $tempImageUrl;
    protected $image;
    //protected $parent --to implement when doing item UGC
    
    public function __construct($params){
        if(!isset($params['mw'])){
            throw new \RuntimeException('memory wall not supplied');
        }
        if(!$params['mw'] instanceof MemoryWall){
            throw new \RuntimeException('invalid parameters for MemoryWallContent');
        }
        
        $this->memoryWall = $params['mw'];
        $this->id = uniqid('ugc-');
        if(isset($params['mr']) && $params['mr'] instanceof MediaResource){
            $this->mediaResource = $params['mr'];
            $this->id = $this->getMediaResource()->getId();
        }
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
    
    public function setMediaResource(MediaResource $mr = null)
    {
        if(!is_null($mr)){
            $this->mediaResource = $mr;
        }
    }

    public function getMediaResource()
    {
        return $this->mediaResource;
    }
    
    public function getOriginalImageUrl(){
        return $this->originalImageUrl;
    }
     
    
    public function getThumbnnailImageUrl(){
        return $this->thumbnailImageUrl;
    }
     
    
    public function getImage(){
        return $this->image;
    }
    
    public function setImage(UploadedFile $image = null){
        $this->image = $image;
        
        if(isset($this->originalImageUrl)){
            $this->tempImageUrl = $this->originalImageUrl;
            $this->originalImageUrl = null;
        } else {
            $this->originalImageUrl = 'initial';
        }
    }
    
    public function preUpload(){
        if(null !== $this->getImage()){
            $fn = sha1(uniqid(mt_rand(0, 99999), true));
            $this->originalImageUrl = $fn . '.' . $this->getImage()->guessExtension();
        }
    }
    
    public function upload(){
        if(null === $this->getImage()){
            return;
        }
        
        $this->getImage()->move($this->getUploadRootDir(), $this->originalImageUrl);
        //create the thumbnail and move that as well
        
        if(isset($this->tempImageUrl)){
            unlink($this->getUploadRootDir(). '/' . $this->tempImageUrl);
            $this->tempImageUrl = null;
        }
        $this->originalImageUrl = null;
    }
    
    public function removeUpload()
    {
        if (isset($this->tempImageUrl)) {
            unlink($this->tempImageUrl);
        }
    }
    
    public function getAbsolutePath()
    {
        return null === $this->originalImageUrl ? null : $this->getUploadRootDir().'/'.$this->originalImageUrl;
    }

    public function getWebPath()
    {
        return null === $this->originalImageUrl ? null : $this->getUploadDir().'/'.$this->originalImageUrl;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'bundles/SkNd/upload';
    }
    
    
    protected function getThumbnailUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'bundles/SkNd/upload/thumbs';
    }
    

}
