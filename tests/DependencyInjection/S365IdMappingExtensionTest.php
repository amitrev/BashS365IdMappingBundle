<?php

declare(strict_types=1);

namespace Bash\S365IDMappingBundle\Tests\DependencyInjection;

use Bash\S365IDMappingBundle\DependencyInjection\S365IDMappingExtension;
use Bash\S365IDMappingBundle\Infrastructure\HttpClient\IdMappingClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class S365IdMappingExtensionTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function test_extension_loads_services(): void
    {
        $container = new ContainerBuilder();

        $extension = new S365IDMappingExtension();

        $config = [
            'base_url' => 'https://api',
            'username' => 'u',
            'password' => 'p',
            'project' => 'proj',
        ];

        $extension->load([$config], $container);

        $this->assertTrue($container->hasDefinition(IdMappingClient::class));
    }
}
