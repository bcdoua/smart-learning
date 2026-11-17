<?php

namespace App\Form;

use App\Entity\Exam;
use App\Entity\Result;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('student', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name',
                'query_builder' => fn ($repo) => $repo->createQueryBuilder('u')->where('u.roles LIKE :role')->setParameter('role', '%ROLE_STUDENT%'),
            ])
            ->add('exam', EntityType::class, ['class' => Exam::class, 'choice_label' => 'title'])
            ->add('score', NumberType::class)
            ->add('status', ChoiceType::class, ['choices' => ['Pending' => 'pending', 'Passed' => 'passed', 'Failed' => 'failed']]);
    }

    public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['data_class' => Result::class]); }
}
