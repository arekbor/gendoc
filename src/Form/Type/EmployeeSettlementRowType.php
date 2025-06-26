<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Model\EmployeeSettlementRow;
use App\Service\EmployeeSettlementService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;

final class EmployeeSettlementRowType extends AbstractType
{
    public function __construct(
        private readonly EmployeeSettlementService $employeeSettlementService
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('day', ChoiceType::class, [
                'label' => false,
                'choices' => array_merge([0], $this->employeeSettlementService->getDays($options['month'], $options['year'])),
                'choice_label' => function ($value) use ($options) {
                    return $value === 0 ? 'Wybierz dzień' :
                        $value . ' ' . $this->employeeSettlementService->getDayName($value, $options['month'], $options['year']);
                },
                'attr' => [
                    'data-controller' => 'form-collection',
                    'data-action' => 'change->form-collection#onDayChange',
                ],
                'constraints' => [
                    new GreaterThan(value: 0, message: 'Dzień nie został wybrany.')
                ]
            ])
            ->add('startTime', TimeType::class, [
                'label' => false,
                'constraints' => [
                    new LessThan([
                        'propertyPath' => 'parent.all[endTime].data',
                        'message' => 'Start powinień być mniejszy od zakończenia.'
                    ])
                ]
            ])
            ->add('endTime', TimeType::class, [
                'label' => false,
                'constraints' => [
                    new GreaterThan([
                        'propertyPath' => 'parent.all[startTime].data',
                        'message' => 'Zakończenie powinno być większe od startu.'
                    ])
                ]
            ])
            ->add('place', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('activities', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('comment', TextType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('remove', ButtonType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'btn-close btn-close-red',
                    'data-action' => 'form-collection#removeCollectionElement'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EmployeeSettlementRow::class,
        ]);

        $resolver->setRequired('month');
        $resolver->setRequired('year');

        $resolver->setAllowedTypes('month', 'int');
        $resolver->setAllowedTypes('year', 'int');
    }
}
