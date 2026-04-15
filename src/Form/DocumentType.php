<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Contact;
use App\Entity\Document;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom ou type',
                'required' => false,
            ])
            ->add('uploadedFile', FileType::class, [
                'label' => 'Fichier',
                'mapped' => false,
                'required' => !$options['is_edit'],
                'constraints' => [
                    new File(
                        maxSize: '8M',
                        mimeTypes: [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ],
                        mimeTypesMessage: 'Formats autorises: PDF, JPG, PNG, DOC, DOCX.'
                    ),
                ],
            ])
            ->add('accounts', EntityType::class, [
                'class' => Account::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
                'by_reference' => false,
            ])
            ->add('contacts', EntityType::class, [
                'class' => Contact::class,
                'choice_label' => static fn(Contact $contact): string => (string) $contact,
                'multiple' => true,
                'required' => false,
                'by_reference' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
            'is_edit' => false,
        ]);
    }
}
