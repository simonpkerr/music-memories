<?php

/*
 * Original code Copyright (c) 2011 Simon Kerr
 * @author Simon Kerr
 * @version 1.0
 * MemoryWallType used for creating memory walls  
 */
namespace SkNd\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MemoryWallType extends AbstractType{
    
    public function buildForm(FormBuilderInterface $builder, array $options = null){
        
        //entity field mapped to the decade class displaying the id and decadeName properties
        $builder->add('name', 'text', array(
            'trim'     => true,
            'max_length'=> 35,
            'label'     => 'form.memory_wall.name',
            'translation_domain' => 'SkNdUserBundle',
        ));
        
        //entity field mapped to the mediatype class displaying the id and mediaName properties
        $builder->add('description', 'textarea', array(
            'trim'      => true,
            'required'  => false,
            'max_length'=> 100,
            'label'     => 'form.memory_wall.description',
            'translation_domain' => 'SkNdUserBundle',
        ));
        
        $builder->add('associatedDecade', 'entity', array(
            'property'      => 'decadeName',
            'class'         => 'SkNdMediaBundle:Decade',
            'empty_value'   => 'All Decades',
            'required'      => false,
            'label'         => 'form.memory_wall.associated_decade',
            'translation_domain' => 'SkNdUserBundle',
        ));
        
        $builder->add('isPublic', 'checkbox', array(
            'attr'     => array('checked'   => 'checked'),
            'required'  => false,
            'label'     => 'form.memory_wall.is_public',
            'translation_domain' => 'SkNdUserBundle',
        ));
                
    }
    
    public function getName(){
        return 'memoryWall';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'  =>  'SkNd\UserBundle\Entity\MemoryWall',  
            'intention'   =>  'memory_wall'
        ));
    }
    
    
    
}

?>
