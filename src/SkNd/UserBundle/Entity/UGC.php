<?php
/**
 * Description of UGC
 *
 * @author Simon Kerr
 * @copyright (c) 2013, Simon Kerr
 * UGC defines user generated content, notes, photos and comments on other items
 * on a memory wall
 */

namespace SkNd\UserBundle\Entity;
use SkNd\UserBundle\Entity\MemoryWallContent;

class UGC extends MemoryWallContent {
    protected $thumbnailImageUrl;
    protected $originalImageUrl;
    
    public function setThumbnailImageUrl($url){
        $this->thumbnailImageUrl = $url;
    }
    
    public function getThumbnailImageUrl(){
        return $this->thumbnailImageUrl;
    }
    
    public function setOriginalImageUrl($url){
        $this->originalImageUrl = $url;
    }
    
    public function getOriginalImageUrl(){
        return $this->originalImageUrl;
    }
    
    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->originalImageUrl;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->originalImageUrl;
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

?>
