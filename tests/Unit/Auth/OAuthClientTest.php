<?php

declare(strict_types=1);

namespace HeartlandRetail\Tests\Unit\Auth;

use HeartlandRetail\Auth\OAuthClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OAuthClient::class)]
final class OAuthClientTest extends TestCase
{
    #[Test]
    public function get_authorization_url_builds_correct_url(): void
    {
        $oauth = new OAuthClient('client_id_123', 'client_secret_456');

        $url = $oauth->getAuthorizationUrl(
            'https://example.com/callback',
            ['item.read', 'customer.manage'],
            'csrf_state_token',
        );

        self::assertStringStartsWith('https://retail.heartland.us/oauth/authorize?', $url);
        self::assertStringContainsString('client_id=client_id_123', $url);
        self::assertStringContainsString('scope=item.read%20customer.manage', $url);
        self::assertStringContainsString('redirect_uri=https%3A%2F%2Fexample.com%2Fcallback', $url);
        self::assertStringContainsString('state=csrf_state_token', $url);
    }

    #[Test]
    public function get_authorization_url_handles_single_scope(): void
    {
        $oauth = new OAuthClient('id', 'secret');

        $url = $oauth->getAuthorizationUrl('https://example.com/cb', ['item.read'], 'state');

        self::assertStringContainsString('scope=item.read', $url);
        self::assertStringNotContainsString('%20', $url); // No space encoding needed
    }
}
