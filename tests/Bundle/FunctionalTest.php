<?php

namespace Sourceability\Portal\Tests\Bundle;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class FunctionalTest extends TestCase
{
    public function test()
    {
        $kernel = new TestKernel('test', true);
        $application = new Application($kernel);

        $command = $application->find('portal:cast');
        $commandTester = new CommandTester($command);
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
         "description": "content"
     }
 }
OUTPUT
,
            $output
        );
    }
}
