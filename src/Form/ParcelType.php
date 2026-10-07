<?php

namespace App\Form;

use App\Entity\Parcel;
use App\Entity\ProjectChild;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class ParcelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentYear = (int) date('Y');
        $years = range($currentYear, 2000); 

        $builder
            ->add('title', TextType::class, [
                'label'    => "Nom de la parcelle <span class='text-danger'>*</span>",
                'required' => true,
                'attr'     => [
                    'class' => 'form-control'
                ],
                'label_html' => true
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => "Date de début <span class='text-danger'>*</span>",
                'required' => true,
                'widget'   => 'single_text',
                'format'   => 'dd/MM/yyyy',
                'html5'    => false,
                'attr'     => [
                    'placeholder'  => 'Entrer une date',
                    'autocomplete' => 'off',
                    'class'        => 'datepick form-control',
                ],
                'label_html' => true
            ])
            ->add('plantingYear', ChoiceType::class, [
                'label'    => "Année de plantation <span class='text-danger'>*</span>",
                'required' => true,
                'choices'  => array_combine($years, $years),
                'attr'     => [
                    'class' => 'form-control'
                ],
                'label_html' => true
            ])
            ->add('city', TextType::class, array(
                'label'    => "Ville",
                'required' => true,
                'attr'     => [
                    'class' => 'form-control'
                ]
            ))
            ->add('latitude', NumberType::class, array(
                'label'    => "Latitude <span class='text-danger'>*</span>",
                'required' => true,
                'attr'     => [
                    'class' => 'form-control'
                ],
                'label_html' => true
            ))
            ->add('longitude', NumberType::class, array(
                'label'    => "Longitude <span class='text-danger'>*</span>",
                'required' => true,
                'attr'     => [
                    'class' => 'form-control'
                ],
                'label_html' => true
            ))
        ;

        $builder->get('plantingYear')
            ->addModelTransformer(new CallbackTransformer(
                function ($date) {
                    return $date ? $date->format('Y') : null;
                },
                function ($year) {
                    return $year ? new \DateTime("$year-01-01") : null;
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parcel::class,
        ]);
    }
}
