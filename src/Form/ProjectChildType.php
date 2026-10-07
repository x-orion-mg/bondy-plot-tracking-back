<?php

namespace App\Form;

use App\Entity\ProjectChild;
use App\Entity\ProjectParent;
use App\Entity\ProjectStep;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Doctrine\ORM\EntityRepository;

class ProjectChildType extends AbstractType
{
    private const PLACEHOLDER_TEXT = 'Sélectionner';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre <span class="text-danger">*</span> (max 50 caractères)',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 50
                ],
                'label_html' => true
            ])
            ->add('projectParent', EntityType::class, array(
                'label' => 'Projet bondy associé <span class="text-danger">*</span>',
                'class' => ProjectParent::class,
                'query_builder' => function (EntityRepository $_er) {
                    $query_builder = $_er->createQueryBuilder('prj_parent');
                    $query_builder->leftJoin('prj_parent.projectType', 'prj_type');
                    $query_builder->leftJoin('prj_parent.localisation', 'localisation');
                    $query_builder->orderBy('prj_parent.id', 'ASC');

                    return $query_builder;
                },
                'choice_label' => function ($projectParent) {
                    return $projectParent->getLocalisation()->getCountry() . ' - ' . $projectParent->getLocalisation()->getRegion();
                },
                'multiple' => false,
                'expanded' => false,
                'required' => false,
                'placeholder' => self::PLACEHOLDER_TEXT,
                'empty_data' => null,
                'attr' => [
                    'required' => true,
                    'class' => 'form-control select2-example kl-select-parent'
                ],
                'label_html' => true
            ))
            ->add('user', EntityType::class, [
                'label' => 'Partenaire <span class="text-danger">*</span>',
                'class' => User::class,
                'query_builder' => function (EntityRepository $_er) {
                    $query_builder = $_er->createQueryBuilder('user');
                    $query_builder->leftJoin('user.profile', 'profile');
                    $query_builder->where('profile.code = :code');
                    $query_builder->setParameter('code', 'PARTNER');
                    $query_builder->orderBy('user.id', 'DESC');

                    return $query_builder;
                },
                'choice_label' => 'lastName',
                'multiple' => false,
                'expanded' => false,
                'required' => false,
                'placeholder' => self::PLACEHOLDER_TEXT,
                'empty_data' => null,
                'attr' => [
                    'required' => true,
                    'class' => 'form-control select2-example'
                ],
                'label_html' => true
            ])
            ->add('projectStep', EntityType::class, [
                'label' => 'Etape du projet <span class="text-danger">*</span>',
                'class' => ProjectStep::class,
                'query_builder' => function (EntityRepository $_er) {
                    $query_builder = $_er->createQueryBuilder('prj_step');
                    $query_builder->orderBy('prj_step.id', 'ASC');

                    return $query_builder;
                },
                'choice_label' => 'projectStepName',
                'multiple' => false,
                'expanded' => false,
                'required' => false,
                'placeholder' => self::PLACEHOLDER_TEXT,
                'empty_data' => null,
                'attr' => [
                    'required' => true,
                    'class' => 'form-control select2-example'
                ],
                'label_html' => true
            ])
            ->add('history', TextareaType::class, [
                'label' => 'Histoire du projet',
                'required' => false,
                'attr' => [
                    'class' => 'form-control summernote-editor'
                ]
            ])
            ->add('city', TextType::class, array(
                'label' => 'Ville <span class="text-danger">*</span>',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ],
                'label_html' => true
            ))
            ->add('startDate', DateTimeType::class, [
                'label' => 'Date de début <span class="text-danger">*</span>',
                'required' => true,
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'html5' => false,
                'attr' => [
                    'placeholder' => 'Entrer une date',
                    'autocomplete' => 'off',
                    'class' => 'datepick form-control',
                ],
                'label_html' => true
            ])
            ->add('environmentalImpactPlantedTrees', IntegerType::class, array(
                'label' => 'Arbres plantés <span class="text-danger">*</span>',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'label_html' => true
            ))
            ->add('environmentalImpactCO2', IntegerType::class, array(
                'label' => 'TCO2 séquestrés <span class="text-danger">*</span>',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'label_html' => true
            ))
            ->add('economicImpactSustainableEmployment', IntegerType::class, array(
                'label' => 'Emplois durable <span class="text-danger">*</span>',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'label_html' => true
            ))
            ->add('economicImpactRestoredHectares', NumberType::class, array(
                'label' => 'Hectares restaurés <span class="text-danger">*</span>',
                'scale' => 2, // nombre de décimales
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'label_html' => true
            ))
            ->add('socialImpactSeasonalJobs', IntegerType::class, array(
                'label' => 'Emplois saisonniers <span class="text-danger">*</span>',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'label_html' => true
            ))
            ->add('socialImpactIndirectBeneficiaries', IntegerType::class, array(
                'label' => 'Bénéficiaire indirect <span class="text-danger">*</span>',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'label_html' => true
            ))
            ->add('latitude', NumberType::class, array(
                'label' => "Latitude <span class='text-danger'>*</span>",
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ],
                'label_html' => true
            ))
            ->add('longitude', NumberType::class, array(
                'label' => "Longitude <span class='text-danger'>*</span>",
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ],
                'label_html' => true
            ))
            ->add('socialImpactPartnerFarmer', IntegerType::class, array(
                'label' => "Paysan(s) partenaire(s)",
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'label_html' => false
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectChild::class,
        ]);
    }
}
