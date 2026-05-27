<?php

declare(strict_types=1);

namespace Bash\S365IDMappingBundle\Infrastructure\Symfony\Controller;

use Bash\S365IDMappingBundle\Domain\Exception\S365IDMappingException;
use Bash\S365IDMappingBundle\Domain\HttpClient\IdMappingClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[AsController]
final readonly class IdMappingProxyController
{
    public function __construct(
        private IdMappingClientInterface $idMappingClient,
    ) {
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function __invoke(Request $request, string $endpoint): Response
    {
        if (str_contains($endpoint, '..')) {
            throw new S365IDMappingException('Invalid or restricted endpoint');
        }

        $query = $request->query->all();
        $correlationId = $request->headers->get('X-Correlation-ID');
        $method = $request->getMethod();
        $options = [];

        if ('GET' !== $method && 'HEAD' !== $method) {
            $options['body'] = $request->getContent(true);
            $options['headers']['content-type'] = $request->headers->get('Content-Type', 'application/json');
        } elseif ($request->headers->has('Content-Type')) {
            $options['headers']['content-type'] = $request->headers->get('Content-Type');
        }

        if ([] !== $query) {
            $options['query'] = $query;
        }

        $s365Response = $this->idMappingClient->forward(
            $request->getMethod(),
            $endpoint,
            $options,
            $correlationId,
        );

        return new StreamedResponse(
            static function () use ($s365Response): void {
                foreach ($s365Response->toIterable() as $chunk) {
                    echo $chunk;
                    flush();
                }
            },
            $s365Response->getStatusCode(),
            self::filterHeaders($s365Response->getHeaders()),
        );
    }

    /**
     * @param array<string, string[]> $headers
     *
     * @return array<string, string[]>
     */
    private static function filterHeaders(array $headers): array
    {
        static $hopByHop = [
            'connection' => true,
            'keep-alive' => true,
            'proxy-authenticate' => true,
            'proxy-authorization' => true,
            'te' => true,
            'trailers' => true,
            'transfer-encoding' => true,
            'upgrade' => true,
            'content-length' => true,
            'content-encoding' => true,
        ];

        return array_diff_key($headers, $hopByHop);
    }
}
