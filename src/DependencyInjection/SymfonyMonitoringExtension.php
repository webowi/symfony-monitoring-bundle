<?php

declare(strict_types=1);

namespace Webowi\SymfonyMonitoringBundle\DependencyInjection;

use Monolog\Level;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Webowi\SymfonyMonitoringBundle\Handler\IngestHandler;

final class SymfonyMonitoringExtension extends Extension implements PrependExtensionInterface
{
    public const INGEST_HANDLER_SERVICE_ID = 'symfony_monitoring.ingest_handler';

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $definition = new Definition(IngestHandler::class);
        $definition->setArguments([
            $config['url'],
            $config['api_key'],
            null,
            null,
            Level::Debug,
            false,
        ]);

        $container->setDefinition(self::INGEST_HANDLER_SERVICE_ID, $definition);
    }

    public function prepend(ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(
            $this->getConfiguration([], $container),
            $container->getExtensionConfig($this->getAlias()),
        );

        $container->prependExtensionConfig('monolog', [
            'handlers' => [
                'symfony_monitoring' => [
                    'type'         => 'fingers_crossed',
                    'action_level' => $config['level'],
                    'handler'      => 'symfony_monitoring_http',
                    'bubble'       => false,
                ],
                'symfony_monitoring_http' => [
                    'type' => 'service',
                    'id'   => self::INGEST_HANDLER_SERVICE_ID,
                ],
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $config
     */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }
}
