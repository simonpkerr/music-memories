<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * @author Simon Kerr
 * @version 1.0
 * MemoryWallType used for creating memory walls * 
 */

namespace SkNd\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

class MemoryWallType extends AbstractType{
    
    public function buildForm(FormBuilder $builder, array $options = null){
        
        //entity field mapped to the decade class displaying the id and decadeName properties
        $builder->add('name', 'text', array(
            'trim'     => true
        ));
        
        //entity field mapped to the mediatype class displaying the id and mediaName properties
        $builder->add('description', 'text', array(
            'trim'     => true
        ));
        
        $builder->add('associatedDecade', 'entity', array(
            'property'      => 'decadeName',
            'class'         => 'SkNdMediaBundle:Decade',
            'empty_value'   => 'All Decades',
            'required'      => false,
        ));
        
        $builder->add('isPublic', 'checkbox');
                
    }
    
    public function getName(){
        return 'memoryWallCreate';
    }
    
    /*public function getDefaultOptions(array $options){
        return array(
          'data_class' => 'SkNd\MediaBundle\Entity\MediaSelection',  
        );
    }*/
    
    
    
    
}

?>
