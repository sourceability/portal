<?php

namespace Sourceability\Portal\Tests\Bundle;

use PHPUnit\Framework\TestCase;
use Sourceability\Portal\Tests\Bundle\Spells\CodeReviewSpell;
use Sourceability\Portal\Tests\Bundle\Spells\JokeSpell;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class FunctionalTest extends TestCase
{
    private function getCommandTester(string $name = 'portal:cast'): CommandTester
    {
        $kernel = new TestKernel('test', true);
        $application = new Application($kernel);

        $command = $application->find($name);

        return new CommandTester($command);
    }

    public function testCsvHeaders()
    {
        $commandTester = $this->getCommandTester();

        $commandTester->execute([
            'spell' => __DIR__ . '/../../examples/csv_headers.yaml',
        ]);

        $commandTester->assertCommandIsSuccessful();

        $output = preg_replace('# +$#m', '\1', $commandTester->getDisplay());

        self::assertStringContainsString(
            <<<'OUTPUT'
Input:
======

 {"supportedHeaders":["MPN","Manufacturer","Price","Quantity"],"headers":["PN","Prix","Fabricant","Risques","Notes"]}

Completion Results:
===================

 {
     "mappedHeaders": {
         "PN": "MPN",
         "Prix": "Price",
         "Fabricant": "Manufacturer",
         "Risques": null,
         "Notes": null
     }
 }
OUTPUT
,
            $output
        );
        self::assertStringContainsString(
            <<<'OUTPUT'
Input:
======

 {"supportedHeaders":["name","content","categories"],"headers":["title","description"]}

Completion Results:
===================

 {
     "mappedHeaders": {
         "title": "name",
         "description": "content",
         "categories": null
     }
 }
OUTPUT
,
            $output
        );
    }

    public function testCodeReview()
    {
        $commandTester = $this->getCommandTester();

        $commandTester->execute([
            'spell' => 'CodeReview',
        ]);

        $commandTester->assertCommandIsSuccessful();

        $output = preg_replace('# +$#m', '\1', $commandTester->getDisplay());

        self::assertStringContainsString(
            <<<'OUTPUT'
┌────────────────────────────────────────────────────────────────────┬────────────────────────────────────────────────────────────────────────────────────────────────────────┬───────┐
│ context                                                            │ comment                                                                                                │ emoji │
├────────────────────────────────────────────────────────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┼───────┤
│ {                                                                  │ I see you're setting refresh triggers to false. I guess you could say they're... not so fresh anymore. │ 🤯    │
│     "path": "app\/Http\/Controllers\/Rule\/SelectController.php",  │                                                                                                        │       │
│     "lineNumber": 167                                              │                                                                                                        │       │
│ }                                                                  │                                                                                                        │       │
├────────────────────────────────────────────────────────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┼───────┤
│ commit_message                                                     │ This patch is rule-tastic! 🎉                                                                          │ 🎉    │
├────────────────────────────────────────────────────────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┼───────┤
│ {                                                                  │ I'm always refreshed when I see a new trigger. But I guess you're not. 😞                              │ 🤔    │
│     "path": "app\/TransactionRules\/Engine\/SearchRuleEngine.php", │                                                                                                        │       │
│     "lineNumber": 57                                               │                                                                                                        │       │
│ }                                                                  │                                                                                                        │       │
├────────────────────────────────────────────────────────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┼───────┤
│ {                                                                  │ I guess you could say that you're... trigger shy. 😉                                                   │ 👍    │
│     "path": "app\/TransactionRules\/Engine\/SearchRuleEngine.php", │                                                                                                        │       │
│     "lineNumber": 61                                               │                                                                                                        │       │
│ }                                                                  │                                                                                                        │       │
├────────────────────────────────────────────────────────────────────┼────────────────────────────────────────────────────────────────────────────────────────────────────────┼───────┤
│ {                                                                  │ I'm sensing some tension between you and the triggers. Maybe you need some trigger therapy? 💯         │ 💯    │
│     "path": "app\/TransactionRules\/Engine\/SearchRuleEngine.php", │                                                                                                        │       │
│     "lineNumber": 68                                               │                                                                                                        │       │
│ }                                                                  │                                                                                                        │       │
└────────────────────────────────────────────────────────────────────┴──────────────── Total: 5 ──────────────────────────────────────────────────────────────────────────────┴───────┘
OUTPUT
            ,
            $output
        );
    }

    public function testJoke()
    {
        $commandTester = $this->getCommandTester();

        $commandTester->execute([
            'spell' => JokeSpell::class,
            'input' => json_encode('symfony php framework'),
        ]);

        $commandTester->assertCommandIsSuccessful();

        $output = preg_replace('# +$#m', '\1', $commandTester->getDisplay());

        self::assertStringContainsString(
            <<<'OUTPUT'
Input:
======

 symfony php framework

Completion Results:
===================

 "I told my boss I could build a website with Symfony. He said 'What's that, a new type of pasta?'"
OUTPUT
            ,
            $output
        );
    }

    public function testCustomJokeCommand()
    {
        $commandTester = $this->getCommandTester('custom-joke');

        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();

        self::assertStringContainsString(
            <<<'OUTPUT'
Why did the capacitor break up with the diode? Because it couldn't resist its forward bias!
OUTPUT
            ,
            $commandTester->getDisplay()
        );
    }
}
