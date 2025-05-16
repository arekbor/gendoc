<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Model\EmployeeSettlement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Valid;

final class EmployeeSettlementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('addDay', ButtonType::class, [
                'label' => 'Dodaj dzień',
                'attr' => [
                    'class' => 'btn btn-success btn-sm mt-3',
                    'data-action' => 'form-collection#addCollectionElement'
                ]
            ])
            ->add('employeeSettlementRows', CollectionType::class, [
                'label' => false,
                'entry_type' => EmployeeSettlementRowType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'entry_options' => [
                    'month' => $options['month'],
                    'year' => $options['year'],
                ],
                'constraints' => [
                    new Valid(),
                    new Count(min: 1, minMessage: 'Wprowadź minimum jeden dzień.')
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Pobierz formatkę'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EmployeeSettlement::class,
        ]);

        $resolver->setRequired('month');
        $resolver->setRequired('year');

        $resolver->setAllowedTypes('month', 'int');
        $resolver->setAllowedTypes('year', 'int');
    }
}
