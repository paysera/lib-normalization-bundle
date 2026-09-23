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
        // collect before anything loads: on PHP 8.4, compiling Symfony 4.4's own files raises deprecation notices, and a
        // notice that reaches the child process's output fails the test whatever it says
        $deprecations = [];
        set_error_handler(function ($type, $message) use (&$deprecations) {
            if ($type === E_USER_DEPRECATED || $type === E_DEPRECATED) {
                $deprecations[] = $message;
            }

            return true;
        });
        try {
            if (!class_exists(DebugClassLoader::class)) {
                $this->markTestSkipped('symfony/error-handler is not installed (Symfony below 4.4)');
            }
            DebugClassLoader::enable();
            try {
                class_exists(PayseraNormalizationBundle::class);
                class_exists(PayseraNormalizationExtension::class);
                class_exists(Configuration::class);
            } finally {
                DebugClassLoader::disable();
            }
        } finally {
            restore_error_handler();
        }

        $bundleDeprecations = array_values(array_filter($deprecations, function ($message) {
            return strpos($message, 'Paysera\\Bundle\\NormalizationBundle\\') !== false;
        }));
        $this->assertSame([], $bundleDeprecations);
    }
}
