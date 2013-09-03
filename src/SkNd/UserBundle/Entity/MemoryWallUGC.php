<?php

/**
 * Memory wall owner added photos/comments
 *
 * @author Simon Kerr
 * @copyright (c) 2013, Simon Kerr
 */
namespace SkNd\UserBundle\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class MemoryWallUGC extends MemoryWallContent{
    protected $title;
    protected $thumbnailImageUrl;
    protected $originalImageUrl;
    private $tempImageUrl;
    protected $image;
    const defaultThumbnailWidth = 150;
    
    public function __construct($params){
        parent::__construct($params);
    }
    
    public function setTitle($title){
        $this->title = $title;
    }
    
    public function getTitle(){
        return $this->title;
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
        $this->createThumbnail();
        
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
        return $this->getUploadRootDir() . '/thumbs';
    }
    
    private function createThumbnail(){
        $img = imagecreatefromjpeg($this->getAbsolutePath());
        $w = imagesx($img);
        $h = imagesy($img);
        $newWidth = self::defaultThumbnailWidth;
        $newHeight = floor($h * ($newWidth / $w));
        $tmpImg = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresized($tmpImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $w, $h);
        imagejpeg($tmpImg, $this->getThumbnailUploadDir() . '/' . $this->originalImageUrl);
                
    }
}

?>
