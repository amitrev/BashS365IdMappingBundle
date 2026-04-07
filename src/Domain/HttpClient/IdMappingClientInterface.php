<?php

declare(strict_types=1);

namespace Bash\S365IDMappingBundle\Domain\HttpClient;

use Bash\S365IDMappingBundle\Domain\Dto\S365Response;

interface IdMappingClientInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function forward(string $method, string $url, array $options = [], ?string $correlationId = null): S365Response;
}
