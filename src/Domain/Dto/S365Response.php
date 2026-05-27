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
    /** @var string[] */
    private array $chunks = [];
    private bool $isStreamConsumed = false;
    private ?\Iterator $iterator = null;

    /**
     * @param string|iterable<string>|\Closure(): (string|iterable<string>) $contentOrLoader
     * @param array<string, string[]>                                       $headers
     */
    public function __construct(
        private readonly string|iterable|\Closure $contentOrLoader,
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
            foreach ($this->toIterable() as $_) {
            }
        }

        return $this->content;
    }

    /**
     * @return iterable<string>
     */
    public function toIterable(): iterable
    {
        if ($this->isContentLoaded) {
            yield $this->content;

            return;
        }

        yield from $this->chunks;

        if ($this->isStreamConsumed) {
            return;
        }

        if (null === $this->iterator) {
            $content = $this->contentOrLoader;
            if ($content instanceof \Closure) {
                $content = $content();
            }

            if (\is_string($content)) {
                $this->content = $content;
                $this->isContentLoaded = true;
                yield $content;

                return;
            }

            $this->iterator = $content instanceof \Iterator ? $content : (fn () => yield from $content)();
        }

        while ($this->iterator->valid()) {
            $chunk = $this->iterator->current();
            $this->chunks[] = $chunk;
            yield $chunk;
            $this->iterator->next();
        }

        $this->content = implode('', $this->chunks);
        $this->chunks = [];
        $this->isContentLoaded = true;
        $this->isStreamConsumed = true;
        $this->iterator = null;
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
