<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Range;
use Doctrine\ORM\EntityRepository;
use App\Entity\Lot;
use App\Entity\Category;

class NewLotFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')->orderBy('c.name', 'ASC');
                },
                'choice_label' => 'name',
                'placeholder' => 'Выберите категорию'
            ])
            ->add('description', TextareaType::class)
            ->add('image', FileType::class, [
                'mapped' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '3072k'
                    ])
                ]
            ])
            ->add('startPrice', IntegerType::class, [
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'minMessage' => 'Начальная цена не может быть меньше {{ limit }}',
                        'max' => 10000000,
                        'maxMessage' => 'Начальная цена не может быть больше {{ limit }}',
                    ])
                ]
            ])
            ->add('step', IntegerType::class, [
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'minMessage' => 'Шаг ставки не может быть меньше {{ limit }}',
                        'max' => 100000,
                        'maxMessage' => 'Шаг ставки не может быть больше {{ limit }}',
                    ])
                ]
            ])
            ->add('endDate', DateTimeType::class, [
                'date_widget' => 'single_text',
                'html5' => false,
                'date_format' => 'dd.MM.yyyy',
                'constraints' => [
                    new Range([
                        'min' => 'now +23 hours',
                        'minMessage' => 'Дата завершения не может быть меньше {{ limit }}',
                        'max' => 'now +30 days',
                        'maxMessage' => 'Дата завершения не может быть больше {{ limit }}',
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lot::class
        ]);
    }
}
