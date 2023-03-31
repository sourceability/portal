<?php

namespace Sourceability\Portal\Tests\Bundle\SampleApp;

use Http\Client\Plugin\Vcr\NamingStrategy\PathNamingStrategy;
use Http\Client\Plugin\Vcr\Recorder\FilesystemRecorder;
use Http\Client\Plugin\Vcr\RecordPlugin;
use Http\Client\Plugin\Vcr\ReplayPlugin;
use Sourceability\Portal\Bundle\SourceabilityPortalBundle;
use Sourceability\Portal\Tests\Bundle\SampleApp\Command\CustomJokeCommand;
use Sourceability\Portal\Tests\Bundle\SampleApp\Spells\CodeReviewSpell;
use Sourceability\Portal\Tests\Bundle\SampleApp\Spells\JokeSpell;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new SourceabilityPortalBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->parameters()->set('portal_dont_record_responses', true);

        $container->services()
            ->set(PathNamingStrategy::class, PathNamingStrategy::class)

            ->set(FilesystemRecorder::class, FilesystemRecorder::class)
                ->args([__DIR__ . '/vcr'])

            ->set(RecordPlugin::class, RecordPlugin::class)
                ->args([
                    new ReferenceConfigurator(PathNamingStrategy::class),
                    new ReferenceConfigurator(FilesystemRecorder::class),
                ])

            ->set(ReplayPlugin::class, ReplayPlugin::class)
                ->args([
                    new ReferenceConfigurator(PathNamingStrategy::class),
                    new ReferenceConfigurator(FilesystemRecorder::class),

                    // To record responses:
                    // PORTAL_RECORD_RESPONSES=1 phpunit ...
                    '%env(default:portal_dont_record_responses:not:PORTAL_RECORD_RESPONSES)%'
                ])

            ->set(CodeReviewSpell::class, CodeReviewSpell::class)
                ->autowire()
                ->tag('sourceability_portal.spell', ['short_name' => 'CodeReview'])

            ->set(JokeSpell::class, JokeSpell::class)
                ->autoconfigure() // should auto tag

            ->set(CustomJokeCommand::class, CustomJokeCommand::class)
                ->autowire()
                ->autoconfigure()
        ;

        $container->extension('sourceability_portal', [
            'httplug_plugins' => [
                RecordPlugin::class,
                ReplayPlugin::class,
            ],
        ]);
    }
}
