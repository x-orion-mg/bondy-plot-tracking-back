<?php

namespace App\Form;

use App\Entity\TreeSpecies;
use App\Entity\UsageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TreeSpeciesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('commonName', TextType::class, [
                'label'    => 'Nom vernaculaire <span class="text-danger">*</span>',
                'required' => true,
                'attr'     => [
                    'class' => 'form-control'
                ],
                'label_html' => true
            ])
            ->add('scientificName', TextType::class, [
                'label'    => 'Nom scientifique <span class="text-danger">*</span>',
                'required' => true,
                'attr'     => [
                    'class' => 'form-control'
                ],
                'label_html' => true
            ])
            ->add('usageType', EntityType::class, [
                'class'        => UsageType::class,
                'choice_label' => 'label',
                'multiple'     => false,
                'expanded'     => false,
                'required'     => false,
                'placeholder'  => 'Sélectionner',
                'empty_data'   => null,
                'attr'         => [
                    'required' => true,
                    'class'    => 'form-control select2-example'
                ],
                'label' => 'Type d\'utilisation <span class="text-danger">*</span>',
                'label_html' => true
            ])
            ->add('color', ColorType::class, [
	            'label'    => 'Couleur <span class="text-danger">*</span>',
	            'required' => true,
	            'attr'     => [
		            'class' => 'form-control',
	            ],
	            'label_html' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TreeSpecies::class,
        ]);
    }
}
