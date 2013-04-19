<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * Modified by Simon Kerr for the purposes of developing noodleDig (2012)
 */

namespace SkNd\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

class ProfileFormType extends BaseType
{
    private $class;

    /**
     * @param string $class The User class name
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /*public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
  
    }*/
    
    protected function buildUserForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildUserForm($builder, $options);
        
        //any extra properties are added here
        $startYear = new \DateTime("now");
        $startYear = $startYear->sub(date_interval_create_from_date_string('90 years'));
        $yearRange = range($startYear->format('Y'), date('Y'));
        arsort($yearRange);
        $builder->add('dateOfBirth', 'birthday', array(
            'years' => $yearRange,
            'label' => 'form.dob',
            'translation_domain' => 'FOSUserBundle',
            ));
        
        $builder->add('firstname', 'text', array(
            'required'  => false,
            'label'     => 'form.firstname',
            'translation_domain' => 'FOSUserBundle',
        ));
        
        $builder->add('lastname', 'text', array(
            'required'  => false,
            'label'     => 'form.lastname',
            'translation_domain' => 'FOSUserBundle',
        ));
    }

    public function getDefaultOptions(array $options){
        return array(
            'data_class' => 'SkNd\UserBundle\Entity\User',
        );
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SkNd\UserBundle\Entity\User',
        ));
    }
    
    public function getName()
    {
        return 'sk_nd_user_profile';
    }

 
}
