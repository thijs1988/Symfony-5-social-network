<?php

namespace App\Form;

use App\Entity\UserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image', FileType::class, [
                'mapped' => false
            ])
            ->add('hobby', TextType::class, ['label'=>'Hobby\'s'])
            ->add('sport', TextType::class)
            ->add('address', TextType::class)
            ->add('city', TextType::class)
            ->add('zip', TextType::class)
            ->add('movies', TextType::class)
            ->add('description', TextType::class)
            ->add('Submit', SubmitType::class, [
                'attr' => [ 'style' => 'float: left']
                ])
            ->add('Cancel', ButtonType::class, [
                'attr' => [ 'onClick' => 'window.history.back();', 'style' => 'margin-left: 5px']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserProfile::class,
        ]);
    }
}
