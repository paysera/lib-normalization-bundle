<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\Tests\DependencyInjection;

use Paysera\Bundle\NormalizationBundle\DependencyInjection\PayseraNormalizationExtension;
use Paysera\Component\Normalization\CoreDenormalizer;
use Paysera\Component\Normalization\CoreNormalizer;
use Paysera\Component\Normalization\DataFilter;
use Paysera\Component\Normalization\Normalizer\DateTimeImmutableNormalizer;
use Paysera\Component\Normalization\Normalizer\DateTimeNormalizer;
use Paysera\Component\Normalization\NormalizerRegistryInterface;
use Paysera\Component\Normalization\Registry\GroupedNormalizerRegistryProvider;
use Paysera\Component\Normalization\TypeGuesser;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class PayseraNormalizationExtensionTest extends TestCase
{
    const DATE_TIME_CONFIGS = [['register_normalizers' => ['date_time' => ['format' => 'U']]]];

    /**
     * @dataProvider loadProvider
     * @param array<array<mixed>> $configs
     * @param array<string, array<string, mixed>> $expected
     */
    public function testLoadRegistersTheServices(array $configs, array $expected)
    {
        $container = new ContainerBuilder();
        $empty = new ContainerBuilder();

        (new PayseraNormalizationExtension())->load($configs, $container);

        $this->assertSame(self::normalize($expected), self::normalize([
            'definitions' => array_diff_key($container->getDefinitions(), $empty->getDefinitions()),
            'aliases' => array_diff_key($container->getAliases(), $empty->getAliases()),
            'parameters' => $container->getParameterBag()->all(),
        ]));
    }

    /**
     * @return array<string, array{array<array<mixed>>, array<string, array<string, mixed>>}>
     */
    public static function loadProvider(): array
    {
        $provider = new Reference('paysera_normalization.normalizer_registry_provider');
        $definitions = [
            'paysera_normalization.normalizer_registry_provider' =>
                (new Definition(GroupedNormalizerRegistryProvider::class))->setLazy(true),
            'paysera_normalization.normalizer_registry' => (new Definition(NormalizerRegistryInterface::class))
                ->setLazy(true)
                ->setFactory([$provider, 'getDefaultNormalizerRegistry']),
            'paysera_normalization.type_guesser' => new Definition(TypeGuesser::class),
            'paysera_normalization.data_filter' => new Definition(DataFilter::class),
            'paysera_normalization.core_normalizer' => new Definition(CoreNormalizer::class, [
                $provider,
                new Reference('paysera_normalization.type_guesser'),
                new Reference('paysera_normalization.data_filter'),
            ]),
            'paysera_normalization.core_denormalizer' => new Definition(CoreDenormalizer::class, [$provider]),
        ];
        $aliases = [
            CoreNormalizer::class => new Alias('paysera_normalization.core_normalizer'),
            CoreDenormalizer::class => new Alias('paysera_normalization.core_denormalizer'),
        ];
        $format = '%paysera_normalization.date_time_normalizer.format%';

        return [
            'without the date_time normalizers' => [
                [],
                ['definitions' => $definitions, 'aliases' => $aliases, 'parameters' => []],
            ],
            'with the date_time normalizers' => [
                self::DATE_TIME_CONFIGS,
                [
                    'definitions' => $definitions + [
                        'paysera_normalization.date_time_normalizer' =>
                            (new Definition(DateTimeNormalizer::class, [$format]))
                                ->addTag('paysera_normalization.autoconfigured_normalizer'),
                        'paysera_normalization.date_time_immutable_normalizer' =>
                            (new Definition(DateTimeImmutableNormalizer::class, [$format]))
                                ->addTag('paysera_normalization.autoconfigured_normalizer')
                                ->addTag(
                                    'paysera_normalization.mixed_type_denormalizer',
                                    ['type' => 'DateTimeInterface']
                                ),
                    ],
                    'aliases' => $aliases,
                    'parameters' => ['paysera_normalization.date_time_normalizer.format' => 'U'],
                ],
            ],
        ];
    }

    public function testLoadRaisesNoDeprecation()
    {
        $deprecations = [];
        set_error_handler(function (int $type, string $message) use (&$deprecations): bool {
            $deprecations[] = $message;

            return true;
        }, E_USER_DEPRECATED);
        try {
            (new PayseraNormalizationExtension())->load(self::DATE_TIME_CONFIGS, new ContainerBuilder());
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $deprecations);
    }

    /**
     * @param array<string, array<string, mixed>> $services
     * @return array<string, mixed>
     */
    private static function normalize(array $services): array
    {
        ksort($services['definitions']);
        ksort($services['aliases']);

        return self::export($services);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function export($value)
    {
        if (is_object($value)) {
            return [get_class($value) => self::export((array)$value)];
        }
        if (!is_array($value)) {
            return $value;
        }
        $exported = [];
        foreach ($value as $key => $item) {
            $exported[$key] = self::export($item);
        }

        return $exported;
    }
}
