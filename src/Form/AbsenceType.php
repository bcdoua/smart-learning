<?php

namespace App\Form;

use App\Entity\Absence;
use App\Entity\Course;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbsenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('student', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name',
                'query_builder' => fn ($repo) => $repo->createQueryBuilder('u')->where('u.roles LIKE :role')->setParameter('role', '%ROLE_STUDENT%'),
            ])
            ->add('course', EntityType::class, ['class' => Course::class, 'choice_label' => 'title'])
            ->add('dateAbsence', DateType::class, ['widget' => 'single_text'])
            ->add('reason', TextareaType::class, ['required' => false])
            ->add('status', ChoiceType::class, ['choices' => ['Pending' => 'pending', 'Justified' => 'justified', 'Unjustified' => 'unjustified']]);
    }

    public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['data_class' => Absence::class]); }
}
