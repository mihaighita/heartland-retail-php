<?php

declare(strict_types=1);

namespace HeartlandRetail\Http;

/**
 * Wraps a raw Heartland Retail API response.
 */
class Response
{
    /**
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $body
     */
    public function __construct(
        private readonly int $statusCode,
        private readonly array $headers,
        private readonly ?array $body,
        private readonly string $rawBody,
    ) {}

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function isSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /** @return array<string, string> */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        $lower = strtolower($name);

        foreach ($this->headers as $key => $value) {
            if (strtolower((string) $key) === $lower) {
                return $value;
            }
        }

        return null;
    }

    /** Decoded JSON body, or null if the response had no body. */
    public function getBody(): ?array
    {
        return $this->body;
    }

    /** Convenience: get a key from the decoded body. */
    public function get(string $key): mixed
    {
        return $this->body[$key] ?? null;
    }

    public function getRawBody(): string
    {
        return $this->rawBody;
    }
}
