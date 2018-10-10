<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\DependencyInjection;

use Paysera\Component\Normalization\TypeAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class PayseraNormalizationExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        if (self::supportsAutoconfiguration()) {
            $container
                ->registerForAutoconfiguration(TypeAwareInterface::class)
                ->addTag('paysera_normalization.autoconfigured_normalizer')
            ;
        }

        if (isset($config['register_normalizers']['date_time'])) {
            $dateTimeFormat = $config['register_normalizers']['date_time']['format'];
            $container->setParameter('paysera_normalization.date_time_normalizer.format', $dateTimeFormat);
            $loader->load('services/date_time_normalizer.xml');
        }
    }

    public static function supportsAutoconfiguration()
    {
        return method_exists(ContainerBuilder::class, 'registerForAutoconfiguration');
    }
}
