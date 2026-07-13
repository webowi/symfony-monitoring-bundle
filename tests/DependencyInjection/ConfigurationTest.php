<?php

declare(strict_types=1);

namespace Webowi\SymfonyMonitoringBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * @return iterable<array{0: string}>
     */
    public static function provideValidLevels(): iterable
    {
        yield [LogLevel::DEBUG];
        yield [LogLevel::INFO];
        yield [LogLevel::NOTICE];
        yield [LogLevel::WARNING];
        yield [LogLevel::ERROR];
        yield [LogLevel::CRITICAL];
        yield [LogLevel::ALERT];
        yield [LogLevel::EMERGENCY];
    }

    #[Test, DataProvider('provideValidLevels')]
    public function explicitValuesAreRetained(string $level): void
    {
        $config = $this->process([[
            'url'     => 'https://example.test',
            'api_key' => 'key',
            'level'   => $level,
        ]]);

        $this->assertSame('https://example.test', $config['url']);
        $this->assertSame('key', $config['api_key']);
        $this->assertSame($level, $config['level']);
    }
}
