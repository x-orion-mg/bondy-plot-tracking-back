<?php
namespace App\Form;

use App\Entity\SubProject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class SubProjectType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('entityType', HiddenType::class)
			->add('entityId', HiddenType::class)
			->add('name', TextType::class, [
				'constraints' => [
					new NotBlank(),
					new Length(['max' => 20]),
				],
			])
			->add('banner', FileType::class, [
				'constraints' => [
					new File([
						'maxSize' => '5M',
						'mimeTypes' => [
							'image/jpeg',
							'image/png',
						],
						'mimeTypesMessage' => 'Please upload a valid image file (JPEG or PNG)',
					]),
				],
			])
			->add('startDate', HiddenType::class)
			->add('city', TextType::class, [
				'constraints' => [
					new NotBlank(),
				],
			]);

	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => SubProject::class,
		]);
	}
}
