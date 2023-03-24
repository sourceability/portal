<?php

declare(strict_types=1);

namespace Sourceability\Portal\Completer;

interface Completer
{
    public function complete(string $prompt): string;
}
