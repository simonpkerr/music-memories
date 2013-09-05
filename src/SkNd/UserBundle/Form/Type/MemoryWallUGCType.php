<?php

/*
 * @author Simon Kerr
 * @copyright 2013 Simon Kerr
 * @version 1.0
 * MemoryWallUGCType used for creating memory wall related user generated content (photos/comments)  
 */

namespace SkNd\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MemoryWallUGCType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options = null) {

        //entity field mapped to the decade class displaying the id and decadeName properties
        $builder->add('title', 'text', array(
            'trim' => true,
            'max_length' => 50,
            'label' => 'form.memory_wall.ugc.title',
            'translation_domain' => 'SkNdUserBundle',
            'required' => true,
        ));

        //entity field mapped to the mediatype class displaying the id and mediaName properties
        $builder->add('comments', 'textarea', array(
            'trim' => true,
            'required' => false,
            'max_length' => 140,
            'label' => 'form.memory_wall.ugc.comments',
            'translation_domain' => 'SkNdUserBundle',
        ));

        $builder->add('image', 'file', array(
            'required' => false,
            'label' => 'form.memory_wall.ugc.image',
            'translation_domain' => 'SkNdUserBundle',
        ));
    }

    public function getName() {
        return 'memoryWallUGC';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'SkNd\UserBundle\Entity\MemoryWallUGC',
            'intention' => 'memory_wall_content',
            'validation_groups' => array('memoryWallContent'),
        ));
    }

}

?>
