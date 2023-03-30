<?php

declare(strict_types=1);

namespace Sourceability\Portal\Bundle\DependencyInjection;

use Http\Client\Common\Plugin\AddHostPlugin;
use Http\Client\Common\Plugin\AddPathPlugin;
use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\Plugin\ErrorPlugin;
use Http\Client\Common\PluginClient;
use Http\Client\HttpClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Message\Authentication\Bearer;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Sourceability\OpenAIClient\Client;
use Sourceability\Portal\Command\CastCommand;
use Sourceability\Portal\Completer\ChatGPTCompleter;
use Sourceability\Portal\Completer\Completer;
use Sourceability\Portal\Portal;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class SourceabilityPortalExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $container
            ->register(Portal::class, Portal::class)
            ->setArguments([
                new Reference(Completer::class),
            ]);

        $container
            ->register(Completer::class, ChatGPTCompleter::class)
            ->setArguments([
                new Reference(Client::class),
            ]);

        $container
            ->register(UriFactoryInterface::class, UriFactoryInterface::class)
            ->setFactory([
                Psr17FactoryDiscovery::class,
                'findUriFactory',
            ]);

        $container
            ->register($authenticationService = $this->serviceId('authentication'), Bearer::class)
            ->setArguments([$mergedConfig['openai_api_key']]);
        $container
            ->register($authenticationPluginService = $this->serviceId('plugin.authentication'), AuthenticationPlugin::class)
            ->setArguments([
                new Reference($authenticationService),
            ]);
        $container
            ->register($uriService = $this->serviceId('uri'), UriInterface::class)
            ->setFactory([
                new Reference(UriFactoryInterface::class),
                'createUri',
            ])
            ->setArguments(['https://api.openai.com/v1']);
        $container
            ->register($hostPluginService = $this->serviceId('plugin.host'), AddHostPlugin::class)
            ->setArguments([new Reference($uriService)]);
        $container
            ->register($pathPluginService = $this->serviceId('plugin.path'), AddPathPlugin::class)
            ->setArguments([new Reference($uriService)]);
        $container
            ->register($errorPluginService = $this->serviceId('plugin.error'), ErrorPlugin::class);

        $pluginServices = [
            $authenticationPluginService,
            $hostPluginService,
            $pathPluginService,
            $errorPluginService,
        ];

        if (array_key_exists('httplug_plugins', $mergedConfig)
            && is_array($mergedConfig['httplug_plugins'])
        ) {
            $pluginServices = [
                ...$pluginServices,
                ...$mergedConfig['httplug_plugins'],
            ];
        }

        $container
            ->register($httpClientService = $this->serviceId('http_client'), PluginClient::class)
            ->setArguments([
                new Reference(HttpClient::class),
                array_map(fn (string $service): Reference => new Reference($service), $pluginServices),
            ]);

        $container
            ->register(Client::class, Client::class)
            ->setFactory([
                Client::class,
                'create',
            ])
            ->setArguments([
                new Reference($httpClientService),
                [],
                [],
            ]);

        $container
            ->register(CastCommand::class, CastCommand::class)
            ->setArguments([new Reference(Portal::class)])
            ->addTag('console.command', [
                'command' => 'portal:cast',
            ]);
    }

    private function serviceId(string $id): string
    {
        return sprintf('sourceability_portal.%s', $id);
    }
}
