<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\Tests\Functional\Fixtures\Normalizer;

use Paysera\Bundle\NormalizationBundle\Tests\Functional\Fixtures\Entity\MyClass;
use Paysera\Component\Normalization\DenormalizationContext;
use Paysera\Component\Normalization\NormalizationContext;
use Paysera\Component\ObjectWrapper\ObjectWrapper;

class ExtendedMyClassNormalizer extends MyClassNormalizer
{
    /**
     * @param MyClass $entity
     * @param NormalizationContext $normalizationContext
     * @return array
     */
    public function normalize($entity, NormalizationContext $normalizationContext)
    {
        return ['field' => $entity->getField() . ':ext'];
    }

    public function denormalize(ObjectWrapper $input, DenormalizationContext $context)
    {
        return (new MyClass())->setField($input->getRequiredString('field') . ':ext');
    }

    public function getType(): string
    {
        return MyClass::class;
    }
}
