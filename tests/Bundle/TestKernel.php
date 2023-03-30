<?php

namespace Sourceability\Portal\Tests\Bundle;

use Http\Client\Plugin\Vcr\NamingStrategy\PathNamingStrategy;
use Http\Client\Plugin\Vcr\Recorder\FilesystemRecorder;
use Http\Client\Plugin\Vcr\RecordPlugin;
use Http\Client\Plugin\Vcr\ReplayPlugin;
use Sourceability\Portal\Bundle\SourceabilityPortalBundle;
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
                    true, // set this to false in order to record new responses
                ])
        ;

        $container->extension('sourceability_portal', [
            'httplug_plugins' => [
                RecordPlugin::class,
                ReplayPlugin::class,
            ],
        ]);
    }
}
