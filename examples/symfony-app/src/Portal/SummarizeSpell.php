<?php

declare(strict_types=1);

namespace App\Portal;

use Sourceability\Portal\Bundle\DependencyInjection\Attribute\AutoconfigureSpell;
use Sourceability\Portal\Spell\ApiPlatformSpell;

/**
 * @extends ApiPlatformSpell<string, Summary>
 */
#[AutoconfigureSpell('Summarize')]
class SummarizeSpell extends ApiPlatformSpell
{
    public function getPrompt($input): string
    {
        return sprintf('Summarize %s', json_encode($input));
    }

    protected function getClass(): string
    {
        return Summary::class;
    }
}
