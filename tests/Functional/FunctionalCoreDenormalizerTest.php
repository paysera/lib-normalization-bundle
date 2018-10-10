<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\Tests\Functional;

use Paysera\Component\Normalization\CoreDenormalizer;

class FunctionalCoreDenormalizerTest extends FunctionalTestCase
{
    /**
     * @var CoreDenormalizer
     */
    private $coreDenormalizer;

    protected function setUp()
    {
        parent::setUp();
        $container = $this->setUpContainer('basic.yml');
        $this->coreDenormalizer = $container->get('paysera_normalization.core_denormalizer');
    }

    /**
     * @param mixed $expectedResult
     * @param mixed $data
     * @param string $type
     *
     * @dataProvider denormalizationProvider
     */
    public function testDenormalization($expectedResult, $data, string $type)
    {
        $this->assertEquals($expectedResult, $this->coreDenormalizer->denormalize($data, $type));
    }

    public function denormalizationProvider()
    {
        return [
            [1, 1, 'plain'],
        ];
    }
}
