<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\Tests\Functional;

use Paysera\Bundle\NormalizationBundle\DependencyInjection\PayseraNormalizationExtension;
use Paysera\Bundle\NormalizationBundle\Tests\Functional\Fixtures\Entity\MyClass;
use Paysera\Component\Normalization\CoreDenormalizer;
use Paysera\Component\Normalization\CoreNormalizer;

class FunctionalAutoconfigurationTest extends FunctionalTestCase
{
    /**
     * @var CoreDenormalizer
     */
    private $coreDenormalizer;

    /**
     * @var CoreNormalizer
     */
    private $coreNormalizer;

    protected function set_up()
    {
        parent::set_up();

        if (!PayseraNormalizationExtension::supportsAutoconfiguration()) {
            $this->markTestSkipped('This symfony version does not support autoconfiguration');
        }

        $container = $this->setUpContainer('autoconfiguration.yml');
        $this->coreDenormalizer = $container->get('core_denormalizer');
        $this->coreNormalizer = $container->get('core_normalizer');
    }

    public function testAutoconfiguration()
    {
        $entity = (new MyClass())->setField('value');

        $normalized = $this->coreNormalizer->normalize($entity);

        if (method_exists($this, 'assertIsObject')) {
            $this->assertIsObject($normalized);
        } else {
            $this->assertInternalType('object', $normalized);
        }

        $this->assertInstanceOf('stdClass', $normalized);

        $denormalized = $this->coreDenormalizer->denormalize($normalized, MyClass::class);
        $this->assertEquals($entity, $denormalized);
    }
}
