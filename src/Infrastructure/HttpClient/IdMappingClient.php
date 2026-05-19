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
    /** @var array<string, string> */
    private readonly array $defaultHeaders;
    /** @var string[] */
    private readonly array $defaultAuth;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Target('s365_id_mapping')] private readonly LoggerInterface $logger,
        private readonly string $username,
        private readonly string $password,
        private readonly string $project,
    ) {
        $this->defaultHeaders = [
            'Project' => $this->project,
            'Accept' => 'application/json',
        ];
        $this->defaultAuth = [$this->username, $this->password];
    }

    /**
     * @param array<string, mixed> $options
     */
    public function forward(string $method, string $url, array $options = [], ?string $correlationId = null): S365Response
    {
        $headers = $this->defaultHeaders;
        if (null !== $correlationId) {
            $headers['X-Correlation-ID'] = $correlationId;
        }

        $finalOptions = [
            ...$options,
            'headers' => [...$headers, ...($options['headers'] ?? [])],
            'auth_basic' => $options['auth_basic'] ?? $this->defaultAuth,
        ];

        try {
            $response = $this->httpClient->request($method, $url, $finalOptions);

            return new S365Response(
                $response->getContent(false),
                $response->getStatusCode(),
                $response->getHeaders(false),
            );
        } catch (\Throwable $e) {
            $this->logger->error('S365 API Transport Error', ['url' => $url, 'error' => $e->getMessage()]);
            throw new S365CommunicationException('Transport error for '.$url, 0, $e);
        }
    }
}
