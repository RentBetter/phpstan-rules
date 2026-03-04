<?php

namespace App\Form;

class OptionsResolver
{
    /** @param array<string, mixed> $defaults */
    public function setDefaults(array $defaults): void {}
}

class BadFormType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\Tenancy::class, // ERROR — entity
        ]);
    }
}

class BadNestedEntityFormType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Tenancies\Tenancies\Entity\Tenancy::class, // ERROR — entity in subdomain
        ]);
    }
}

class GoodFormType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Form\TenancyFormData::class, // OK — FormData DTO
        ]);
    }
}

class GoodNoDataClassFormType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true, // OK — no data_class
        ]);
    }
}
