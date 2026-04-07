<?php

declare(strict_types=1);

namespace Bash\S365IDMappingBundle\Tests\Infrastructure\HttpClient;

use Bash\S365IDMappingBundle\Infrastructure\HttpClient\IdMappingClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class IdMappingClientTest extends TestCase
{
    public function test_forward_includes_correlation_id_header(): void
    {
        // Content http client mock: we assert this one gets the X-Correlation-ID header
        $contentHttpClient = $this->createMock(HttpClientInterface::class);

        $contentResponse = $this->createMock(ResponseInterface::class);
        $contentResponse->method('getStatusCode')->willReturn(200);
        $contentResponse->method('getContent')->willReturn('{"ok":true}');
        $contentResponse->method('getHeaders')->willReturn(['x-custom' => ['v']]);

        $contentHttpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'endpoint',
                $this->callback(function ($options) {
                    return isset($options['headers']['X-Correlation-ID']) && 'cid-1' === $options['headers']['X-Correlation-ID'];
                }),
            )
            ->willReturn($contentResponse);

        $logger = $this->createMock(LoggerInterface::class);

        $client = new IdMappingClient($contentHttpClient, $logger, 'user', 'pass', 'proj');

        $s365Response = $client->forward('GET', 'endpoint', [], 'cid-1');

        $this->assertSame('{"ok":true}', $s365Response->getContent());
    }
}
