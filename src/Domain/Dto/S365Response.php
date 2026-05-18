<?php

declare(strict_types=1);

namespace Bash\S365IDMappingBundle\Domain\Dto;

final class S365Response
{
    /** @var array<string, mixed> */
    private array $data;
    private bool $isDecoded = false;

    /**
     * @param array<string, string[]> $headers
     */
    public function __construct(
        private readonly string $content,
        private readonly int $statusCode,
        private readonly array $headers = [],
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, string[]>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \JsonException
     */
    public function toArray(): array
    {
        if (!$this->isDecoded) {
            $this->data = json_decode($this->content, true, 512, JSON_THROW_ON_ERROR);
            $this->isDecoded = true;
        }

        return $this->data;
    }
}
