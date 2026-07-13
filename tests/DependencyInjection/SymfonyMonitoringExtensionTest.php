<?php

declare(strict_types=1);

namespace Webowi\SymfonyMonitoringBundle\Tests\DependencyInjection;

use Monolog\Level;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Webowi\SymfonyMonitoringBundle\DependencyInjection\SymfonyMonitoringExtension;
use Webowi\SymfonyMonitoringBundle\Handler\IngestHandler;

class SymfonyMonitoringExtensionTest extends TestCase
{
    /**
     * @param array<string, mixed> $config
     */
    private function loadContainer(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $extension = new SymfonyMonitoringExtension();
        $container->registerExtension($extension);
        $container->loadFromExtension('symfony_monitoring', $config);

        $extension->prepend($container);
        $extension->load([$config], $container);

        return $container;
    }

    #[Test]
    public function loadRegistersIngestHandlerServiceWithConfiguredUrlAndApiKey(): void
    {
        $container = $this->loadContainer([
            'url'     => 'https://example.test/api/v1/logs/ingest',
            'api_key' => 'mon_ing_key',
        ]);

        $definition = $container->getDefinition(SymfonyMonitoringExtension::INGEST_HANDLER_SERVICE_ID);

        $this->assertSame(IngestHandler::class, $definition->getClass());
        $this->assertSame('https://example.test/api/v1/logs/ingest', $definition->getArgument(0));
        $this->assertSame('mon_ing_key', $definition->getArgument(1));
    }

    #[Test]
    public function loadForcesIngestHandlerServiceLevelToDebugRegardlessOfBundleLevel(): void
    {
        $container = $this->loadContainer([
            'url'     => 'https://example.test',
            'api_key' => 'key',
            'level'   => LogLevel::CRITICAL,
        ]);

        $definition = $container->getDefinition(SymfonyMonitoringExtension::INGEST_HANDLER_SERVICE_ID);

        $this->assertSame(Level::Debug, $definition->getArgument(4));
        $this->assertFalse($definition->getArgument(5));
    }

    #[Test]
    public function prependInjectsFingersCrossedHandlerAtConfiguredActionLevel(): void
    {
        $container = $this->loadContainer([
            'url'     => 'https://example.test',
            'api_key' => 'key',
            'level'   => LogLevel::WARNING,
        ]);

        $monologConfig = $container->getExtensionConfig('monolog');

        $this->assertSame('fingers_crossed', $monologConfig[0]['handlers']['symfony_monitoring']['type']);
        $this->assertSame(LogLevel::WARNING, $monologConfig[0]['handlers']['symfony_monitoring']['action_level']);
        $this->assertSame('symfony_monitoring_http', $monologConfig[0]['handlers']['symfony_monitoring']['handler']);
        $this->assertFalse($monologConfig[0]['handlers']['symfony_monitoring']['bubble']);
        $this->assertSame('service', $monologConfig[0]['handlers']['symfony_monitoring_http']['type']);
        $this->assertSame(
            SymfonyMonitoringExtension::INGEST_HANDLER_SERVICE_ID,
            $monologConfig[0]['handlers']['symfony_monitoring_http']['id'],
        );
    }

    #[Test]
    public function prependDefaultsActionLevelToErrorWhenLevelIsOmitted(): void
    {
        $container = $this->loadContainer([
            'url'     => 'https://example.test',
            'api_key' => 'key',
        ]);

        $monologConfig = $container->getExtensionConfig('monolog');

        $this->assertSame(LogLevel::ERROR, $monologConfig[0]['handlers']['symfony_monitoring']['action_level']);
    }

    #[Test]
    public function missingUrlThrowsInvalidConfigurationException(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->loadContainer(['api_key' => 'key']);
    }

    #[Test]
    public function missingApiKeyThrowsInvalidConfigurationException(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->loadContainer(['url' => 'https://example.test']);
    }
}
