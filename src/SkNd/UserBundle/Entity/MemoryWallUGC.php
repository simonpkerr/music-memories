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
   // protected $ugcMemoryWall;
    protected $title;
    protected $imagePath;
    private $tempImage;
    protected $image;
    const defaultThumbnailWidth = 150;
    
    public function __construct($params){
        parent::__construct($params);
        //$this->ugcMemoryWall = $params['mw'];
    }
    
    public function setTitle($title){
        $this->title = $title;
    }
    
    public function getTitle(){
        return $this->title;
    }
    
    public function getImagePath(){
        return $this->imagePath;
    }
    
    public function setImagePath($imagePath){
        return $this->imagePath = $imagePath;
    }
    
    public function getImage(){
        return $this->image;
    }
    
    public function setImage(UploadedFile $image = null){
        $this->image = $image;
        
        if(isset($this->imagePath)){
            $this->tempImage = $this->imagePath;
            $this->imagePath = null;
        } else {
            $this->imagePath = 'initial';
        }
    }
    
    public function preUpload(){
        if(null !== $this->getImage()){
            $fn = sha1(uniqid(mt_rand(0, 99999), true));
            $this->imagePath = $fn . '.' . $this->getImage()->guessExtension();
        }
    }
    
    public function upload(){
        if(null === $this->getImage()){
            return;
        }
        $this->createThumbnail();
        $this->getImage()->move($this->getUploadRootDir(), $this->imagePath);
        //create the thumbnail and move that as well
        
        if(isset($this->tempImage)){
            unlink($this->getUploadRootDir(). '/' . $this->tempImage);
            $this->tempImage = null;
        }
        $this->image = null;
    }
    
    public function removeUpload()
    {
        if (null !== $this->imagePath && file_exists($this->getAbsolutePath())) {
            unlink($this->getAbsolutePath());
            unlink($this->getThumbnailAbsolutePath());
        }
    }
    
    public function getAbsolutePath()
    {
        return null === $this->imagePath ? null : $this->getUploadRootDir().'/'.$this->imagePath;
    }

    public function getWebPath()
    {
        return null === $this->imagePath ? null : '/SkNd/web/' . $this->getUploadDir().'/'.$this->imagePath;
    }
    
    public function getThumbnailAbsolutePath()
    {
        return null === $this->imagePath ? null : $this->getUploadRootDir().'/thumbs/'.$this->imagePath;
    }

    public function getThumbnailWebPath()
    {
        return null === $this->imagePath ? null : '/SkNd/web/' . $this->getUploadDir().'/thumbs/'.$this->imagePath;
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
    
    
    private function createThumbnail(){
        $ext = $this->getImage()->getClientMimeType();
        $commands = null;
        switch($ext){
            case "image/png":
                $commands = array(
                    'imagecreatefrompng',
                    'imagepng',
                );
                break;
            case "image/jpeg":
                $commands = array(
                    'imagecreatefromjpeg',
                    'imagejpeg',
                );
                break;
            case "image/gif":
                $commands = array(
                    'imagecreatefromgif',
                    'imagegif',
                );
                break;
        }
        
        $img = call_user_func($commands[0],$this->getImage());
        $w = imagesx($img);
        $h = imagesy($img);
        $newWidth = $w > self::defaultThumbnailWidth ? self::defaultThumbnailWidth : $w;
        $newHeight = floor($h * ($newWidth / $w));
        $tmpImg = imagecreatetruecolor($newWidth, $newHeight);
        if($ext === "image/png" || $ext === "image/gif"){
            imagealphablending($tmpImg, false);
            imagesavealpha($tmpImg, true);
        }
        
        imagecopyresized($tmpImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $w, $h);
        call_user_func($commands[1], $tmpImg, $this->getUploadRootDir() . '/thumbs/' . $this->imagePath);
                
    }
}

?>
