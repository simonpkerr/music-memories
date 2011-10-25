<?php

namespace ThinkBack\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;

class GenreRepository extends EntityRepository
{
    
    public function getAllGenres(){
        $genres = $this->createQueryBuilder('g')
                ->select(array('g.id', 'g.genreName', 'g.mediaType_id'))
                ->getQuery()
                ->getResult();
        
        return json_encode($genres);  
    }
    
    
}