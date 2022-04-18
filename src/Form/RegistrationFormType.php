<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
					->add('username', TextType::class,[
						'label' => false,
						'required' => false,
						'attr' => [
							'id'=>'',

							'placeholder' => 'Username1',
							'class'=>"form-control paddin-button-7 username_hiden",
						],
					])
            ->add('identification_number', TextType::class, [
							'constraints' => [
								new NotBlank(),
								new Length(['min' => 1]),
							],
							//'autocomplete'=>"off",
							'label' => false,
							'attr' => [
								'placeholder' => 'register.dni',
								'class'=>"form-control paddin-button-7",
							],
						])
            ->add('code', TextType::class, [
							'constraints' => [
								new NotBlank(),
								new Length(['min' => 1]),
							],
							'label' => false,
							'attr' => [
								'placeholder' => 'register.code',
								'class'=>"form-control paddin-button-7",
							],
						])

					//->add('username')
					/*->add('username', EntityType::class, [
						'class' => User::class,
						'query_builder' => function (EntityRepository $er) {
							return $er->createQueryBuilder('User','u')
								//->from('User', 'u')
								->orderBy('u.username', 'ASC');
						},
						'choice_label' => 'username',
						])*/

            /*->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])*/
						->add('plainPassword', PasswordType::class, [
							// instead of being set onto the object directly,
							// this is read and encoded in the controller
							'label' => false,
							'mapped' => false,
							'attr' => [
								'autocomplete' => 'new-password',
								'placeholder' => 'register.password',
								'class' => "form-control paddin-button-7",
							],
							'constraints' => [
								new NotBlank([
									'message' => 'Please enter a password',
								]),
								new Length([
									'min' => 6,
									'minMessage' => 'Your password should be at least {{ limit }} characters',
									// max length allowed by Symfony for security reasons
									'max' => 4096,
								]),
							],
						])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
