<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle;

use Paysera\Component\DependencyInjection\AddTaggedCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PayseraNormalizationBundle extends Bundle
{
    /**
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        // must be TypeAwareInterface, added with autoconfiguration, registered by implemented interfaces
        $container->addCompilerPass(new AddTaggedCompilerPass(
            'paysera_normalization.normalizer_registry_provider',
            'paysera_normalization.autoconfigured_normalizer',
            'addTypeAwareNormalizer',
            ['group' => null]
        ));

        $container->addCompilerPass(new AddTaggedCompilerPass(
            'paysera_normalization.normalizer_registry_provider',
            'paysera_normalization.normalizer',
            'addNormalizer',
            ['type' => null, 'group' => null]
        ));
        $container->addCompilerPass(new AddTaggedCompilerPass(
            'paysera_normalization.normalizer_registry_provider',
            'paysera_normalization.mixed_type_denormalizer',
            'addMixedTypeDenormalizer',
            ['type' => null, 'group' => null]
        ));
        $container->addCompilerPass(new AddTaggedCompilerPass(
            'paysera_normalization.normalizer_registry_provider',
            'paysera_normalization.object_denormalizer',
            'addObjectDenormalizer',
            ['type' => null, 'group' => null]
        ));
    }
}
