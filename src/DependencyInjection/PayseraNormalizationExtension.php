<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\DependencyInjection;

use Paysera\Component\Normalization\CoreDenormalizer;
use Paysera\Component\Normalization\CoreNormalizer;
use Paysera\Component\Normalization\DataFilter;
use Paysera\Component\Normalization\Normalizer\DateTimeImmutableNormalizer;
use Paysera\Component\Normalization\Normalizer\DateTimeNormalizer;
use Paysera\Component\Normalization\NormalizerRegistryInterface;
use Paysera\Component\Normalization\Registry\GroupedNormalizerRegistryProvider;
use Paysera\Component\Normalization\TypeAwareInterface;
use Paysera\Component\Normalization\TypeGuesser;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class PayseraNormalizationExtension extends Extension
{
    public static function supportsAutoconfiguration()
    {
        return method_exists(ContainerBuilder::class, 'registerForAutoconfiguration');
    }

    /**
     * @param array<array<mixed>|null> $configs
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->registerServices($container);

        if (self::supportsAutoconfiguration()) {
            $container
                ->registerForAutoconfiguration(TypeAwareInterface::class)
                ->addTag('paysera_normalization.autoconfigured_normalizer')
            ;
        }

        if (isset($config['register_normalizers']['date_time'])) {
            $dateTimeFormat = $config['register_normalizers']['date_time']['format'];
            $container->setParameter('paysera_normalization.date_time_normalizer.format', $dateTimeFormat);
            $this->registerDateTimeNormalizers($container);
        }
    }

    private function registerServices(ContainerBuilder $container)
    {
        $container->setDefinition(
            'paysera_normalization.normalizer_registry_provider',
            (new Definition(GroupedNormalizerRegistryProvider::class))->setLazy(true)
        );
        $container->setDefinition(
            'paysera_normalization.normalizer_registry',
            (new Definition(NormalizerRegistryInterface::class))
                ->setLazy(true)
                ->setFactory([
                    new Reference('paysera_normalization.normalizer_registry_provider'),
                    'getDefaultNormalizerRegistry',
                ])
        );
        $container->setDefinition('paysera_normalization.type_guesser', new Definition(TypeGuesser::class));
        $container->setDefinition('paysera_normalization.data_filter', new Definition(DataFilter::class));

        $container->setDefinition('paysera_normalization.core_normalizer', new Definition(CoreNormalizer::class, [
            new Reference('paysera_normalization.normalizer_registry_provider'),
            new Reference('paysera_normalization.type_guesser'),
            new Reference('paysera_normalization.data_filter'),
        ]));
        $container->setAlias(CoreNormalizer::class, new Alias('paysera_normalization.core_normalizer'));

        $container->setDefinition('paysera_normalization.core_denormalizer', new Definition(CoreDenormalizer::class, [
            new Reference('paysera_normalization.normalizer_registry_provider'),
        ]));
        $container->setAlias(CoreDenormalizer::class, new Alias('paysera_normalization.core_denormalizer'));
    }

    private function registerDateTimeNormalizers(ContainerBuilder $container)
    {
        $format = '%paysera_normalization.date_time_normalizer.format%';
        $container->setDefinition(
            'paysera_normalization.date_time_normalizer',
            (new Definition(DateTimeNormalizer::class, [$format]))
                ->addTag('paysera_normalization.autoconfigured_normalizer')
        );
        $container->setDefinition(
            'paysera_normalization.date_time_immutable_normalizer',
            (new Definition(DateTimeImmutableNormalizer::class, [$format]))
                ->addTag('paysera_normalization.autoconfigured_normalizer')
                ->addTag('paysera_normalization.mixed_type_denormalizer', ['type' => 'DateTimeInterface'])
        );
    }
}
