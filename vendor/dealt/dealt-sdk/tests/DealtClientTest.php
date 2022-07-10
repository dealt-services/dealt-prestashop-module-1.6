<?php

use Dealt\DealtSDK\DealtClient;
use Dealt\DealtSDK\DealtEnvironment;
use Dealt\DealtSDK\Exceptions\InvalidArgumentException;
use Dealt\DealtSDK\Services\DealtMissions;
use Dealt\DealtSDK\Services\DealtOffers;
use PHPUnit\Framework\TestCase;

final class DealtClientTest extends TestCase
{
    public function testInitializesCorrectly(): void
    {
        $this->assertInstanceOf(
            DealtClient::class,
            new DealtClient(['api_key' => 'xxx', 'env' => DealtEnvironment::PRODUCTION])
        );
    }

    public function testInitializesCorrectlyWhenMissingEnv(): void
    {
        $this->assertInstanceOf(
            DealtClient::class,
            new DealtClient(['api_key' => 'xxx'])
        );
    }

    public function testThrowsWhenMissingApiKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DealtClient(['env' => DealtEnvironment::TEST]);
    }

    public function testThrowsWhenGivenWrongApiKeyType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DealtClient(['api_key' => []]);
    }

    public function testThrowsWhenGivenWrongEnvKeyType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new DealtClient(['api_key' => '', 'env' => []]);
    }

    public function testResolvesMissionsService(): void
    {
        $client = new DealtClient(['api_key' => 'xxx']);
        $this->assertInstanceOf(
            DealtMissions::class,
            $client->missions
        );
    }

    public function testResolvesOffersService(): void
    {
        $client = new DealtClient(['api_key' => 'xxx']);
        $this->assertInstanceOf(
            DealtOffers::class,
            $client->offers
        );
    }
}
