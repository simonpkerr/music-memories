<?php
/*
 * Original code Copyright (c) 2011 Simon Kerr
 * @author Simon Kerr
 * @version 1.0
 * RegistrationFormType extends the FOSUserBundle registration form, by adding
 * first, last name and date of birth
 */
namespace SkNd\UserBundle\Form\Type;
    
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType {
    
    public function buildForm(FormBuilderInterface $builder, array $options){
        parent::buildForm($builder, $options);
        
        //any extra properties are added here
        $startYear = new \DateTime("now");
        $startYear = $startYear->sub(date_interval_create_from_date_string('90 years'));
        $yearRange = range($startYear->format('Y'), date('Y'));
        arsort($yearRange);
        $builder->add('dateofbirth', 'birthday', array(
            'years' => $yearRange,
            'format'=> 'MMM-dd-yyyy',
            'label'     => 'form.dob',
            'translation_domain' => 'FOSUserBundle',
            
            ));
        
        $builder->add('firstname', 'text', array(
            'required'  => false,
            'label'     => 'form.firstname',
            'translation_domain' => 'FOSUserBundle',
        ));
        $builder->add('surname', 'text', array(
            'required'  => false,
            'label'     => 'form.surname',
            'translation_domain' => 'FOSUserBundle',
        ));
        
        
    }
    
    public function getName(){
        return 'sk_nd_user_registration';
    }
    
}


?>
