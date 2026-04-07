<?php

declare(strict_types=1);

namespace Bash\S365IDMappingBundle;

use Bash\S365IDMappingBundle\DependencyInjection\S365IDMappingExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class S365IDMappingBundle extends AbstractBundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new S365IDMappingExtension();
    }
}
