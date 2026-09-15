<?php

namespace App\Entity {

    use Doctrine\ORM\Mapping as ORM;

    #[ORM\Entity]
    class Tenancy {}

    // A value object in the Entity namespace: embeddable, not a managed row.
    #[ORM\Embeddable]
    class AddressModel {}

    // A plain model in the Entity namespace with no mapping at all.
    class MoneyModel {}
}

namespace App\Tenancies\Tenancies\Entity {

    use Doctrine\ORM\Mapping as ORM;

    #[ORM\Entity(repositoryClass: 'App\Tenancies\Tenancies\Repository\TenancyRepository')]
    class Tenancy {}
}

namespace App\Form {

    class TenancyFormData {}

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
                'data_class' => TenancyFormData::class, // OK — FormData DTO
            ]);
        }
    }

    class GoodEmbeddableFormType
    {
        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => \App\Entity\AddressModel::class, // OK — embeddable value object
            ]);
        }
    }

    class GoodModelFormType
    {
        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => \App\Entity\MoneyModel::class, // OK — unmapped model in an Entity namespace
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
}
