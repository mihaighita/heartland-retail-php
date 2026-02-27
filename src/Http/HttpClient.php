<?php

declare(strict_types=1);

namespace HeartlandRetail\Http;

use HeartlandRetail\Exception\AuthenticationException;
use HeartlandRetail\Exception\AuthorizationException;
use HeartlandRetail\Exception\HeartlandRetailException;
use HeartlandRetail\Exception\NotFoundException;
use HeartlandRetail\Exception\RateLimitException;
use HeartlandRetail\Exception\TransportException;
use HeartlandRetail\Exception\ValidationException;

/**
 * Low-level HTTP transport for the Heartland Retail REST API.
 *
 * Responsibilities:
 *   - Attach Bearer-token Authorization header.
 *   - JSON-encode request bodies; decode response bodies.
 *   - Detect and raise typed exceptions from HTTP status codes.
 *   - Honour rate-limit responses (HTTP 429) with configurable retry back-off.
 *   - Enforce a configurable per-second request budget to avoid hitting limits proactively.
 *
 * @internal This class is not part of the public API. Use {@see \HeartlandRetail\Client} instead.
 */
class HttpClient
{
    private const DEFAULT_TIMEOUT = 30;
    private const DEFAULT_CONNECT_TIMEOUT = 10;
    private const DEFAULT_MAX_RETRIES = 3;
    private const DEFAULT_RETRY_DELAY_MS = 500;
    private const USER_AGENT = 'HeartlandRetailPHP/2.0';

    private string $baseUrl;
    private string $accessToken;

    /** Maximum consecutive retries on rate-limit or 5xx responses. */
    private readonly int $maxRetries;

    /** Optional: maximum requests per second (0 = unlimited). */
    private readonly float $requestsPerSecond;

    /** Monotonic timestamp of the last outgoing request (microseconds). */
    private float $lastRequestTime = 0.0;

    /**
     * @param string $baseUrl           Full base URL, e.g. "https://mystore.retail.heartland.us/api"
     * @param string $accessToken       OAuth2 bearer token
     * @param int    $maxRetries        Max auto-retries on 429/5xx (default 3)
     * @param float  $requestsPerSecond Proactive rate cap (0 = unlimited)
     */
    public function __construct(
        string $baseUrl,
        #[\SensitiveParameter] string $accessToken,
        int $maxRetries = self::DEFAULT_MAX_RETRIES,
        float $requestsPerSecond = 0.0,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->accessToken = $accessToken;
        $this->maxRetries = max(0, $maxRetries);
        $this->requestsPerSecond = $requestsPerSecond;
    }

