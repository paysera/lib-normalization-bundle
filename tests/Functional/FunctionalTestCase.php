<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\Tests\Functional;

use Paysera\Bundle\NormalizationBundle\Tests\Functional\Fixtures\TestKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ResettableContainerInterface;
use Symfony\Contracts\Service\ResetInterface;
use Symfony\Component\Filesystem\Filesystem;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

abstract class FunctionalTestCase extends TestCase
{
    /**
     * @var TestKernel
     */
    protected $kernel;

    protected function setUpContainer(string $testCaseFile, string $commonFile = 'common.yml'): ContainerInterface
    {
        $this->kernel = new TestKernel($testCaseFile, $commonFile);
        $this->kernel->boot();

        return $this->kernel->getContainer();
    }

    protected function tear_down()
    {
        $container = $this->kernel->getContainer();
        $this->kernel->shutdown();
        if (
            $container instanceof ResettableContainerInterface
            || $container instanceof ResetInterface
        ) {
            $container->reset();
        }

        $filesystem = new Filesystem();
        $filesystem->remove($this->kernel->getCacheDir());
    }
}
