<?php

declare(strict_types=1);

namespace HeartlandRetail\Tests;

use HeartlandRetail\Http\HttpClient;
use HeartlandRetail\Http\Response;

/**
 * A lightweight HttpClient stub that records calls instead of making real HTTP requests.
 *
 * @internal For testing only.
 */
class MockHttpClient extends HttpClient
{
    /** @var array<int, array{method: string, path: string, args: mixed[]}> */
    public array $calls = [];

    private Response $stubbedResponse;

    public function __construct(?Response $stubbedResponse = null)
    {
        // We don't call parent — no real cURL will be used.
        $this->stubbedResponse = $stubbedResponse ?? new Response(200, [], ['id' => 1], '{"id":1}');
    }

    public function setResponse(Response $response): void
    {
        $this->stubbedResponse = $response;
    }

    public function get(string $path, array $query = []): Response
    {
        $this->calls[] = ['method' => 'GET', 'path' => $path, 'args' => [$query]];

        return $this->stubbedResponse;
    }

    public function post(string $path, ?array $body = null): Response
    {
        $this->calls[] = ['method' => 'POST', 'path' => $path, 'args' => [$body]];

        return $this->stubbedResponse;
    }

    public function put(string $path, ?array $body = null): Response
    {
        $this->calls[] = ['method' => 'PUT', 'path' => $path, 'args' => [$body]];

        return $this->stubbedResponse;
    }

    public function patch(string $path, ?array $body = null): Response
    {
        $this->calls[] = ['method' => 'PATCH', 'path' => $path, 'args' => [$body]];

        return $this->stubbedResponse;
    }

    public function delete(string $path): Response
    {
        $this->calls[] = ['method' => 'DELETE', 'path' => $path, 'args' => []];

        return $this->stubbedResponse;
    }

    public function setAccessToken(string $token): void
    {
        // no-op
    }

    /** Get the last recorded call. */
    public function lastCall(): ?array
    {
        return $this->calls[count($this->calls) - 1] ?? null;
    }
}
