<?php

declare(strict_types=1);

namespace HeartlandRetail\Tests\Unit\Exception;

use HeartlandRetail\Exception\AuthenticationException;
use HeartlandRetail\Exception\AuthorizationException;
use HeartlandRetail\Exception\HeartlandRetailException;
use HeartlandRetail\Exception\NotFoundException;
use HeartlandRetail\Exception\RateLimitException;
use HeartlandRetail\Exception\TransportException;
use HeartlandRetail\Exception\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExceptionTest extends TestCase
{
    #[Test]
    public function base_exception_extends_runtime_exception(): void
    {
        $e = new HeartlandRetailException('fail', 500, ['error' => 'bad']);

        self::assertInstanceOf(RuntimeException::class, $e);
        self::assertSame('fail', $e->getMessage());
        self::assertSame(500, $e->getStatusCode());
        self::assertSame(['error' => 'bad'], $e->getResponseBody());
    }

    #[Test]
    #[DataProvider('subclassProvider')]
    public function subclasses_extend_base_exception(string $class): void
    {
        $e = new $class('test', 400);

        self::assertInstanceOf(HeartlandRetailException::class, $e);
    }

    /** @return iterable<string, array{string}> */
    public static function subclassProvider(): iterable
    {
        yield 'Authentication' => [AuthenticationException::class];
        yield 'Authorization' => [AuthorizationException::class];
        yield 'NotFound' => [NotFoundException::class];
        yield 'Validation' => [ValidationException::class];
        yield 'RateLimit' => [RateLimitException::class];
        yield 'Transport' => [TransportException::class];
    }

    #[Test]
    public function validation_exception_exposes_field_errors(): void
    {
        $e = new ValidationException('Validation failed.', 422, [
            'errors' => [
                'email' => ['is required', 'must be valid'],
                'name' => ['is too short'],
            ],
        ]);

        $errors = $e->getErrors();

        self::assertArrayHasKey('email', $errors);
        self::assertCount(2, $errors['email']);
        self::assertSame(['is too short'], $errors['name']);
    }

    #[Test]
    public function validation_exception_returns_empty_array_when_no_errors(): void
    {
        $e = new ValidationException('fail', 422, []);

        self::assertSame([], $e->getErrors());
    }

    #[Test]
    public function rate_limit_exception_exposes_retry_after(): void
    {
        $e = new RateLimitException('Too many requests', 30);

        self::assertSame(429, $e->getStatusCode());
        self::assertSame(30, $e->getRetryAfter());
    }

    #[Test]
    public function rate_limit_exception_defaults_retry_after_to_60(): void
    {
        $e = new RateLimitException('Too many requests');

        self::assertSame(60, $e->getRetryAfter());
    }

    #[Test]
    public function base_exception_handles_null_response_body(): void
    {
        $e = new HeartlandRetailException('error', 500);

        self::assertNull($e->getResponseBody());
    }
}
