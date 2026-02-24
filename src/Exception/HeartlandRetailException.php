<?php

declare(strict_types=1);

namespace HeartlandRetail\Exception;

use RuntimeException;

/**
 * Base exception for all Heartland Retail API errors.
 */
class HeartlandRetailException extends RuntimeException
{
    /** @var array<string, mixed>|null */
    private readonly ?array $responseBody;

    /**
     * @param array<string, mixed>|null $responseBody
     */
    public function __construct(
        string $message,
        int $statusCode = 0,
        ?array $responseBody = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
        $this->responseBody = $responseBody;
    }

    public function getStatusCode(): int
    {
        return $this->getCode();
    }

    /** @return array<string, mixed>|null */
    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }
}
