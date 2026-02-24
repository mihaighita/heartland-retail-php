<?php

declare(strict_types=1);

namespace HeartlandRetail\Tests\Unit\Auth;

use HeartlandRetail\Auth\TokenResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TokenResponse::class)]
final class TokenResponseTest extends TestCase
{
    #[Test]
    public function it_exposes_readonly_properties(): void
    {
        $token = new TokenResponse(
            accessToken: 'abc123',
            tokenType: 'bearer',
            scope: 'read write',
        );

        self::assertSame('abc123', $token->accessToken);
        self::assertSame('bearer', $token->tokenType);
        self::assertSame('read write', $token->scope);
    }

    #[Test]
    public function base_url_for_prepends_https_and_appends_api(): void
    {
        $token = new TokenResponse('t', 'bearer', '');

        self::assertSame(
            'https://mystore.retail.heartland.us/api',
            $token->baseUrlFor('mystore.retail.heartland.us'),
        );
    }

    #[Test]
    public function base_url_for_does_not_double_https(): void
    {
        $token = new TokenResponse('t', 'bearer', '');

        self::assertSame(
            'https://mystore.retail.heartland.us/api',
            $token->baseUrlFor('https://mystore.retail.heartland.us'),
        );
    }

    #[Test]
    public function base_url_for_strips_trailing_slash(): void
    {
        $token = new TokenResponse('t', 'bearer', '');

        self::assertSame(
            'https://mystore.retail.heartland.us/api',
            $token->baseUrlFor('mystore.retail.heartland.us/'),
        );
    }
}
