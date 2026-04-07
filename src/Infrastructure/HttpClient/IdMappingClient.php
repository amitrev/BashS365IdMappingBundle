<?php

declare(strict_types=1);

namespace Bash\S365IDMappingBundle\Infrastructure\HttpClient;

use Bash\S365IDMappingBundle\Domain\Dto\S365Response;
use Bash\S365IDMappingBundle\Domain\Exception\S365CommunicationException;
use Bash\S365IDMappingBundle\Domain\HttpClient\IdMappingClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class IdMappingClient implements IdMappingClientInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Target('s365_id_mapping')] private LoggerInterface $logger,
        private string $username,
        private string $password,
        private string $project,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function forward(string $method, string $url, array $options = [], ?string $correlationId = null): S365Response
    {
        $defaultOptions = [
            'auth_basic' => [$this->username, $this->password],
            'headers' => [
                'Project' => $this->project,
                'Accept' => 'application/json',
            ],
        ];

        if ($correlationId) {
            $defaultOptions['headers']['X-Correlation-ID'] = $correlationId;
        }

        $finalOptions = array_merge_recursive($defaultOptions, $options);

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
