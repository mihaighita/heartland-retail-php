<?php

declare(strict_types=1);

namespace HeartlandRetail\Exception;

/**
 * Thrown when the API returns HTTP 429 (rate limit exceeded).
 *
 * Inspect {@see getRetryAfter()} to determine when to retry.
 */
class RateLimitException extends HeartlandRetailException
{
    private readonly int $retryAfter;

    /**
     * @param array<string, mixed>|null $responseBody
     */
    public function __construct(
        string $message,
        int $retryAfter = 60,
        ?array $responseBody = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 429, $responseBody, $previous);
        $this->retryAfter = $retryAfter;
    }

    /** Number of seconds to wait before retrying. */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
