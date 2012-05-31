<?php

namespace SkNd\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * DecadesRepository
 */
class DecadeRepository extends EntityRepository
{
    public function getDecades(){
        $decades = $this->findAll();
        return $decades;
    }
    
    public function getDecadeBySlug($slug){
        return $this->findOneBy(array('slug' => $slug));
    }
    
    public function getDefaultDecade(){
        return $this->findOneBy(array('slug' => '2000'));
    }
    
}