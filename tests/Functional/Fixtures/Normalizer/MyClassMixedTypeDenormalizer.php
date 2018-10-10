<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\Tests\Functional\Fixtures\Normalizer;

use Paysera\Bundle\NormalizationBundle\Tests\Functional\Fixtures\Entity\MyClass;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\MixedTypeDenormalizerInterface;

class MyClassMixedTypeDenormalizer implements MixedTypeDenormalizerInterface
{
    public function denormalize($input, DenormalizationContext $context)
    {
        return (new MyClass())->setField($input);
    }
}
