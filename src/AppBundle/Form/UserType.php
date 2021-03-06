<?php
// src/AppBundle/Form/UserType.php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvents;

class UserType extends AbstractType
{   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class, array(
                'label' => 'College Email Prefix'
            ))
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Confirm Password'),
            ))
            ->add('college', EntityType::class, array(
                'class' => 'AppBundle:College',
                'choice_label' => 'name',
                'expanded' => false,
                'multiple' => false
            ))
            ->add('suffix', HiddenType::class, array(
                'attr' => array('value' => '@school.edu'),
                'mapped' => false,
            ));
            
        $builder
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (\Symfony\Component\Form\FormEvent $event) {
                    $data = $event->getData();
                    $email = $data["email"] . $data['suffix'];
                    $data["email"] = $email;
                    $event->setData($data);
                });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User',
        ));
    }
}