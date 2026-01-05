<?php

declare(strict_types=1);

namespace App\Form;

use App\Domain\Enum\CustomFieldType as FieldTypeEnum;
use App\Entity\CustomField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма для настройки кастомного поля.
 */
final class CustomFieldType extends AbstractType
{
    /**
     * Рисуем поля формы.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Тип поля',
                'choices' => array_combine(
                    array_map(fn($e) => $e->label(), FieldTypeEnum::cases()),
                    FieldTypeEnum::cases()
                ),
                'choice_value' => fn(?FieldTypeEnum $e) => $e?->value,
                'choice_label' => fn(FieldTypeEnum $e) => $e->label(),
            ])
            ->add('isRequired', CheckboxType::class, [
                'label' => 'Обязательное для заполнения',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomField::class,
        ]);
    }
}
