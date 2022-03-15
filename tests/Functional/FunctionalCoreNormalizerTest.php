<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\Tests\Functional;

use Paysera\Component\Normalization\CoreNormalizer;
use Paysera\Component\Normalization\NormalizationContext;

class FunctionalCoreNormalizerTest extends FunctionalTestCase
{
    /**
     * @var CoreNormalizer
     */
    private $coreNormalizer;

    protected function setUpTest()
    {
        parent::setUp();
        $container = $this->setUpContainer('basic.yml');
        $this->coreNormalizer = $container->get('core_normalizer');
    }

    /**
     * @param mixed $expectedResult
     * @param mixed $data
     *
     * @dataProvider normalizationProvider
     */
    public function testNormalization($expectedResult, $data)
    {
        $this->setUpTest();
        $this->assertEquals($expectedResult, $this->coreNormalizer->normalize($data));
        $this->tearDownTest();
    }

    /**
     * @param mixed $expectedResult
     * @param mixed $data
     * @param array $includedFields
     * @dataProvider normalizationWithIncludedFieldsProvider
     */
    public function testNormalizationWithIncludedFields($expectedResult, $data, array $includedFields)
    {
        $this->setUpTest();
        $context = new NormalizationContext($this->coreNormalizer, $includedFields);
        $this->assertEquals($expectedResult, $this->coreNormalizer->normalize($data, null, $context));
        $this->tearDownTest();
    }

    /**
     * @param mixed $expectedResult
     * @param mixed $data
     * @param string $type
     * @dataProvider normalizationWithTypeProvider
     */
    public function testNormalizationWithType($expectedResult, $data, string $type)
    {
        $this->setUpTest();
        $this->assertEquals($expectedResult, $this->coreNormalizer->normalize($data, $type));
        $this->tearDownTest();
    }

    public function normalizationProvider()
    {
        return [
            [1, 1],
        ];
    }

    public function normalizationWithIncludedFieldsProvider()
    {
        return [
            [1, 1, []],
        ];
    }

    public function normalizationWithTypeProvider()
    {
        return [
            [1, 1, 'plain'],
        ];
    }
}
