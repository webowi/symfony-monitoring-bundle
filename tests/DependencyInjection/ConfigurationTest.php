<?php

declare(strict_types=1);

namespace Webowi\SymfonyMonitoringBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Webowi\SymfonyMonitoringBundle\DependencyInjection\Configuration;

class ConfigurationTest extends TestCase
{
    /**
     * @param list<array<string, mixed>> $configs
     *
     * @return array<string, mixed>
     */
    private function process(array $configs): array
    {
        return (new Processor())->processConfiguration(new Configuration(), $configs);
    }

    #[Test]
    public function missingUrlAndApiKeyThrows(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->process([]);
    }

    #[Test]
    public function missingApiKeyThrows(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->process([['url' => 'https://example.test']]);
    }

    #[Test]
    public function levelDefaultsToError(): void
    {
        $config = $this->process([[
            'url'     => 'https://example.test',
            'api_key' => 'key',
        ]]);

        $this->assertSame(LogLevel::ERROR, $config['level']);
    }

    #[Test]
    public function levelMustBeAValidPsrLogLevel(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->process([[
            'url'     => 'https://example.test',
            'api_key' => 'key',
            'level'   => 'not-a-level',
        ]]);
    }

    #[Test]
    public function explicitValuesAreRetained(): void
    {
        $config = $this->process([[
            'url'     => 'https://example.test',
            'api_key' => 'key',
            'level'   => LogLevel::WARNING,
        ]]);

        $this->assertSame('https://example.test', $config['url']);
        $this->assertSame('key', $config['api_key']);
        $this->assertSame(LogLevel::WARNING, $config['level']);
    }
}
