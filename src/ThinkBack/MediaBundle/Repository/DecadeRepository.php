<?php

namespace ThinkBack\MediaBundle\Repository;

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
}