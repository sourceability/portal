<?php

declare(strict_types=1);

namespace Sourceability\Portal\Bundle\DependencyInjection\Attribute;

use Attribute;
use Sourceability\Portal\Bundle\DependencyInjection\SourceabilityPortalExtension;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[Attribute(Attribute::TARGET_CLASS)]
class AutoconfigureSpell extends AutoconfigureTag
{
    public function __construct(string $shortName)
    {
        parent::__construct(
            SourceabilityPortalExtension::TAG_SPELL,
            [
                SourceabilityPortalExtension::TAG_SPELL_SHORT_NAME => $shortName,
            ]
        );
    }
}
