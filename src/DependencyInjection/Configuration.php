<?php

declare(strict_types=1);

namespace Webowi\SymfonyMonitoringBundle\DependencyInjection;

use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('symfony_monitoring');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('url')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('Base URL of your Monitoring Webowi instance.')
                ->end()
                ->scalarNode('api_key')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info("Your project's ingestion API key.")
                ->end()
                ->enumNode('level')
                    ->values(self::allowedLevels())
                    ->defaultValue(LogLevel::ERROR)
                    ->info('PSR-3 level at which buffered context is flushed and sent.')
                ->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * @return list<string>
     */
    private static function allowedLevels(): array
    {
        return [
            LogLevel::DEBUG,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
            LogLevel::ERROR,
            LogLevel::CRITICAL,
            LogLevel::ALERT,
            LogLevel::EMERGENCY,
        ];
    }
}
