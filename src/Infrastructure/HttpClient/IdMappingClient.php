<?php

declare(strict_types=1);

namespace Bash\S365IDMappingBundle\Infrastructure\HttpClient;

use Bash\S365IDMappingBundle\Domain\Dto\S365Response;
use Bash\S365IDMappingBundle\Domain\Exception\S365CommunicationException;
use Bash\S365IDMappingBundle\Domain\HttpClient\IdMappingClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class IdMappingClient implements IdMappingClientInterface
{
    /** @var array<string, mixed> */
    private readonly array $baseOptions;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Target('s365_id_mapping')] private readonly LoggerInterface $logger,
        private readonly string $username,
        private readonly string $password,
        private readonly string $project,
    ) {
        $this->baseOptions = [
            'headers' => [
                'project' => $this->project,
                'accept' => 'application/json',
            ],
            'auth_basic' => [$this->username, $this->password],
        ];
    }

    /**
     * @param array<string, mixed> $options
     */
    public function forward(string $method, string $url, array $options = [], ?string $correlationId = null): S365Response
    {
        if ([] === $options && null === $correlationId) {
            $finalOptions = $this->baseOptions;
        } else {
            $headers = $this->baseOptions['headers'];
            if (null !== $correlationId) {
                $headers['x-correlation-id'] = $correlationId;
            }

            $finalOptions = [
                ...$options,
                'headers' => [...$headers, ...($options['headers'] ?? [])],
                'auth_basic' => $options['auth_basic'] ?? $this->baseOptions['auth_basic'],
            ];
        }

        try {
            $response = $this->httpClient->request($method, $url, $finalOptions);

            return new S365Response(
                static fn () => $response->getContent(false),
                $response->getStatusCode(),
                $response->getHeaders(false),
            );
        } catch (\Throwable $e) {
            $this->logger->error('S365 API Transport Error', ['url' => $url, 'error' => $e->getMessage()]);
            throw new S365CommunicationException('Transport error for '.$url, 0, $e);
        }
    }
}
