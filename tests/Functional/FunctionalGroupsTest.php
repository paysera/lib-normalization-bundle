<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\Tests\Functional;

use Paysera\Bundle\NormalizationBundle\Tests\Functional\Fixtures\Entity\MyClass;
use Paysera\Component\Normalization\CoreDenormalizer;
use Paysera\Component\Normalization\CoreNormalizer;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\NormalizationContext;

class FunctionalGroupsTest extends FunctionalTestCase
{
    /**
     * @var CoreDenormalizer
     */
    private $coreDenormalizer;

    /**
     * @var CoreNormalizer
     */
    private $coreNormalizer;

    protected function setUp()
    {
        parent::setUp();
        $container = $this->setUpContainer('groups.yml');
        $this->coreDenormalizer = $container->get('core_denormalizer');
        $this->coreNormalizer = $container->get('core_normalizer');
    }

    public function testGroupsWithAutoconfiguredTag()
    {
        $entity = (new MyClass())->setField('value');

        $normalized = $this->coreNormalizer->normalize($entity);
        $this->assertSame('value', $normalized->field);

        $normalizedExt = $this->coreNormalizer->normalize($entity, null, new NormalizationContext(
            $this->coreNormalizer,
            [],
            'extended'
        ));
        $this->assertSame('value:ext', $normalizedExt->field);

        /** @var MyClass $denormalized */
        $denormalized = $this->coreDenormalizer->denormalize($normalized, MyClass::class);
        $this->assertSame('value', $denormalized->getField());

        /** @var MyClass $denormalizedExt */
        $denormalizedExt = $this->coreDenormalizer->denormalize(
            $normalized,
            MyClass::class,
            new DenormalizationContext($this->coreDenormalizer, 'extended')
        );
        $this->assertSame('value:ext', $denormalizedExt->getField());
    }

    public function testGroupsWithBasicTags()
    {
        $entity = (new MyClass())->setField('value');

        $normalized = $this->coreNormalizer->normalize($entity, 'my_class');
        $this->assertSame('value', $normalized->field);

        $normalizedExt = $this->coreNormalizer->normalize($entity, 'my_class', new NormalizationContext(
            $this->coreNormalizer,
            [],
            'extended'
        ));
        $this->assertSame('value:ext', $normalizedExt->field);

        /** @var MyClass $denormalized */
        $denormalized = $this->coreDenormalizer->denormalize($normalized, 'my_class');
        $this->assertSame('value', $denormalized->getField());

        /** @var MyClass $denormalizedExt */
        $denormalizedExt = $this->coreDenormalizer->denormalize(
            $normalized,
            'my_class',
            new DenormalizationContext($this->coreDenormalizer, 'extended')
        );
        $this->assertSame('value:ext', $denormalizedExt->getField());
    }

    public function testGroupsWithFallback()
    {
        $entity = (new MyClass())->setField('value');

        $normalizedExt = $this->coreNormalizer->normalize($entity, 'my_class2', new NormalizationContext(
            $this->coreNormalizer,
            [],
            'extended'
        ));
        $this->assertSame('value', $normalizedExt->field);

        /** @var MyClass $denormalizedExt */
        $denormalizedExt = $this->coreDenormalizer->denormalize(
            $normalizedExt,
            'my_class2',
            new DenormalizationContext($this->coreDenormalizer, 'extended')
        );
        $this->assertSame('value', $denormalizedExt->getField());
    }

    public function testGroupsWithMixedDenormalizerTag()
    {
        /** @var MyClass $denormalizedFromScalar */
        $denormalizedFromScalar = $this->coreDenormalizer->denormalize(
            'value',
            'my_class_scalar',
            new DenormalizationContext($this->coreDenormalizer, 'extended')
        );
        $this->assertSame('value', $denormalizedFromScalar->getField());
    }
}
