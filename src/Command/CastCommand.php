<?php

declare(strict_types=1);

namespace Sourceability\Portal\Command;

use JsonException;
use JsonSerializable;
use Psr\Container\ContainerInterface;
use Sourceability\Portal\Exception\Exception;
use Sourceability\Portal\Portal;
use Sourceability\Portal\Spell\Spell;
use Sourceability\Portal\Spell\StaticSpell;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

#[AsCommand('portal:cast')]
class CastCommand extends Command
{
    public function __construct(
        private readonly Portal $portal,
        private readonly ?ContainerInterface $spellLocator = null
    ) {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('spell', InputArgument::REQUIRED, 'Class name of the spell to cast, or the path to a yaml spell.')
            ->addArgument('input', InputArgument::OPTIONAL, 'Input as a string or JSON. Omit to go through all the spell examples.')
        ;
    }

    protected function execute(InputInterface $consoleInput, OutputInterface $consoleOutput): int
    {
        $spell = null;
        $io = new SymfonyStyle($consoleInput, $consoleOutput);

        $spellName = $consoleInput->getArgument('spell');
        $input = $consoleInput->getArgument('input');

        if (is_string($spellName)
            && file_exists($spellName)
        ) {
            try {
                $parsedSpell = Yaml::parseFile($spellName);
                if (! is_array($parsedSpell)
                    || ! array_key_exists('schema', $parsedSpell)
                    || ! array_key_exists('prompt', $parsedSpell)
                ) {
                    throw new Exception(
                        'The YAML file needs to define at least the schema and cast keys.'
                    );
                }

                $spell = new StaticSpell(
                    $parsedSpell['schema'],
                    $parsedSpell['prompt'],
                    $parsedSpell['examples'] ?? [],
                );
            } catch (ParseException) {
                // ignore
            }
        }

        if ($spell === null) {
            if (is_string($spellName)
                && $this->spellLocator !== null
                && $this->spellLocator->has($spellName)
            ) {
                $spell = $this->spellLocator->get($spellName);
            } elseif (is_string($spellName) && class_exists($spellName)) {
                $spell = new $spellName();
            }

            if (! $spell instanceof Spell) {
                $io->error(
                    sprintf('Spell must be a class that implements %s.', Spell::class)
                );

                return 1;
            }

            assert($spell instanceof Spell);
        }

        $schema = $spell->getSchema();
        if ($schema instanceof JsonSerializable || is_array($schema)) {
            $schema = json_encode($schema, JSON_THROW_ON_ERROR);
        }
        $schemaArray = json_decode((string) $schema, true, 512, JSON_THROW_ON_ERROR);
        assert(is_array($schemaArray));

        if ($input !== null) {
            assert(is_scalar($input));

            try {
                $input = json_decode((string) $input, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                // ignore
            }

            $inputKeys = is_array($input) ? array_keys($input) : [];
            $inputIsArray = is_array($input) && count($inputKeys) > 0 && count($inputKeys) === count(array_filter($inputKeys, 'is_int'));

            $inputs = $inputIsArray ? $input : [$input];
        } else {
            $inputs = $spell->getExamples();

            if (count($inputs) < 1) {
                $io->error(
                    'The spell defines no examples. To cast a spell from the CLI, either defines examples or provide the input argument.'
                );

                return 1;
            }
        }

        $firstInput = true;
        foreach ($inputs as $input) {
            if (! $firstInput) {
                $io->writeln(['', '', '']);
            }
            $firstInput = false;

            $io->title('Input:');
            $inputView = is_string($input) ? $input : json_encode($input, \JSON_PRETTY_PRINT & JSON_THROW_ON_ERROR);
            assert(is_string($inputView));
            $io->block($inputView, null, 'bg=gray', ' ', true);

            $castResult = $this->portal->cast($spell, $input);

            if ($consoleOutput->isVerbose()) {
                $io->title('Prompt:');
                $io->block($castResult->getPrompt(), null, 'bg=gray', ' ', true);
            }

            if ($consoleOutput->isVerbose()) {
                $io->title('Completion:');
                $io->block($castResult->getCompletion(), null, 'bg=gray', ' ', true);
            }

            $io->title('Completion Results:');

            if ((is_countable($castResult->getTransferValue()) ? count($castResult->getTransferValue()) : 0) > 0) {
                if (is_array($castResult->getTransferValue())
                    && is_int(array_keys($castResult->getTransferValue())[0])
                ) {
                    $table = new Table($consoleOutput);
                    $table->setStyle('box');

                    $schemaProperties = $schemaArray['properties'] ?? $schemaArray['items']['properties'] ?? null;
                    if (is_array($schemaProperties)) {
                        $table->setHeaders(array_keys($schemaProperties));
                    }
                    $table->setFooterTitle(sprintf('Total: %d', count($castResult->getTransferValue())));

                    $firstResult = true;
                    foreach ($castResult->getTransferValue() as $value) {
                        assert(is_array($value));

                        if (! $firstResult) {
                            $table->addRow(new TableSeparator());
                        }
                        $firstResult = false;

                        $table->addRow(
                            array_values(
                                array_map(
                                    function ($value) {
                                        if (is_scalar($value)) {
                                            return $value;
                                        }

                                        return json_encode($value, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE);
                                    },
                                    $value
                                )
                            )
                        );
                    }
                    $table->render();
                } else {
                    $value = $castResult->getTransferValue();
                    $io->block(json_encode($value, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE) ?: '', null, 'bg=gray', ' ', true);
                }
            } else {
                $value = $castResult->getTransferValue();
                $io->block(json_encode($value, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE) ?: '', null, 'bg=gray', ' ', true);
            }

            if ($consoleOutput->isVerbose()) {
                $io->info(join(' - ', [
                    'Total: ' . $castResult->getCost()->getTotal()->formatTo('en_US'),
                    'Prompt: ' . $castResult->getCost()->getPrompt()->formatTo('en_US'),
                    'Completion: ' . $castResult->getCost()->getCompletion()->formatTo('en_US'),
                ]));
            }

            if ($consoleOutput->isVeryVerbose()) {
                $io->title('Results:');
                $io->block(var_export($castResult->getValue(), true), null, 'bg=gray', ' ', true);
            }
        }

        return 0;
    }
}
