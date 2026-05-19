<?php

declare(strict_types=1);

namespace Bash\S365IDMappingBundle\Domain\Dto;

final class S365Response
{
    /** @var array<string, mixed> */
    private array $data;
    private bool $isDecoded = false;
    private string $content;
    private bool $isContentLoaded = false;

    /**
     * @param string|\Closure(): string $contentOrLoader
     * @param array<string, string[]>   $headers
     */
    public function __construct(
        private readonly string|\Closure $contentOrLoader,
        private readonly int $statusCode,
        private readonly array $headers = [],
    ) {
        if (\is_string($this->contentOrLoader)) {
            $this->content = $this->contentOrLoader;
            $this->isContentLoaded = true;
        }
    }

    public function getContent(): string
    {
        if (!$this->isContentLoaded) {
            /** @var \Closure(): string $loader */
            $loader = $this->contentOrLoader;
            $this->content = $loader();
            $this->isContentLoaded = true;
        }

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
            $this->data = json_decode($this->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $this->isDecoded = true;
        }

        return $this->data;
    }
}
