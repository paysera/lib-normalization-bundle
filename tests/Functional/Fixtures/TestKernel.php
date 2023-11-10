<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\Tests\Functional\Fixtures;

use Paysera\Bundle\NormalizationBundle\PayseraNormalizationBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class TestKernel extends Kernel
{
    private $testCaseFile;
    private $commonFile;

    public function __construct(string $testCaseFile, string $commonFile = 'common.yml')
    {
        parent::__construct((string)crc32($testCaseFile . $commonFile), true);
        $this->testCaseFile = $testCaseFile;
        $this->commonFile = $commonFile;
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new PayseraNormalizationBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/' . $this->commonFile);
        $loader->load(__DIR__ . '/config/cases/' . $this->testCaseFile);
    }
}
