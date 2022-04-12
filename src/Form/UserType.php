<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
		public function buildForm(FormBuilderInterface $builder, array $options): void {
		$builder
			//->add('username')
			//->add('roles')
			//->add('password')
			//->add('isVerified')
			->add('identification_number', TextType::class, [
				'constraints' => [
					new NotBlank(),
					new Length(['min' => 1]),
				],
				'label' => false,
				'attr' => [
					'placeholder' => 'Identification number',
					'class' => "form-control paddin-button-7",
				],
			])
			->add('name', TextType::class, [
				'constraints' => [
					new NotBlank(),
					new Length(['min' => 1]),
				],
				'label' => false,
				'attr' => [
					'placeholder' => 'Name',
					'class' => "form-control paddin-button-7",
				],
			])
			->add('last_name', TextType::class, [
				'constraints' => [
					new NotBlank(),
					new Length(['min' => 1]),
				],
				'label' => false,
				'attr' => [
					'placeholder' => 'Last name',
					'class' => "form-control paddin-button-7",
				],
			])
			->add('gender', ChoiceType::class, [
				'constraints' => [
					new NotBlank(),
					new Length(['min' => 1]),
				],
				'label' => false,
				'choices' => ["Male" => "Male", "Female" => "Female"],
				'attr' => [
					'placeholder' => 'Gender',
					'class' => "form-control paddin-button-7",
				],
			])
			//->add('type')
			//->add('code')
			//->add('account')
		;
	}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
