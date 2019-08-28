<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle;

use Paysera\Component\DependencyInjection\AddTaggedCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PayseraNormalizationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        // must be TypeAwareInterface, added with autoconfiguration, registered by implemented interfaces
        $container->addCompilerPass(new AddTaggedCompilerPass(
            'paysera_normalization.normalizer_registry',
            'paysera_normalization.autoconfigured_normalizer',
            'addTypeAwareNormalizer'
        ));

        $container->addCompilerPass(new AddTaggedCompilerPass(
            'paysera_normalization.normalizer_registry',
            'paysera_normalization.normalizer',
            'addNormalizer',
            ['type' => null]
        ));
        $container->addCompilerPass(new AddTaggedCompilerPass(
            'paysera_normalization.normalizer_registry',
            'paysera_normalization.mixed_type_denormalizer',
            'addMixedTypeDenormalizer',
            ['type' => null]
        ));
        $container->addCompilerPass(new AddTaggedCompilerPass(
            'paysera_normalization.normalizer_registry',
            'paysera_normalization.object_denormalizer',
            'addObjectDenormalizer',
            ['type' => null]
        ));
    }
}
