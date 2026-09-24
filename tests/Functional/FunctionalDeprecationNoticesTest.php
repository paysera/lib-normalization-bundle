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
     * Symfony's debug class loader, which applications run in their dev environment and under symfony/phpunit-bridge,
     * checks every class as it is loaded against the parents and interfaces it implements. None of its notices may
     * name this bundle's classes. The test runs in its own process so that the classes are loaded after the debug
     * class loader is enabled.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoadingTheBundleClassesRaisesNoDeprecationNamingThem()
    {
        $classes = [PayseraNormalizationBundle::class, PayseraNormalizationExtension::class, Configuration::class];

        // collect before anything loads: on PHP 8.4, compiling Symfony 4.4's own files raises deprecation notices, and a
        // notice that reaches the child process's output fails the test whatever it says
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
            // the check means something only if the classes are loaded here, after the debug class loader is enabled
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
