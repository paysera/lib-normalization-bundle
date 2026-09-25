<?php
declare(strict_types=1);

namespace Paysera\Bundle\NormalizationBundle\Tests\Functional;

use Paysera\Bundle\NormalizationBundle\DependencyInjection\Configuration;
use Paysera\Bundle\NormalizationBundle\DependencyInjection\PayseraNormalizationExtension;
use Paysera\Bundle\NormalizationBundle\PayseraNormalizationBundle;
use Symfony\Component\ErrorHandler\DebugClassLoader;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class FunctionalDeprecationNoticesTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoadingTheBundleClassesRaisesNoDeprecationNamingThem()
    {
        $classes = [PayseraNormalizationBundle::class, PayseraNormalizationExtension::class, Configuration::class];

        $deprecations = [];
        set_error_handler(function (int $type, string $message) use (&$deprecations): bool {
            if ($type === E_USER_DEPRECATED || $type === E_DEPRECATED) {
                $deprecations[] = $message;
            }

            return true;
        });
        try {
            if (!class_exists(DebugClassLoader::class)) {
                $this->markTestSkipped('symfony/error-handler is not installed (Symfony below 4.4)');
            }
            $this->assertSame([false, false, false], array_map(function (string $class): bool {
                return class_exists($class, false);
            }, $classes));

            DebugClassLoader::enable();
            try {
                $loaded = array_map(function (string $class): bool {
                    return class_exists($class);
                }, $classes);
            } finally {
                DebugClassLoader::disable();
            }
        } finally {
            restore_error_handler();
        }

        $this->assertSame([true, true, true], $loaded);
        $bundleDeprecations = array_values(array_filter($deprecations, function (string $message): bool {
            return strpos($message, 'Paysera\\Bundle\\NormalizationBundle\\') !== false;
        }));
        $this->assertSame([], $bundleDeprecations);
    }
}