    public function setAccessToken(#[\SensitiveParameter] string $token): void
    {
        $this->accessToken = $token;
    }

    /**
     * Perform a GET request, optionally appending query parameters.
     *
     * @param array<string, mixed> $query
     */
    public function get(string $path, array $query = []): Response
    {
        $url = $this->buildUrl($path, $query);

        return $this->execute('GET', $url);
    }

    /**
     * Perform a POST request with a JSON body.
     *
     * @param array<string, mixed>|null $body
     */
    public function post(string $path, ?array $body = null): Response
    {
        return $this->execute('POST', $this->buildUrl($path), $body);
    }

    /**
     * Perform a PUT request with a JSON body.
     *
     * @param array<string, mixed>|null $body
     */
    public function put(string $path, ?array $body = null): Response
    {
        return $this->execute('PUT', $this->buildUrl($path), $body);
    }

    /**
     * Perform a PATCH request with a JSON body.
     *
     * @param array<string, mixed>|null $body
     */
    public function patch(string $path, ?array $body = null): Response
    {
        return $this->execute('PATCH', $this->buildUrl($path), $body);
    }

    /**
     * Perform a DELETE request.
     */
    public function delete(string $path): Response
    {
        return $this->execute('DELETE', $this->buildUrl($path));
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /**
     * @param array<string, mixed> $query
     */
    private function buildUrl(string $path, array $query = []): string
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');

        if ($query !== []) {
            $filtered = array_filter($query, static fn (mixed $v): bool => $v !== null);

            if ($filtered !== []) {
                $qs = http_build_query($filtered, '', '&', PHP_QUERY_RFC3986);

                // Heartland's API expects group[]=a&group[]=b (bracket-only),
                // not group[0]=a&group[1]=b (PHP's indexed format).
                $qs = preg_replace('/%5B\d+%5D/', '%5B%5D', $qs);

                $url .= '?' . $qs;
            }
        }

        return $url;
    }

    /** Rate-limit throttle: ensure we don't exceed the configured rps budget. */
    private function throttle(): void
    {
        if ($this->requestsPerSecond <= 0) {
            return;
        }

        $minInterval = 1_000_000 / $this->requestsPerSecond;
        $elapsed = (microtime(true) * 1_000_000) - $this->lastRequestTime;

        if ($elapsed < $minInterval) {
            usleep((int) ($minInterval - $elapsed));
        }

        $this->lastRequestTime = microtime(true) * 1_000_000;
    }

    /**
     * @param array<string, mixed>|null $body
     */
    private function execute(string $method, string $url, ?array $body = null): Response
    {
        $attempt = 0;

        while (true) {
            $this->throttle();

            [$statusCode, $headers, $rawBody] = $this->curlRequest($method, $url, $body);

            $decoded = null;
            if ($rawBody !== '') {
                $decoded = json_decode($rawBody, true);
            }

            $response = new Response($statusCode, $headers, $decoded, $rawBody);

            // --- Handle retryable errors ---
            if ($statusCode === 429) {
                $retryAfter = (int) ($headers['retry-after'] ?? 60);

                if ($attempt < $this->maxRetries) {
                    $attempt++;
                    sleep(max(1, $retryAfter));

                    continue;
                }

                throw new RateLimitException(
                    "Rate limit exceeded. Retry after {$retryAfter}s.",
                    $retryAfter,
                    $decoded,
                );
            }

            if ($statusCode >= 500 && $attempt < $this->maxRetries) {
                $attempt++;
                $delay = self::DEFAULT_RETRY_DELAY_MS * (2 ** ($attempt - 1));
                usleep($delay * 1000);

                continue;
            }

            // --- Raise typed exceptions for error status codes ---
            match (true) {
                $statusCode === 401 => throw new AuthenticationException(
                    'Unauthenticated: check your access token.',
                    401,
                    $decoded,
                ),
                $statusCode === 403 => throw new AuthorizationException(
                    'Forbidden: insufficient scope or permissions.',
                    403,
                    $decoded,
                ),
                $statusCode === 404 => throw new NotFoundException(
                    "Not found: {$url}",
                    404,
                    $decoded,
                ),
                $statusCode === 422 => throw new ValidationException(
                    $decoded['message'] ?? 'Validation failed.',
                    422,
                    $decoded,
                ),
                $statusCode >= 400 => throw new HeartlandRetailException(
                    $decoded['message'] ?? "HTTP error {$statusCode}",
                    $statusCode,
                    $decoded,
                ),
                default => null,
            };

            return $response;
        }
    }

    /**
     * Execute the raw cURL request.
     *
     * @param array<string, mixed>|null $body
     * @return array{0: int, 1: array<string, string>, 2: string} [statusCode, headers, body]
     *
     * @throws TransportException on cURL failure
     */
    private function curlRequest(string $method, string $url, ?array $body): array
    {
        $ch = curl_init();

        $requestHeaders = [
            'Authorization: Bearer ' . $this->accessToken,
            'Accept: application/json',
            'User-Agent: ' . self::USER_AGENT,
        ];

        $encodedBody = null;
        if ($body !== null) {
            $encodedBody = json_encode($body, JSON_THROW_ON_ERROR);
            $requestHeaders[] = 'Content-Type: application/json';
            $requestHeaders[] = 'Content-Length: ' . strlen($encodedBody);
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::DEFAULT_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::DEFAULT_CONNECT_TIMEOUT,
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CUSTOMREQUEST => $method,
        ]);

        if ($encodedBody !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedBody);
        }

        $raw = curl_exec($ch);

        if ($raw === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);

            throw new TransportException("cURL error ({$errno}): {$error}");
        }

        /** @var string $raw */
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $rawHeaders = substr($raw, 0, $headerSize);
        $responseBody = substr($raw, $headerSize);

        $headers = $this->parseHeaders($rawHeaders);

        return [$statusCode, $headers, $responseBody];
    }

    /**
     * Parse raw HTTP header block into a key→value array (lowercased keys).
     *
     * @return array<string, string>
     */
    private function parseHeaders(string $rawHeaders): array
    {
        $headers = [];

        foreach (explode("\r\n", $rawHeaders) as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
                $headers[strtolower(trim($key))] = trim($value);
            }
        }

        return $headers;
    }
}
