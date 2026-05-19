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
                    return isset($options['headers']['x-correlation-id']) && 'cid-1' === $options['headers']['x-correlation-id'];
                }),
            )
            ->willReturn($contentResponse);

        $chunk = $this->createMock(\Symfony\Contracts\HttpClient\ChunkInterface::class);
        $chunk->method('getContent')->willReturn($contentResponse->getContent());

        $responseStream = new class([$chunk]) implements \Symfony\Contracts\HttpClient\ResponseStreamInterface {
            private int $key = 0;

            /** @param \Symfony\Contracts\HttpClient\ChunkInterface[] $chunks */
            public function __construct(private array $chunks)
            {
            }

            public function current(): \Symfony\Contracts\HttpClient\ChunkInterface
            {
                return $this->chunks[$this->key];
            }

            public function next(): void
            {
                ++$this->key;
            }

            public function key(): ResponseInterface
            {
                throw new \Exception('Not implemented');
            }

            public function valid(): bool
            {
                return isset($this->chunks[$this->key]);
            }

            public function rewind(): void
            {
                $this->key = 0;
            }
        };

        $contentHttpClient->method('stream')
            ->willReturn($responseStream);

        $logger = $this->createMock(LoggerInterface::class);

        $client = new IdMappingClient($contentHttpClient, $logger, 'user', 'pass', 'proj');

        $s365Response = $client->forward('GET', 'endpoint', [], 'cid-1');

        $this->assertSame('{"ok":true}', $s365Response->getContent());
    }
}
