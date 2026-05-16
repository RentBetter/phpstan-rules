<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RedundantJoinColumns
{
    // ERROR — every arg matches a default → entire attribute redundant
    #[ORM\JoinColumn(name: 'type_id', referencedColumnName: 'id')]
    private $type;

    // ERROR — entirely redundant via empty args
    #[ORM\JoinColumn]
    private $accessLevel;

    // ERROR — camelCase: adminRole → admin_role_id (only `name` is redundant; nullable: false is not)
    #[ORM\JoinColumn(name: 'admin_role_id', nullable: false)]
    private $adminRole;

    // ERROR — number_aware: user2faToken → user_2fa_token_id
    #[ORM\JoinColumn(name: 'user_2fa_token_id', nullable: false)]
    private $user2faToken;

    // ERROR — nullable: true is the default
    #[ORM\JoinColumn(name: 'custom_fk_id', nullable: true)]
    private $withRedundantNullable;

    // ERROR — unique: false is the default
    #[ORM\JoinColumn(name: 'custom_fk_id', unique: false, nullable: false)]
    private $withRedundantUnique;

    // ERROR — options: [] is treated the same as null (default)
    #[ORM\JoinColumn(name: 'custom_fk_id', options: [], nullable: false)]
    private $withEmptyOptions;

    // ERROR — onDelete: null and columnDefinition: null are defaults
    #[ORM\JoinColumn(name: 'custom_fk_id', onDelete: null, columnDefinition: null, nullable: false)]
    private $withNullDefaults;

    // ERROR — referencedColumnName: 'id' is the default (other args non-default)
    #[ORM\JoinColumn(name: 'parent_uuid', referencedColumnName: 'id', nullable: false)]
    private $withRedundantReferencedColumn;

    // OK — name diverges from derived default
    #[ORM\JoinColumn(name: 'parent_type_id', nullable: false)]
    private $parent;

    // OK — referencedColumnName is non-default
    #[ORM\JoinColumn(name: 'type_uuid', referencedColumnName: 'uuid', nullable: false)]
    private $byUuid;

    // OK — nullable: false is non-default
    #[ORM\JoinColumn(nullable: false)]
    private $required;

    // OK — onDelete is set to a real value
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private $cascading;

    // OK — unique: true is non-default
    #[ORM\JoinColumn(unique: true)]
    private $uniqueFk;

    // OK — options has content
    #[ORM\JoinColumn(options: ['default' => 0])]
    private $withOptions;

    // OK — no JoinColumn at all
    private $implicit;
}
