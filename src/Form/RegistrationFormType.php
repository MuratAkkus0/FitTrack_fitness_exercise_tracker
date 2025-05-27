<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'attr' => [
                    'class' => 'pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500'
                ],
                'label' => false,
            ])
            ->add('surname', null, [
                'attr' => [
                    'class' => 'pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500'
                ],
                'label' => false,
            ])
            ->add('email', null, [
                'attr' => [
                    'class' => 'pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500'
                ],
                'label' => false,
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Kullanım şartlarını kabul etmelisiniz.',
                    ]),
                ],
                'label' => false,
                'attr' => ['class' => 'h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded']
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Şifreler eşleşmiyor.',
                'first_options' => [
                    'attr' => [
                        'class' => 'pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500',
                        'autocomplete' => 'new-password',
                    ],
                    'label' => false,
                ],
                'second_options' => [
                    'attr' => [
                        'class' => 'pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500',
                        'autocomplete' => 'new-password',
                    ],
                    'label' => false,
                ],
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,

                'constraints' => [
                    new NotBlank([
                        'message' => 'Lütfen bir şifre giriniz',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Şifreniz en az {{ limit }} karakter olmalıdır',
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
            'data_class' => Users::class,
        ]);
    }
}
