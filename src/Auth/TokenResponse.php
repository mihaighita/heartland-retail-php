<?php

declare(strict_types=1);

namespace HeartlandRetail\Auth;

/**
 * Immutable value object representing a Heartland Retail OAuth bearer token.
 */
final readonly class TokenResponse
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public string $scope,
    ) {}

    /**
     * Build the base API URL for a given account host.
     *
     * Usage: $token->baseUrlFor($host) → "https://example.retail.heartland.us/api"
     */
    public function baseUrlFor(string $host): string
    {
        $host = rtrim($host, '/');

        if (! str_starts_with($host, 'https://') && ! str_starts_with($host, 'http://')) {
            $host = 'https://' . $host;
        }

        return $host . '/api';
    }
}
