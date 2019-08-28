<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\Tests\Functional;

use Paysera\Bundle\NormalizationBundle\Tests\Functional\Fixtures\Entity\MyClass;
use Paysera\Component\Normalization\CoreDenormalizer;
use Paysera\Component\Normalization\CoreNormalizer;

class FunctionalTagsTest extends FunctionalTestCase
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
        $container = $this->setUpContainer('tags.yml');
        $this->coreDenormalizer = $container->get('paysera_normalization.core_denormalizer');
        $this->coreNormalizer = $container->get('paysera_normalization.core_normalizer');
    }

    public function testTags()
    {
        $entity = (new MyClass())->setField('value');

        $normalized = $this->coreNormalizer->normalize($entity);
        $this->assertInternalType('object', $normalized);
        $this->assertInstanceOf('stdClass', $normalized);

        $denormalized = $this->coreDenormalizer->denormalize($normalized, MyClass::class);
        $this->assertEquals($entity, $denormalized);
    }

    public function testTagsWithType()
    {
        $entity = (new MyClass())->setField('value');

        $normalized = $this->coreNormalizer->normalize($entity, 'my_class');
        $this->assertInternalType('object', $normalized);
        $this->assertInstanceOf('stdClass', $normalized);

        $denormalized = $this->coreDenormalizer->denormalize($normalized, 'my_class');
        $this->assertEquals($entity, $denormalized);

        $denormalizedFromScalar = $this->coreDenormalizer->denormalize('value', 'my_class_scalar');
        $this->assertEquals($entity, $denormalizedFromScalar);

        $denormalizedFromScalar = $this->coreDenormalizer->denormalize('value', 'my_class_scalar2');
        $this->assertEquals($entity, $denormalizedFromScalar);
    }
}
