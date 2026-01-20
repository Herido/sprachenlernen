<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;

class DeleteAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'Passwort zur Bestätigung',
                'mapped' => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('gdpr', CheckboxType::class, [
                'label' => 'Ich verstehe, dass mein Account und meine Daten dauerhaft gelöscht werden.',
                'mapped' => false,
                'constraints' => [new IsTrue(['message' => 'Bitte bestätige die Löschung.'])],
            ]);
    }
}
