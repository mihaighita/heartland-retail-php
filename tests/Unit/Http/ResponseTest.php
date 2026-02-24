<?php

declare(strict_types=1);

namespace HeartlandRetail\Tests\Unit\Http;

use HeartlandRetail\Http\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Response::class)]
final class ResponseTest extends TestCase
{
    #[Test]
    public function it_exposes_status_code(): void
    {
        $response = new Response(200, [], null, '');

        self::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function is_success_returns_true_for_2xx(): void
    {
        self::assertTrue((new Response(200, [], null, ''))->isSuccess());
        self::assertTrue((new Response(201, [], null, ''))->isSuccess());
        self::assertTrue((new Response(204, [], null, ''))->isSuccess());
    }

    #[Test]
    public function is_success_returns_false_for_non_2xx(): void
    {
        self::assertFalse((new Response(400, [], null, ''))->isSuccess());
        self::assertFalse((new Response(500, [], null, ''))->isSuccess());
        self::assertFalse((new Response(301, [], null, ''))->isSuccess());
    }

    #[Test]
    public function it_returns_headers(): void
    {
        $response = new Response(200, ['content-type' => 'application/json'], null, '');

        self::assertSame('application/json', $response->getHeader('Content-Type'));
        self::assertSame('application/json', $response->getHeader('content-type'));
    }

    #[Test]
    public function get_header_returns_null_for_missing_header(): void
    {
        $response = new Response(200, [], null, '');

        self::assertNull($response->getHeader('X-Missing'));
    }

    #[Test]
    public function it_returns_decoded_body(): void
    {
        $body = ['id' => 42, 'name' => 'Test'];
        $response = new Response(200, [], $body, '{"id":42,"name":"Test"}');

        self::assertSame($body, $response->getBody());
        self::assertSame(42, $response->get('id'));
        self::assertSame('Test', $response->get('name'));
    }

    #[Test]
    public function get_returns_null_for_missing_key(): void
    {
        $response = new Response(200, [], ['id' => 1], '{"id":1}');

        self::assertNull($response->get('nonexistent'));
    }

    #[Test]
    public function it_returns_raw_body(): void
    {
        $raw = '{"id":1}';
        $response = new Response(200, [], ['id' => 1], $raw);

        self::assertSame($raw, $response->getRawBody());
    }

    #[Test]
    public function it_handles_null_body(): void
    {
        $response = new Response(204, [], null, '');

        self::assertNull($response->getBody());
        self::assertNull($response->get('anything'));
    }
}
