<?php

namespace Sourceability\Portal\Tests\Bundle\SampleApp\Command;

use Sourceability\Portal\Portal;
use Sourceability\Portal\Tests\Bundle\SampleApp\Spells\JokeSpell;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('custom-joke')]
class CustomJokeCommand extends Command
{
    public function __construct(private readonly Portal $portal, private readonly JokeSpell $jokeSpell)
    {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $castResult = $this->portal->cast(
            $this->jokeSpell,
            'electronic components'
        );

        $output->writeln($castResult->getValue());

        return 0;
    }
}
