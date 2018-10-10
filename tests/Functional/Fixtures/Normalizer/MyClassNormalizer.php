<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\Tests\Functional\Fixtures\Normalizer;

use Paysera\Bundle\NormalizationBundle\Tests\Functional\Fixtures\Entity\MyClass;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\NormalizationContext;
use Paysera\Component\Normalization\NormalizerInterface;
use Paysera\Component\Normalization\ObjectDenormalizerInterface;
use Paysera\Component\Normalization\TypeAwareInterface;
use Paysera\Component\ObjectWrapper\ObjectWrapper;

class MyClassNormalizer implements ObjectDenormalizerInterface, NormalizerInterface, TypeAwareInterface
{
    /**
     * @param MyClass $entity
     * @param NormalizationContext $normalizationContext
     * @return array
     */
    public function normalize($entity, NormalizationContext $normalizationContext)
    {
        return ['field' => $entity->getField()];
    }

    public function denormalize(ObjectWrapper $input, DenormalizationContext $context)
    {
        return (new MyClass())->setField($input->getRequiredString('field'));
    }

    public function getType(): string
    {
        return MyClass::class;
    }
}
