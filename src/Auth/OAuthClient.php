<?php

declare(strict_types=1);

namespace HeartlandRetail\Auth;

use HeartlandRetail\Exception\HeartlandRetailException;
use HeartlandRetail\Exception\TransportException;

/**
 * Handles the Heartland Retail OAuth 2.0 authorization-code flow.
 *
 * Typical usage:
 *
 *   1. Redirect the end-user to {@see getAuthorizationUrl()}.
 *   2. After callback, call {@see exchangeCodeForToken()} → TokenResponse.
 *   3. Call {@see lookupAccountHost()} to get the account-specific subdomain.
 *   4. Pass the token + host to {@see \HeartlandRetail\Client}.
 *
 * @see https://dev.retail.heartland.us/#oauth
 */
class OAuthClient
{
    private const AUTHORIZE_URL = 'https://retail.heartland.us/oauth/authorize';
    private const TOKEN_URL = 'https://retail.heartland.us/api/oauth/token';
    private const HOST_URL = 'https://retail.heartland.us/api/system/host';

    public function __construct(
        private readonly string $clientId,
        #[\SensitiveParameter] private readonly string $clientSecret,
    ) {}

    /**
     * Build the URL to redirect the end-user to for authorization.
     *
     * @param string   $redirectUri Your callback URL (must match registered URI)
     * @param string[] $scopes      OAuth scopes to request
     * @param string   $state       CSRF state token (generate with random_bytes(16))
     */
    public function getAuthorizationUrl(
        string $redirectUri,
        array $scopes,
        string $state,
    ): string {
        $params = http_build_query([
            'client_id' => $this->clientId,
            'scope' => implode(' ', $scopes),
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);

        return self::AUTHORIZE_URL . '?' . $params;
    }

    /**
     * Exchange the temporary authorization code for a bearer access token.
     *
     * @throws HeartlandRetailException on API-level failure
     * @throws TransportException on network failure
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): TokenResponse
    {
        $params = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ];

        $raw = $this->httpGet(self::TOKEN_URL . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986));

        if (! isset($raw['access_token'])) {
            throw new HeartlandRetailException('Token exchange failed: no access_token in response.');
        }

        return new TokenResponse(
            accessToken: $raw['access_token'],
            tokenType: $raw['token_type'] ?? 'bearer',
            scope: $raw['scope'] ?? '',
        );
    }

    /**
     * Resolve the account-specific API host for a given access token.
     *
     * The result looks like: "example.retail.heartland.us"
     * Use it to build the base URL: "https://{host}/api/"
     *
     * @throws HeartlandRetailException
     */
    public function lookupAccountHost(#[\SensitiveParameter] string $accessToken): string
    {
        $raw = $this->httpGet(self::HOST_URL, $accessToken);

        if (! isset($raw['host'])) {
            throw new HeartlandRetailException('Host lookup failed: no host in response.');
        }

        return $raw['host'];
    }

    // -------------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     *
     * @throws HeartlandRetailException
     * @throws TransportException
     */
    private function httpGet(string $url, ?string $bearerToken = null): array
    {
        $ch = curl_init($url);

        $headers = ['Accept: application/json'];
        if ($bearerToken !== null) {
            $headers[] = 'Authorization: Bearer ' . $bearerToken;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $raw = curl_exec($ch);

        if ($raw === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);

            throw new TransportException("cURL error ({$errno}): {$error}");
        }

        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode((string) $raw, true);

        if ($status >= 400) {
            $message = $decoded['message'] ?? "HTTP {$status}";
            throw new HeartlandRetailException($message, $status, $decoded);
        }

        return $decoded ?? [];
    }
}
