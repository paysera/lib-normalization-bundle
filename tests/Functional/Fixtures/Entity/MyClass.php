<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\Tests\Functional\Fixtures\Entity;

class MyClass
{
    /**
     * @var string
     */
    private $field;

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     * @return $this
     */
    public function setField(string $field): MyClass
    {
        $this->field = $field;
        return $this;
    }
}
