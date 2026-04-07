<?php

declare(strict_types=1);

namespace Bash\S365IDMappingBundle\DependencyInjection;

use Bash\S365IDMappingBundle\Infrastructure\HttpClient\IdMappingClient;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class S365IDMappingExtension extends Extension
{
    public function getAlias(): string
    {
        return 's365_id_mapping';
    }

    /**
     * @param array<string, mixed> $config
     */
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration();
    }

    /**
     * @param array<string, mixed>[] $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition(IdMappingClient::class);
        $definition->setArgument('$username', $config['username']);
        $definition->setArgument('$password', $config['password']);
        $definition->setArgument('$project', $config['project']);
    }
}
