<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->add('level', ChoiceType::class, ['choices' => ['Beginner' => 'Beginner', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced']])
            ->add('category', TextType::class, ['required' => false])
            ->add('teacher', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name',
                'query_builder' => fn ($repo) => $repo->createQueryBuilder('u')->where('u.roles LIKE :role')->setParameter('role', '%ROLE_TEACHER%'),
            ])
            ->add('imageFile', FileType::class, ['mapped' => false, 'required' => false, 'constraints' => [new File(maxSize: '3M')]])
            ->add('pdfFile', FileType::class, ['mapped' => false, 'required' => false, 'constraints' => [new File(maxSize: '20M')]])
            ->add('videoFile', FileType::class, ['mapped' => false, 'required' => false, 'constraints' => [new File(maxSize: '200M')]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Course::class]);
    }
}
