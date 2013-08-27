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

class MemoryWallContentType extends AbstractType{
    
    public function buildForm(FormBuilderInterface $builder, array $options = null){
        
        //entity field mapped to the decade class displaying the id and decadeName properties
        $builder->add('title', 'text', array(
            'trim'     => true,
            'max_length'=> 50,
            'label'     => 'form.memory_wall.ugc.title',
            'translation_domain' => 'SkNdUserBundle',
        ));
        
        //entity field mapped to the mediatype class displaying the id and mediaName properties
        $builder->add('comments', 'textarea', array(
            'trim'      => true,
            'max_length'=> 140,
            'label'     => 'form.memory_wall.ugc.comments',
            'translation_domain' => 'SkNdUserBundle',
        ));
        
        $builder->add('image', 'file', array(
            'required'      => false,
            'label'         => 'form.memory_wall.ugc.image',
            'translation_domain' => 'SkNdUserBundle',
        ));
                
    }
    
    public function getName(){
        return 'memoryWallContent';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'  =>  'SkNd\UserBundle\Entity\MemoryWallContent',  
            'intention'   =>  'memory_wall_content'
        ));
    }
    
    
    
}

?>
