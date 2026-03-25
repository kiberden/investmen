<?php

declare(strict_types = 1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @method void assertContains(mixed $needle, iterable $haystack, string $message = '')
 * @method void assertSame(mixed $expected, mixed $actual, string $message = '')
 * @method void assertNotSame(mixed $expected, mixed $actual, string $message = '')
 * @method void assertNull(mixed $actual, string $message = '')
 * @method void assertInstanceOf(string $expected, mixed $actual, string $message = '')
 * @method void assertIsArray(mixed $actual, string $message = '')
 * @method void assertArrayHasKey(string|int $key, array|\ArrayAccess $array, string $message = '')
 * @method void assertIsString(mixed $actual, string $message = '')
 * @method void assertIsInt(mixed $actual, string $message = '')
 * @method void assertGreaterThan(int|float $expected, int|float $actual, string $message = '')
 * @method void expectException(string $exception)
 * @method void expectExceptionMessage(string $message)
 * @method never markTestSkipped(string $message = '')
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
