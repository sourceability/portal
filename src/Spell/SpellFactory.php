<?php

declare(strict_types=1);

namespace Sourceability\Portal\Spell;

/**
 * @template TInput
 * @template TOutput of object
 */
interface SpellFactory
{
    /**
     * @return Spell<TInput, TOutput>
     */
    public static function create(): Spell;
}
